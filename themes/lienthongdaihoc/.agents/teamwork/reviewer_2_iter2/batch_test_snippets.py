import re
import subprocess
import os

report = "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/FULL_PROJECT_AUDIT_REPORT.md"
with open(report, "r", encoding="utf-8") as f:
    text = f.read()

sections = re.findall(r'(####\s*\[[A-Z0-9\-]+\][^\n]+)(.*?)(?=(?:####\s*\[|###\s*|\Z))', text, re.DOTALL)
print(f"Total audit issue sections found: {len(sections)}")

php_count = 0
js_count = 0
php_errors = []
js_errors = []

for heading, body in sections:
    match = re.search(r'\[([A-Z0-9\-]+)\]', heading)
    if not match:
        continue
    issue_id = match.group(1)
    
    # Check PHP blocks
    php_blocks = re.findall(r'```php\n(.*?)```', body, re.DOTALL)
    for i, block in enumerate(php_blocks):
        code_to_test = block
        if '// AFTER:' in block or '/* AFTER */' in block:
            parts = re.split(r'//\s*AFTER:?', block)
            if len(parts) > 1:
                code_to_test = parts[1]
        
        lines = [l for l in code_to_test.split('\n') if not l.strip().startswith('//') and not l.strip().startswith('*')]
        clean_code = '\n'.join(lines).strip()
        if not clean_code:
            continue
        
        # If it contains HTML template code (like <main> or <style>), wrap in PHP appropriately
        if clean_code.startswith('<') and not clean_code.startswith('<?php'):
            clean_code = '?>' + clean_code
        elif not clean_code.startswith('<?php'):
            clean_code = '<?php\n' + clean_code
            
        res = subprocess.run(['php', '-l'], input=clean_code, text=True, capture_output=True)
        php_count += 1
        if res.returncode != 0:
            php_errors.append((issue_id, res.stderr.strip()))

    # Check JS blocks
    js_blocks = re.findall(r'```(?:javascript|js)\n(.*?)```', body, re.DOTALL)
    for i, block in enumerate(js_blocks):
        code_to_test = block
        if '// AFTER:' in block:
            parts = re.split(r'//\s*AFTER:?', block)
            if len(parts) > 1:
                code_to_test = parts[1]
        
        lines = [l for l in code_to_test.split('\n') if not l.strip().startswith('//')]
        clean_code = '\n'.join(lines).strip()
        if not clean_code:
            continue
            
        res = subprocess.run(['node', '-c'], input=clean_code, text=True, capture_output=True)
        js_count += 1
        if res.returncode != 0:
            js_errors.append((issue_id, res.stderr.strip()))

print(f"Tested {php_count} PHP snippets. Errors: {len(php_errors)}")
for issue, err in php_errors:
    print(f"PHP Error in {issue}: {err}")

print(f"Tested {js_count} JS snippets. Errors: {len(js_errors)}")
for issue, err in js_errors:
    print(f"JS Error in {issue}: {err}")
