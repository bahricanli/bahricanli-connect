# WordPress.org plugin assets

Files here are pushed to the SVN `assets/` directory by the deploy workflow
(`ASSETS_DIR: .wordpress-org`). They are **not** shipped inside the plugin zip.

See https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/

| File | Size | Purpose | Status |
|------|------|---------|--------|
| `icon-256x256.png` | 256×256 | Plugin directory icon | present |
| `icon-128x128.png` | 128×128 | Plugin directory icon (fallback) | present |
| `banner-1544x500.png` | 1544×500 | Plugin page banner (hi-DPI) | present |
| `banner-772x250.png` | 772×250 | Plugin page banner | present |
| `screenshot-1.png` | any | Matches `== Screenshots ==` line 1 in `readme.txt` | TODO |

Icon + banners are the Message Manager green `MM` mark, matching the
`bahricanli-publisher` asset style. Sources: `scripts/assets-src/*.svg` —
regenerate the PNGs with `scripts/build-assets.sh` (needs Inkscape).
`screenshot-1.png` (a wp-admin **Connect → Inbox** capture) still needs to be
added, along with a `== Screenshots ==` section in `readme.txt`.
