# GoogleAnalytics-SpeedBoost

Improve your Lighthouse / PageSpeed / GTmetrix scores by **not loading Google Analytics until the visitor consents**. The plugin shows a small consent popup and only injects the GA tag after the visitor clicks **Accept** — and lets them withdraw consent later.

## Features

- GA loads **only after consent** (better performance and privacy).
- Configurable popup text and Accept/Reject button labels.
- **No jQuery** — a few KB of vanilla JS.
- Withdraw consent anywhere with the `[ga_consent_reset]` shortcode.
- The popup only appears once a GA Measurement ID is configured.

## Setup

1. Activate the plugin.
2. Go to **GDPR Popup** in the admin menu and enter your GA Measurement ID (e.g. `G-XXXXXXXXXX`).
3. Optionally customise the popup text and buttons.

## What changed in 2.0.0

- **Removed the jQuery dependency.** 1.0 enqueued jQuery even though the script was pure vanilla JS — counter-productive for a speed plugin.
- **Hardened consent handling.** Cookies are parsed exactly (no substring false matches) and written with `SameSite=Lax`. The GA ID is validated on save.
- **Added consent withdrawal** via `[ga_consent_reset]` (a GDPR requirement that 1.0 lacked).
- The popup and scripts now load **only when a GA ID is set**, plus basic dialog accessibility and nicer default styling.

## License

GPLv2 or later — see [LICENSE](LICENSE).

**Author:** [Finland93](https://github.com/Finland93)
