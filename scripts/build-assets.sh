#!/usr/bin/env bash
# Regenerate WordPress.org plugin assets (.wordpress-org/*.png) from the SVG
# sources in scripts/assets-src/. Requires Inkscape.
set -euo pipefail
cd "$(dirname "$0")/.."
SRC=scripts/assets-src
OUT=.wordpress-org
for f in banner-1544x500 banner-772x250 icon-256x256 icon-128x128; do
  inkscape "$SRC/$f.svg" --export-type=png --export-filename="$OUT/$f.png"
done
echo "Wrote $OUT/*.png"
