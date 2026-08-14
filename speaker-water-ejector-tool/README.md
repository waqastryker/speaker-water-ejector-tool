# Speaker Water Ejector Tool

A WordPress plugin that adds a browser-based speaker water ejector, speaker sound
test, and troubleshooting quiz to any page or post via a shortcode.

It plays calibrated low-frequency tones through the device speaker to help push
out trapped water and loose dust. No app, no signup, no audio files downloaded —
everything is generated in the browser with the Web Audio API.

Live at **[getfixmyspeakers.com](https://getfixmyspeakers.com)**.

---

## Installation

1. Upload the `speaker-water-ejector-tool` folder to `/wp-content/plugins/`.
2. Activate **Speaker Water Ejector Tool** in the WordPress Plugins screen.
3. Add the shortcode to any page or post:

   ```
   [speaker_water_ejector]
   ```

Assets load **only** on pages that contain the shortcode, so the rest of the site
is unaffected.

---

## Requirements

- WordPress with a theme that supports shortcodes
- A browser with Web Audio API support (all current mobile and desktop browsers)
- JavaScript enabled — a fallback notice is shown when it is not

---

## Features

### Cleaning modes

| Mode | Frequency | Gain | Pulse |
| --- | --- | --- | --- |
| Water Eject | 150–220 Hz | 0.50 | 6 Hz |
| Deep Vibration | 120–180 Hz | 0.55 | 4 Hz |
| Gentle Clean | 180–250 Hz | 0.32 | 7 Hz |

Each cycle runs for 30 seconds with a circular countdown dial and stops
automatically.

### Speaker sound test

Seven test tones: Left, Right, Both, Treble (2–8 kHz), Bass (150–400 Hz), Voice
Range (300–3400 Hz), and Distortion Sweep (500–2000 Hz). Left/right separation
uses a stereo panner node, so it genuinely tests channel balance.

### Troubleshooting quiz

Five symptoms — muffled sound, water inside, one side not working, volume too
low, crackling — each mapped to a recommended mode with a direct call-to-action.

---

## Structure

```
speaker-water-ejector-tool/
├── speaker-water-ejector-tool.php   Main plugin file, shortcode, schema output
├── templates/
│   └── tool-template.php            Front-end markup
├── assets/
│   ├── speaker-water-ejector.js     Audio engine, timer, test, quiz
│   └── speaker-water-ejector.css    Scoped styles (.swe- prefix)
├── CHANGELOG.md
├── LICENSE
└── README.md
```

---

## Schema output

The plugin outputs its own JSON-LD — `WebApplication` and `HowTo`, and optionally
`FAQPage` — so the tool page has structured data even when Rank Math's schema
generator is not configured for it.

**If you already build FAQ schema manually in Rank Math for this page, do not run
both.** Duplicate `FAQPage` schema on one URL can cost you rich result
eligibility. To disable only this plugin's FAQ schema:

```php
add_filter( 'swe_tool_disable_faq_schema', '__return_true' );
```

See the plugin file header for the filter that disables all schema output.

---

## What this tool cannot do

Stated plainly, because the site states it plainly: sound vibration can help move
small amounts of trapped water and loose dust. It cannot repair a torn speaker
membrane, corroded contacts, or any internal hardware damage. If sound quality
does not improve after two cycles, the problem needs a repair technician.

---

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

WordPress is GPL-licensed and plugins are derivative works of it, so any
distributed plugin must use a GPL-compatible license.
