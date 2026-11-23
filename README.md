# Campaign CTAs

Reusable call-to-action banners for WordPress posts. Build a CTA once, place it in any post with a block, and update it in one place when the campaign changes. Posts without a CTA get a sensible fallback automatically.

Originally built in 2019 for a large corporate blog, to replace one-off banner images that went stale; rebuilt from scratch as a standalone plugin.

## Features

- **CTAs as content:** each CTA is its own post with a heading, body, button and optional image (the featured image), plus a color theme.
- **CTA block:** writers pick a CTA from a dropdown and see a live preview.
- **One source of truth:** posts store only the CTA's ID, so editing a CTA updates every post that uses it.
- **Fallbacks:** posts with no CTA block get the default for their category (or any taxonomy you choose), then a site-wide default.

## Usage

1. Go to **CTAs → Add New** and fill in the content.
2. In a post, add the **CTA** block and choose a CTA.
3. Optional: under **CTAs → Settings**, choose a site-wide default and the taxonomy used for fallbacks. Then set a **Default CTA** on each category's edit screen.

## Requirements

WordPress 6.1+ and PHP 7.4+. No build step and no other plugins required.

## License

GPL-2.0-or-later
