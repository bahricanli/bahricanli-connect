# WordPress.org plugin assets

Files here are pushed to the SVN `assets/` directory by the deploy workflow
(`ASSETS_DIR: .wordpress-org`). They are **not** shipped inside the plugin zip.

Add the following PNGs (see https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/):

| File | Size | Purpose |
|------|------|---------|
| `icon-256x256.png` | 256×256 | Plugin directory icon |
| `icon-128x128.png` | 128×128 | Plugin directory icon (fallback) |
| `banner-1544x500.png` | 1544×500 | Plugin page banner (hi-DPI) |
| `banner-772x250.png` | 772×250 | Plugin page banner |
| `screenshot-1.png` | any | Matches `== Screenshots ==` line 1 in `readme.txt` |

Until real art is added the deploy step just skips assets with a warning.
