#!/usr/bin/env bash
# Eklenti dizininin ÜST dizinine yayın zip'i üretir.
# Zip içeriği `bahricanli-connect/` klasörüyle başlar (WordPress bunu bekler).
# git archive kullanır: .git ve takip edilmeyen dosyalar zip'e GİRMEZ,
# .gitattributes'taki `export-ignore` girdileri (scripts/, .github/ vb.) hariç tutulur.
set -euo pipefail

cd "$(dirname "$0")/.."

SLUG="$(basename "$PWD")"
MAIN="${SLUG}.php"
VERSION="$(grep -m1 -E '^\s*\*\s*Version:' "$MAIN" | grep -oE '[0-9]+(\.[0-9]+)*')"

OUT_DIR="$(cd .. && pwd)"
OUT="${OUT_DIR}/${SLUG}.zip"

rm -f "$OUT"
git archive --format=zip --prefix="${SLUG}/" -o "$OUT" HEAD

echo "Oluşturuldu: ${OUT}  (v${VERSION:-?})"
unzip -l "$OUT" | tail -n +4 | head -n 20
