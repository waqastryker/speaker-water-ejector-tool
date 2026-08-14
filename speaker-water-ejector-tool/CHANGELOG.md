# Changelog

All notable changes to **Speaker Water Ejector Tool** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased] — live on site, not yet committed

> ⚠️ The version currently running on [getfixmyspeakers.com](https://getfixmyspeakers.com)
> contains the changes below, but they are **not in this repository**. The live
> files were edited directly on the server without a version bump, so the site
> still reports `1.1.0`. These changes were identified by comparing the live
> assets against the 1.1.0 source. Retrieve the live files from the server and
> commit them as **1.2.0**.

### Added (live only)

- **Ear Speaker Cleaner Mode** — 280–420 Hz, gain 0.28, 9 Hz pulse. A fourth
  cleaning mode aimed at the earpiece speaker rather than the loudspeaker.
- **Ear Speaker Test** — an eighth button in the speaker sound test section.
- **"Call volume is low" diagnosis option** — a sixth symptom in the quiz,
  routing to Ear Speaker Cleaner Mode and the Ear Speaker Test.

---

## [1.1.0] — 2026-07-26

The version in this repository. Adds the shortcode-scoped asset loading and the
diagnosis quiz.

### Added

- **Three cleaning modes**, each with its own frequency profile, gain and pulse rate:

  | Mode | Frequency | Gain | Pulse |
  | --- | --- | --- | --- |
  | Water Eject | 150–220 Hz | 0.50 | 6 Hz |
  | Deep Vibration | 120–180 Hz | 0.55 | 4 Hz |
  | Gentle Clean | 180–250 Hz | 0.32 | 7 Hz |

- **Speaker sound test** with seven tones: Left, Right, Both, Treble (2–8 kHz),
  Bass (150–400 Hz), Voice Range (300–3400 Hz) and Distortion Sweep (500–2000 Hz).
  Left/right separation uses a stereo panner.
- **Troubleshooting quiz** covering five symptoms — muffled sound, water inside,
  one side not working, volume too low, and crackling — each mapped to a
  recommended mode with a direct call-to-action.
- **Circular countdown dial** with an animated SVG progress ring and a 30-second
  auto-stop timer.
- **JSON-LD schema output** — `WebApplication` and `HowTo`, with optional
  `FAQPage`. Two filters are provided to avoid duplicate schema when Rank Math
  is already configured for the page:
  - `swe_tool_disable_faq_schema` — disables FAQ schema only
  - see the plugin header for the filter that disables all schema output
- **Safety disclaimer** stating clearly what the tool cannot fix.
- `prefers-reduced-motion` support — animations are disabled on request.

### Technical

- Vanilla JavaScript, no external dependencies.
- Web Audio API oscillators — no audio files are downloaded.
- CSS and JS are enqueued **only** on pages whose content actually contains the
  `[speaker_water_ejector]` shortcode, rather than site-wide.
- All styles scoped with the `.swe-` prefix to avoid collisions with the Kadence
  theme or other plugins.
- Script is safe to run more than once if a page renders the shortcode twice.
- Built against Kadence, Rank Math and LiteSpeed Cache.

---

## [1.0.0] — Initial release

### Added

- First working version of the browser-based speaker water ejector tool.
- `[speaker_water_ejector]` shortcode.

---

[Unreleased]: https://github.com/waqastryker/speaker-water-ejector-tool/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/waqastryker/speaker-water-ejector-tool/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/waqastryker/speaker-water-ejector-tool/releases/tag/v1.0.0
