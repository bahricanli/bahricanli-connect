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
| `screenshot-1.png` | 1280×849 | wp-admin **Connect → Gelen Kutusu** (thread + reply); contact name/number blurred | present |
| `screenshot-2.png` | 1280×849 | wp-admin **Connect → Ayarlar** (API address + key + test) | present |

Icon + banners are the Message Manager green `MM` mark, matching the
`bahricanli-publisher` asset style. Sources: `scripts/assets-src/*.svg` —
regenerate the PNGs with `scripts/build-assets.sh` (needs Inkscape).
Screenshots are captured from a live pkd.org.tr install; personal data
(contact names, phone numbers) is blurred before commit.

## blueprints/blueprint.json

Enables the plugin directory "Live Preview" (WordPress Playground). Minimal: boots
`wp latest` / PHP 8.2, logs in as admin, lands on **Connect → Ayarlar**. The plugin
itself is auto-installed/activated by the directory — no `installPlugin` step here.
The inbox needs a real Message Manager API key, so the preview only shows settings.
Deployed to SVN `assets/blueprints/blueprint.json` by the tag workflow.
