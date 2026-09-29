#!/usr/bin/env python3
import os
import subprocess
import re
import json

THEME_ROOT = "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc"

def run_cmd(cmd):
    result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return result.returncode, result.stdout.strip(), result.stderr.strip()

def analyze_php_file(rel_path):
    full_path = os.path.join(THEME_ROOT, rel_path)
    file_size = os.path.getsize(full_path)
    
    with open(full_path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
        lines = content.splitlines()
        line_count = len(lines)

    # Syntax check
    rc, stdout, stderr = run_cmd(f'php -l "{full_path}"')
    syntax_ok = (rc == 0)
    syntax_msg = stdout if rc == 0 else f"{stdout} {stderr}".strip()

    # Direct access check
    has_abspath = bool(re.search(r"defined\s*\(\s*['\"]ABSPATH['\"]\s*\)", content))

    # Docblock purpose
    purpose = ""
    doc_match = re.search(r'/\*\*\s*\n([^*]|\*[^/])*?\*/', content)
    if doc_match:
        doc = doc_match.group(0)
        doc_lines = [l.strip().lstrip('/*').rstrip('*/').strip() for l in doc.splitlines()]
        doc_lines = [l for l in doc_lines if l and not l.startswith('@')]
        if doc_lines:
            purpose = doc_lines[0]
            if len(doc_lines) > 1 and len(purpose) < 30:
                purpose += " - " + doc_lines[1]

    if not purpose:
        # Heuristic purpose based on file name/path
        if rel_path == "functions.php":
            purpose = "Theme bootstrap & core module loader"
        elif rel_path == "style.css":
            purpose = "Theme stylesheet & metadata"
        elif rel_path.startswith("template-parts/"):
            purpose = f"Reusable UI template part: {rel_path}"
        elif rel_path.startswith("single-"):
            purpose = f"Single detail template for {rel_path[7:-4]}"
        elif rel_path.startswith("archive-"):
            purpose = f"Archive listing template for {rel_path[8:-4]}"
        elif rel_path.startswith("page-"):
            purpose = f"Custom page template for {rel_path[5:-4]}"
        elif rel_path.startswith("taxonomy-"):
            purpose = f"Custom taxonomy template for {rel_path[9:-4]}"
        elif rel_path == "taxonomy.php":
            purpose = "Fallback taxonomy listing template"
        elif rel_path == "index.php":
            purpose = "Fallback main template"
        elif rel_path == "header.php":
            purpose = "Site header template & head tags"
        elif rel_path == "footer.php":
            purpose = "Site footer template & bottom scripts"
        elif rel_path == "404.php":
            purpose = "404 Not Found error template"
        elif rel_path == "front-page.php":
            purpose = "Static front page homepage template"
        elif rel_path == "page.php":
            purpose = "Standard page fallback template"
        elif rel_path == "single.php":
            purpose = "Standard single post fallback template"
        else:
            purpose = f"Module/template: {rel_path}"

    # Extract hooks
    # add_action('hook_name', ...), add_filter('hook_name', ...), do_action('hook_name', ...), apply_filters('hook_name', ...)
    actions_reg = []
    for m in re.finditer(r"add_action\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*([^,\)]+)", content):
        actions_reg.append((m.group(1), m.group(2).strip(), m.start()))
    
    filters_reg = []
    for m in re.finditer(r"add_filter\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*([^,\)]+)", content):
        filters_reg.append((m.group(1), m.group(2).strip(), m.start()))

    actions_fired = []
    for m in re.finditer(r"do_action\s*\(\s*['\"]([^'\"]+)['\"]", content):
        actions_fired.append(m.group(1))

    filters_applied = []
    for m in re.finditer(r"apply_filters\s*\(\s*['\"]([^'\"]+)['\"]", content):
        filters_applied.append(m.group(1))

    # PHP 8.1+ Compatibility checks & Potential Warnings
    php8_issues = []
    
    # 1. Null passed to string functions: strlen, trim, substr, explode, str_replace, strpos, etc.
    # Pattern: strlen($var) or trim($var) where $var might be null (e.g. get_field, get_post_meta without null check)
    for idx, line in enumerate(lines, 1):
        # Look for null risk with trim/strlen/strtolower/strtoupper
        if re.search(r'\b(strlen|trim|ltrim|rtrim|strtolower|strtoupper|substr|explode|strpos|str_replace)\s*\(\s*(get_post_meta|get_field|\$[a-zA-Z0-9_]+)\s*[,)]', line):
            # check if guarded or default provided
            php8_issues.append({
                "line": idx,
                "type": "Null Safety / String Function",
                "code": line.strip(),
                "note": "Possible null passed to string function in PHP 8.1+ (causes Deprecation notice or TypeError if null)"
            })
            
        # Look for optional parameter before required parameter in function definitions
        # function foo($a = 1, $b)
        func_def = re.search(r'function\s+([a-zA-Z0-9_]+)\s*\(([^)]*)\)', line)
        if func_def:
            params = [p.strip() for p in func_def.group(2).split(',') if p.strip()]
            has_default = False
            for p in params:
                if '=' in p:
                    has_default = True
                elif has_default and not p.startswith('&') and not p.startswith('...'):
                    php8_issues.append({
                        "line": idx,
                        "type": "PHP 8.0 Deprecation: Optional Param Before Required",
                        "code": line.strip(),
                        "note": f"Parameter {p} declared after optional parameter in function {func_def.group(1)}"
                    })

        # Direct access to superglobals without isset/empty/??
        # e.g. $_GET['foo'], $_POST['bar'] without isset() or ?? on that line
        # but check if line contains isset, empty, ??, ?:
        for sg in ['$_GET', '$_POST', '$_REQUEST']:
            if sg in line:
                # check if there's direct assignment or usage like $x = $_GET['x'] without ?? or isset
                matches = re.finditer(r'(\$_GET|\$_POST|\$_REQUEST)\[\s*[\'"]([^\'"]+)[\'"]\s*\]', line)
                for sm in matches:
                    matched_expr = sm.group(0)
                    # Check if enclosed in isset(...) or empty(...) or followed by ??
                    # Simple heuristic
                    if 'isset(' not in line and 'empty(' not in line and '??' not in line:
                        php8_issues.append({
                            "line": idx,
                            "type": "Undefined Array Key Risk",
                            "code": line.strip(),
                            "note": f"Unchecked direct access {matched_expr} without ?? or isset() in PHP 8.0+ causes E_WARNING"
                        })

        # get_page_by_path deprecation in WP 6.2+
        if 'get_page_by_path' in line:
            php8_issues.append({
                "line": idx,
                "type": "WP Core Deprecation Risk",
                "code": line.strip(),
                "note": "get_page_by_path() is discouraged/deprecated in WP 6.2+, WP_Query or get_posts() should be used"
            })

    return {
        "file": rel_path,
        "line_count": line_count,
        "size_bytes": file_size,
        "purpose": purpose,
        "syntax_ok": syntax_ok,
        "syntax_msg": syntax_msg,
        "has_abspath": has_abspath,
        "actions_registered": actions_reg,
        "filters_registered": filters_reg,
        "actions_fired": actions_fired,
        "filters_applied": filters_applied,
        "php8_issues": php8_issues
    }

def main():
    php_files = []
    for root, dirs, files in os.walk(THEME_ROOT):
        # Exclude unwanted directories
        dirs[:] = [d for d in dirs if d not in ['.git', '.agents', 'node_modules']]
        for f in files:
            if f.endswith('.php'):
                rel_path = os.path.relpath(os.path.join(root, f), THEME_ROOT)
                php_files.append(rel_path)

    php_files.sort()
    results = [analyze_php_file(f) for f in php_files]

    out_file = os.path.join(THEME_ROOT, ".agents/teamwork/explorer_survey_1/survey_data.json")
    with open(out_file, "w", encoding="utf-8") as f:
        json.dump(results, f, indent=2, ensure_ascii=False)

    print(f"Analyzed {len(results)} files. Written to {out_file}")

if __name__ == "__main__":
    main()
