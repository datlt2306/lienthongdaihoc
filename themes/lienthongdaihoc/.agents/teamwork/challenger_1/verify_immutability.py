#!/usr/bin/env python3
"""
Immutability Verification Script:
Ensures NO original source code files in the theme were modified.
"""
import os
import sys
import datetime

BASE_DIR = "/Users/ken/Local Sites/lienthongdaihoc/app/public/wp-content/themes/lienthongdaihoc"

# Audit start timestamp threshold: 2026-09-25 11:00:00 local time
AUDIT_START_TIMESTAMP = datetime.datetime(2026, 9, 25, 11, 0, 0).timestamp()

ALLOWED_NEW_OR_MODIFIED_FILES = {
    "FULL_PROJECT_AUDIT_REPORT.md",
    "PROJECT.md",
    "ORIGINAL_REQUEST.md"
}

def check_immutability():
    print("==================================================")
    print("STARTING IMMUTABILITY AUDIT OF THEME REPOSITORY")
    print("==================================================")

    modified_source_files = []
    scanned_count = 0

    for root, dirs, files in os.walk(BASE_DIR):
        # Exclude .agents/ metadata directory
        if "/.agents" in root or root.endswith("/.agents"):
            continue
        if "/node_modules" in root or root.endswith("/node_modules"):
            continue

        for file in files:
            scanned_count += 1
            rel_path = os.path.relpath(os.path.join(root, file), BASE_DIR)
            abs_path = os.path.join(root, file)

            mtime = os.path.getmtime(abs_path)
            dt = datetime.datetime.fromtimestamp(mtime)

            if mtime > AUDIT_START_TIMESTAMP:
                if rel_path not in ALLOWED_NEW_OR_MODIFIED_FILES:
                    modified_source_files.append((rel_path, dt.isoformat()))

    print(f"Total files scanned (excluding .agents & node_modules): {scanned_count}")
    print(f"Source files modified during audit window: {len(modified_source_files)}")

    if modified_source_files:
        print("\n[CRITICAL FAILURE] Detected unexpected modified source files:")
        for path, dt in modified_source_files:
            print(f" - {path} (modified at {dt})")
        sys.exit(1)
    else:
        print("\n[PASS] IMMUTABILITY CONFIRMED: 0 original source files have been modified!")
        print("All theme .php, .js, .css, and template files retain their pristine pre-audit state.")
        sys.exit(0)

if __name__ == "__main__":
    check_immutability()
