import os
import re

report_path = 'FULL_PROJECT_AUDIT_REPORT.md'
with open(report_path, 'r', encoding='utf-8') as f:
    text = f.read()

# Pattern: | STT | `path` | lines | bytes | ...
rows = re.findall(r'\|\s*(\d+)\s*\|\s*`([^`]+)`\s*\|\s*([\d,]+)\s*\|\s*([\d,]+)\s*\|', text)
print(f'Total inventory entries parsed from report: {len(rows)}')

all_match = True
for stt, path, lines_str, size_str in rows:
    expected_lines = int(lines_str.replace(',', ''))
    expected_size = int(size_str.replace(',', ''))
    
    if not os.path.exists(path):
        print(f'MISSING FILE ON DISK: {path}')
        all_match = False
        continue
        
    actual_size = os.path.getsize(path)
    with open(path, 'r', encoding='utf-8', errors='ignore') as f:
        actual_lines = sum(1 for _ in f)
        
    if actual_size != expected_size:
        print(f'SIZE MISMATCH for {path}: expected {expected_size}, got {actual_size}')
        all_match = False
        
    if actual_lines != expected_lines:
        print(f'LINE MISMATCH for {path}: expected {expected_lines}, got {actual_lines}')
        all_match = False

if all_match and len(rows) == 50:
    print('VERIFIED: 100% of files (50 total: 49 PHP + 1 CSS) in inventory table match disk sizes and line counts EXACTLY to the byte and line!')
else:
    print(f'Done checking. Result all_match={all_match}, total rows={len(rows)}')
