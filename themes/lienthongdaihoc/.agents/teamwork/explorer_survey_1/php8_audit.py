#!/usr/bin/env python3
import os
import re
import json

THEME_ROOT = "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc"

# Targets: all 49 php files
def get_php_files():
    php_files = []
    for root, dirs, files in os.walk(THEME_ROOT):
        dirs[:] = [d for d in dirs if d not in ['.git', '.agents', 'node_modules']]
        for f in files:
            if f.endswith('.php'):
                php_files.append(os.path.relpath(os.path.join(root, f), THEME_ROOT))
    php_files.sort()
    return php_files

def scan_file(rel_path):
    full_path = os.path.join(THEME_ROOT, rel_path)
    with open(full_path, "r", encoding="utf-8", errors="ignore") as f:
        lines = f.readlines()

    issues = []

    for idx, raw_line in enumerate(lines, 1):
        line = raw_line.strip()
        if not line or line.startswith('//') or line.startswith('*') or line.startswith('/*'):
            continue

        # 1. Null passed to string functions (PHP 8.1 deprecation)
        # e.g., trim(get_field(...)) or strlen(get_post_meta(...)) or str_replace(..., get_field(...))
        str_funcs = ['strlen', 'trim', 'ltrim', 'rtrim', 'strtolower', 'strtoupper', 'substr', 'strpos', 'stripos', 'str_replace', 'preg_match', 'preg_replace', 'urlencode', 'rawurlencode']
        for sf in str_funcs:
            # Check pattern sf(get_field(...)) or sf(get_post_meta(...)) without ?: or ??
            pattern = rf'\b{sf}\s*\(\s*(get_field|get_post_meta)\b'
            if re.search(pattern, line):
                # Check if it has a fallback like ?: '' or ?? ''
                if '?:' not in line and '??' not in line:
                    issues.append({
                        "file": rel_path,
                        "line": idx,
                        "category": "PHP 8.1 Null Deprecation",
                        "severity": "Medium",
                        "code": line,
                        "description": f"Passing result of {re.search(pattern, line).group(1)}() directly to {sf}() without null coalesce (?? '' or ?: ''). In PHP 8.1+, passing null to {sf}() emits E_DEPRECATED."
                    })

        # 2. Undefined array key risk on $_GET, $_POST, $_REQUEST, $_SERVER
        # Looking for direct $_GET['foo'] when not wrapped in isset(), empty(), or ??
        for sg in ['$_GET', '$_POST', '$_REQUEST']:
            matches = re.finditer(rf'(\{sg}\[[\'"][a-zA-Z0-9_\-]+[\'"]\])', line)
            for m in matches:
                expr = m.group(1)
                # Check context on that line: is it guarded by isset, empty, ??, array_key_exists
                # Example safe: isset($_GET['foo']) ? ...
                # Unsafe: $foo = $_GET['foo']; or echo $_GET['foo']; or sanitize_text_field($_GET['foo'])
                # If expression is inside isset(...) or empty(...), it's safe.
                # Let's check if 'isset(' + expr or 'empty(' + expr or expr + ' ??'
                if f"isset({expr}" not in line and f"isset( {expr}" not in line and \
                   f"empty({expr}" not in line and f"empty( {expr}" not in line and \
                   f"{expr} ??" not in line and f"{expr}??" not in line:
                    # Could be unsafe if expr is used directly
                    # Exclude lines where isset was checked on earlier subexpression
                    issues.append({
                        "file": rel_path,
                        "line": idx,
                        "category": "Undefined Array Key",
                        "severity": "Medium",
                        "code": line,
                        "description": f"Direct access to {expr} without prior isset(), empty() or ?? fallback. In PHP 8.0+, missing array key emits E_WARNING (previously E_NOTICE)."
                    })

        # 3. Optional parameter before required parameter in function definitions
        # function foo($a = 1, $b)
        func_match = re.search(r'function\s+([a-zA-Z0-9_]+)\s*\(([^)]+)\)', line)
        if func_match:
            params = [p.strip() for p in func_match.group(2).split(',') if p.strip()]
            has_default = False
            for p in params:
                if '=' in p:
                    has_default = True
                elif has_default and not p.startswith('&') and not p.startswith('...'):
                    issues.append({
                        "file": rel_path,
                        "line": idx,
                        "category": "PHP 8.0 Deprecated Signature",
                        "severity": "High",
                        "code": line,
                        "description": f"Function {func_match.group(1)}() declares parameter `{p}` after an optional parameter with default value. Deprecated in PHP 8.0, fatal in future."
                    })

        # 4. count() or sizeof() on potential non-countable
        # count(get_field(...)) or count(get_post_meta(...))
        if re.search(r'\b(count|sizeof)\s*\(\s*(get_field|get_post_meta)\b', line):
            if 'is_array' not in line:
                issues.append({
                    "file": rel_path,
                    "line": idx,
                    "category": "Count on Non-Countable",
                    "severity": "Medium",
                    "code": line,
                    "description": "Calling count() directly on get_field() or get_post_meta() without is_array() guard. Throws TypeError in PHP 8.0+ if boolean false / null / non-countable returned."
                })

        # 5. get_page_by_path deprecation in WordPress 6.2+
        if 'get_page_by_path(' in line:
            issues.append({
                "file": rel_path,
                "line": idx,
                "category": "WP Core 6.2+ Deprecation",
                "severity": "Low",
                "code": line,
                "description": "get_page_by_path() is deprecated/discouraged since WP 6.2.0 in favor of WP_Query or get_posts()."
            })

    return issues

def main():
    files = get_php_files()
    all_issues = []
    for f in files:
        issues = scan_file(f)
        all_issues.extend(issues)

    out_file = os.path.join(THEME_ROOT, ".agents/teamwork/explorer_survey_1/php8_audit_results.json")
    with open(out_file, "w", encoding="utf-8") as f:
        json.dump(all_issues, f, indent=2, ensure_ascii=False)

    print(f"Total issues found: {len(all_issues)}")
    # Summary by category
    cats = {}
    for iss in all_issues:
        c = iss['category']
        cats[c] = cats.get(c, 0) + 1
    for c, cnt in cats.items():
        print(f" - {c}: {cnt}")

if __name__ == "__main__":
    main()
