---
name: varza-new-activity
description: "Checklist for adding an activity to EJAM. Use when adding a sport, activity, category, indoor or outdoor default, beginners badge, activity icon, accent color, illustration, or OG image."
---

# Add an activity

One activity touches the list, the picture, the share card, and the tests. Do all of them in the same change. Copy follows `varza-voice`. Color and icon follow `.cursor/rules/varza-design.mdc`.

The catalog is `Event::ACTIVITIES` and `Event::SPORTS` in `app/Models/Event.php`. A game stores `setting` (`indoor` or `outdoor`) and a nullable `level` (`beginner`, `intermediate`, `advanced`, `pro`; null means all levels). Keep those two lists on the same slugs.

## Checklist

1. Slug. Lowercase, stable, a key in `Event::SPORTS`. Do not rename a shipped slug.
2. Label. The word people say: "Table tennis", not "TT".
3. Category. One of Racket, Team, Indoor, Outdoor, Social, stored on `Event::ACTIVITIES`. Custom `other` stays uncategorised.
4. Icon. A Lucide name on `x-ui.icon`. No new icon set, no emoji.
5. Accent. One `--activity-*` token. Chips, tints, and the illustration only. Pitch green stays the button color. No Tailwind default palette (`indigo`, `orange`, `sky`, `rose`).
6. Illustration. A `@case` in `resources/views/components/illustrations/sport.blade.php`. Flat, pitch green, white, ink, sand, plus that one accent. Unknown slugs fall through to `other`.
7. OG image. `public/images/og/{slug}.png`. `LinkPreview::image()` already maps the slug to that path. Also check the default image still covers a missing file.
8. Indoor or outdoor default on `Event::ACTIVITIES`. Games copy it into `setting` and can change it. Show it as the sun or home info row.
9. Copy. One line a stranger understands ("Climbing, indoors, beginners welcome."). Run the `varza-voice` checklist. "Beginners welcome" is the badge label when the activity is for new people.
10. Tests. `SportPickerTest` loops `Event::SPORTS`, so a new slug is created and shown. Also cover the filter chip, the favorite-sport validation (`ProfileUpdateRequest`), and the OG image URL for that slug.

## Do not

- Add a one-off color on a single Blade file.
- Leave the illustration or the OG image on `other` and call it done.
- Mention WhatsApp in the activity's marketing line.
