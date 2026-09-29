import re
import subprocess
import os
import sys

report_path = "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc/FULL_PROJECT_AUDIT_REPORT.md"
with open(report_path, "r", encoding="utf-8") as f:
    content = f.read()

# Let's extract snippets under each target section
targets = [
    "SCHEMA-CRIT-01",
    "SCHEMA-HIGH-01",
    "SCHEMA-HIGH-03",
    "FRONT-HIGH-02",
    "PERF-MED-01"
]

results = {}

for target in targets:
    # Find target header
    pattern = rf"####\s*\[{target}\].*?(?=(?:####\s*\[|###\s*|\Z))"
    match = re.search(pattern, content, re.DOTALL)
    if not match:
        print(f"FAILED to find section {target}")
        sys.exit(1)
    
    section_text = match.group(0)
    # Find code blocks
    code_blocks = re.findall(r"```(php|javascript|js|css)?\n(.*?)```", section_text, re.DOTALL)
    results[target] = code_blocks
    print(f"Found {len(code_blocks)} code blocks in {target}")

# Test target 1: SCHEMA-CRIT-01
print("\n--- Testing SCHEMA-CRIT-01 ---")
schema_crit_01_code = results["SCHEMA-CRIT-01"][0][1]
# Wrap with <?php if needed
php_code = "<?php\n" + schema_crit_01_code
proc = subprocess.run(["php", "-l"], input=php_code, text=True, capture_output=True)
print(f"php -l returncode: {proc.returncode}")
print(proc.stdout.strip())
if proc.returncode != 0:
    print("ERROR stderr:", proc.stderr)

# Test target 2: SCHEMA-HIGH-01
print("\n--- Testing SCHEMA-HIGH-01 ---")
schema_high_01_code = results["SCHEMA-HIGH-01"][0][1]
php_code = "<?php\n" + schema_high_01_code
proc = subprocess.run(["php", "-l"], input=php_code, text=True, capture_output=True)
print(f"php -l returncode: {proc.returncode}")
print(proc.stdout.strip())
if proc.returncode != 0:
    print("ERROR stderr:", proc.stderr)

# Test target 3: SCHEMA-HIGH-03
print("\n--- Testing SCHEMA-HIGH-03 ---")
schema_high_03_code = results["SCHEMA-HIGH-03"][0][1]
php_code = "<?php\n" + schema_high_03_code
proc = subprocess.run(["php", "-l"], input=php_code, text=True, capture_output=True)
print(f"php -l returncode: {proc.returncode}")
print(proc.stdout.strip())
if proc.returncode != 0:
    print("ERROR stderr:", proc.stderr)

# Test target 4: FRONT-HIGH-02
print("\n--- Testing FRONT-HIGH-02 ---")
# FRONT-HIGH-02 has 1 js code block that contains BEFORE and AFTER or comments
front_high_02_code = results["FRONT-HIGH-02"][0][1]
# Let's extract the AFTER block or full block
# Let's see if full block is valid JS or if it has comments and code
proc = subprocess.run(["node", "-c"], input=front_high_02_code, text=True, capture_output=True)
print(f"node -c returncode: {proc.returncode}")
print(proc.stdout.strip())
if proc.returncode != 0:
    print("stderr:", proc.stderr)

# Test target 5: PERF-MED-01
print("\n--- Testing PERF-MED-01 ---")
perf_med_01_code = results["PERF-MED-01"][0][1]
php_code = "<?php\n" + perf_med_01_code
proc = subprocess.run(["php", "-l"], input=php_code, text=True, capture_output=True)
print(f"php -l returncode: {proc.returncode}")
print(proc.stdout.strip())
if proc.returncode != 0:
    print("ERROR stderr:", proc.stderr)
