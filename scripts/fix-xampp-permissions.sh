#!/usr/bin/env bash
# Betulkan kebenaran storage untuk XAMPP (Apache = user daemon).
# Jalankan dari root projek: sudo bash scripts/fix-xampp-permissions.sh

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "Membetulkan kebenaran storage & bootstrap/cache di: $ROOT"

# XAMPP macOS: Apache berjalan sebagai daemon
if id daemon &>/dev/null; then
  chown -R daemon:staff storage bootstrap/cache
fi

chmod -R 775 storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;

# Pastikan direktori kritikal boleh ditulis
mkdir -p storage/logs storage/framework/{cache,sessions,views} storage/app/{private/applications,temp}
chmod -R 775 storage/logs storage/framework storage/app/private storage/app/temp bootstrap/cache

echo "Selesai. Cuba muat semula: http://localhost/Alp2-main/public/permohonan/baharu"
