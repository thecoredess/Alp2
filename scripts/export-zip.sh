#!/usr/bin/env bash
# Eksport projek ALP sebagai ZIP (elak fail permission denied dari XAMPP/Apache).
# Jalankan: bash scripts/export-zip.sh
# Output: ~/Desktop/Alp2-main-YYYYMMDD.zip

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
NAME="$(basename "$ROOT")"
STAMP="$(date +%Y%m%d-%H%M)"
OUT="${HOME}/Desktop/${NAME}-${STAMP}.zip"

cd "$(dirname "$ROOT")"

echo "Mengeksport: $ROOT"
echo "Destinasi : $OUT"

zip -r "$OUT" "$NAME" \
  -x "${NAME}/vendor/*" \
  -x "${NAME}/node_modules/*" \
  -x "${NAME}/.env" \
  -x "${NAME}/.git/*" \
  -x "${NAME}/storage/logs/*" \
  -x "${NAME}/storage/framework/cache/*" \
  -x "${NAME}/storage/framework/cache/data/*" \
  -x "${NAME}/storage/framework/views/*" \
  -x "${NAME}/storage/framework/sessions/*" \
  -x "${NAME}/storage/app/private/applications/*" \
  -x "${NAME}/storage/app/temp/*" \
  -x "${NAME}/public/build/*" \
  -x "${NAME}/public/hot" \
  -x "${NAME}/.phpunit.result.cache" \
  -x "${NAME}/.DS_Store" \
  -x "${NAME}/*/.DS_Store"

echo ""
echo "Selesai: $OUT"
ls -lh "$OUT"
echo ""
echo "Nota:"
echo "  • vendor/ & node_modules/ dikecualikan — jalankan composer install && npm ci && npm run build"
echo "  • .env dikecualikan — salin .env.example ke .env"
echo "  • Muat naik/lampiran (storage/app/private) dikecualikan"
