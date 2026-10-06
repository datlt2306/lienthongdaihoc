#!/usr/bin/env bash
set -euo pipefail

CONTAINER="lienthongdaihoc_db"
DB_USER="wordpress"
DB_PASS="wordpress"
DB_NAME="wordpress"
INPUT="${1:-database.sql}"

if [ ! -f "$INPUT" ]; then
  echo "ERROR: File '${INPUT}' not found!"
  exit 1
fi

echo "==> Importing ${INPUT} into ${CONTAINER}..."
docker exec -i "$CONTAINER" mariadb -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$INPUT"
echo "==> Done."
