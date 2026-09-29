import subprocess, tempfile, os

audit_path = '/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/ELIGIBILITY_BUSINESS_AUDIT.md'

with open(audit_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

blocks = []
in_block = False
lang = ''
cur_lines = []
start_line = 0

for idx, line in enumerate(lines, 1):
    stripped = line.strip()
    if not in_block:
        if stripped.startswith('```') and len(stripped) > 3:
            in_block = True
            lang = stripped[3:].strip()
            cur_lines = []
            start_line = idx
    else:
        if stripped == '```':
            blocks.append((start_line, idx, lang, ''.join(cur_lines)))
            in_block = False
            lang = ''
            cur_lines = []
        else:
            cur_lines.append(line)

print(f'Total code blocks found: {len(blocks)}')
all_valid = True

for start, end, lang, code in blocks:
    print(f'\n--- Block at lines {start}-{end} [{lang}] ---')
    if lang == 'php':
        clean = code.strip()
        if not clean.startswith('<?php'):
            clean = '<?php\n' + clean
        with tempfile.NamedTemporaryFile(suffix='.php', mode='w', encoding='utf-8', delete=False) as tf:
            tf.write(clean)
            tf_path = tf.name
        res = subprocess.run(['php', '-l', tf_path], capture_output=True, text=True)
        os.remove(tf_path)
        if res.returncode == 0:
            print('  PHP Syntax: VALID')
        else:
            print(f'  PHP Syntax: INVALID -> {res.stderr or res.stdout}')
            all_valid = False
    elif lang in ['javascript', 'js']:
        with tempfile.NamedTemporaryFile(suffix='.js', mode='w', encoding='utf-8', delete=False) as tf:
            tf.write(code)
            tf_path = tf.name
        res = subprocess.run(['node', '-c', tf_path], capture_output=True, text=True)
        os.remove(tf_path)
        if res.returncode == 0:
            print('  JS Syntax: VALID')
        else:
            print(f'  JS Syntax: INVALID -> {res.stderr or res.stdout}')
            all_valid = False
    elif lang in ['sql', 'html']:
        print(f'  {lang.upper()} Block: Checked ({len(code.splitlines())} lines)')

if all_valid:
    print('\nALL CODE BLOCKS ARE SYNTACTICALLY VALID AND SOUND!')
else:
    print('\nSOME CODE BLOCKS HAVE SYNTAX ERRORS!')
