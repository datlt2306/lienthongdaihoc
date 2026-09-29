import re
import subprocess
import tempfile
import sys

with open('FULL_PROJECT_AUDIT_REPORT.md', 'r', encoding='utf-8') as f:
    content = f.read()

success = True

# 1. SCHEMA-CRIT-01
m = re.search(r'#### \[SCHEMA-CRIT-01\].*?```php\n(.*?)```', content, re.DOTALL)
if m:
    code = "<?php\n" + m.group(1)
    with tempfile.NamedTemporaryFile('w', suffix='.php', delete=False) as tf:
        tf.write(code)
        tf_name = tf.name
    res = subprocess.run(['php', '-l', tf_name], capture_output=True, text=True)
    print('SCHEMA-CRIT-01 php -l status:', res.returncode)
    if res.returncode != 0:
        print('SCHEMA-CRIT-01 error:', res.stderr)
        success = False
    else:
        print('SCHEMA-CRIT-01: OK -', res.stdout.strip())
else:
    print('SCHEMA-CRIT-01: NOT FOUND')
    success = False

# 2. SCHEMA-HIGH-01
m = re.search(r'#### \[SCHEMA-HIGH-01\].*?```php\n(.*?)```', content, re.DOTALL)
if m:
    code = "<?php\n" + m.group(1)
    with tempfile.NamedTemporaryFile('w', suffix='.php', delete=False) as tf:
        tf.write(code)
        tf_name = tf.name
    res = subprocess.run(['php', '-l', tf_name], capture_output=True, text=True)
    print('SCHEMA-HIGH-01 php -l status:', res.returncode)
    if res.returncode != 0:
        print('SCHEMA-HIGH-01 error:', res.stderr)
        success = False
    else:
        print('SCHEMA-HIGH-01: OK -', res.stdout.strip())
else:
    print('SCHEMA-HIGH-01: NOT FOUND')
    success = False

# 3. SCHEMA-HIGH-03
m = re.search(r'#### \[SCHEMA-HIGH-03\].*?```php\n(.*?)```', content, re.DOTALL)
if m:
    code = "<?php\nfunction _test_faq() {\n$data = [];\n" + m.group(1) + "\nreturn $data;\n}"
    with tempfile.NamedTemporaryFile('w', suffix='.php', delete=False) as tf:
        tf.write(code)
        tf_name = tf.name
    res = subprocess.run(['php', '-l', tf_name], capture_output=True, text=True)
    print('SCHEMA-HIGH-03 php -l status:', res.returncode)
    if res.returncode != 0:
        print('SCHEMA-HIGH-03 error:', res.stderr)
        success = False
    else:
        print('SCHEMA-HIGH-03: OK -', res.stdout.strip())
else:
    print('SCHEMA-HIGH-03: NOT FOUND')
    success = False

# 4. FRONT-HIGH-02
m = re.search(r'#### \[FRONT-HIGH-02\].*?```javascript\n(.*?)```', content, re.DOTALL)
if m:
    js_block = m.group(1)
    # Extract the AFTER implementation of initLeadForm (use rfind to get the second AFTER block)
    after_part = js_block[js_block.rfind('// AFTER:'):]
    after_code = re.search(r'(function initLeadForm\(.*?\n\})', after_part, re.DOTALL)
    if after_code:
        with tempfile.NamedTemporaryFile('w', suffix='.js', delete=False) as tf:
            tf.write(after_code.group(1))
            tf_name = tf.name
        res = subprocess.run(['node', '-c', tf_name], capture_output=True, text=True)
        print('FRONT-HIGH-02 node -c status:', res.returncode)
        if res.returncode != 0:
            print('FRONT-HIGH-02 error:', res.stderr)
            success = False
        else:
            print('FRONT-HIGH-02: OK - Valid JavaScript syntax')
    else:
        print('FRONT-HIGH-02: initLeadForm not found in AFTER block')
        success = False
else:
    print('FRONT-HIGH-02: NOT FOUND')
    success = False

# 5. PERF-MED-01
m = re.search(r'#### \[PERF-MED-01\].*?```php\n(.*?)```', content, re.DOTALL)
if m:
    code = "<?php\ndefine('LTDH_META_OFFERED_PROGRAMS', '_offered_programs');\ndefine('LTDH_META_MAJOR_REL', 'major_relationship');\n" + m.group(1)
    with tempfile.NamedTemporaryFile('w', suffix='.php', delete=False) as tf:
        tf.write(code)
        tf_name = tf.name
    res = subprocess.run(['php', '-l', tf_name], capture_output=True, text=True)
    print('PERF-MED-01 php -l status:', res.returncode)
    if res.returncode != 0:
        print('PERF-MED-01 error:', res.stderr)
        success = False
    else:
        print('PERF-MED-01: OK -', res.stdout.strip())
else:
    print('PERF-MED-01: NOT FOUND')
    success = False

if not success:
    sys.exit(1)
print("\nAll patched snippets in FULL_PROJECT_AUDIT_REPORT.md passed syntax checks with ZERO errors!")
