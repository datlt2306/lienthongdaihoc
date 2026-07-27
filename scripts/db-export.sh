#!/usr/bin/env bash
set -euo pipefail

CONTAINER="lienthongdaihoc_db"
DB_USER="wordpress"
DB_PASS="wordpress"
DB_NAME="wordpress"
OUTPUT="${1:-database.sql}"

echo "==> Exporting database from ${CONTAINER}..."
docker exec -i "$CONTAINER" mariadb-dump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$OUTPUT"
echo "==> Done: ${OUTPUT} ($(wc -c < "$OUTPUT") bytes)"
