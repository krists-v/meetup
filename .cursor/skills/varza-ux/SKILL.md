---
name: varza-ux
description: "Varza UX and UI practices. Use when changing screens, layout, navigation, cards, forms, empty states, icons, or motion. Triggers on hierarchy, primary action, info row, mobile sticky button, accessibility, and empty states."
---

# Varza UX

One screen, one next step. The person should know what to do and where to look without reading the whole page.

Visual tokens, the icon set, and the motion kit are in `.cursor/rules/varza-design.mdc`. Copy is in the `varza-voice` skill. Follow both.

The logged-in app stays calmer than the homepage. No mesh, grain, glass, or hero entrance inside the app.

## Hierarchy

- One primary action per screen. Everything else is `secondary`, `ghost`, or a text link.
- Page title first, then the primary action, then supporting facts.
- Do not make two filled pitch-green buttons compete. The sticky mobile action and the in-page primary are the same action, not two different ones.
- Marketing pages may use a larger headline. App pages stay `text-2xl` or `text-3xl`.

## Info row

Every fact on a card or a game page is an info row: icon, then text. Never a bare line of metadata.

| Fact | Icon | Text |
| --- | --- | --- |
| Date | `calendar` | Thu 10 Dec |
| Time | `clock` | 18:00 |
| Place | `map-pin` | Park pitch, as a maps link |
| Spots | `users` | 3 of 8 in · 5 spots left |
| Level | `signal` | Beginners welcome, or the level |
| Outdoor | `sun` | Outdoors |
| Indoor | `home` | Indoors |

Icon is Lucide, 20px, `text-pitch-700`, `aria-hidden`. Text is `text-base text-ink`. A short label above it, when the row is alone on a page, is `text-sm text-ink-muted`. Gap is `gap-3`. The row's hit target is at least 44px when it is a link.

## Cards

Same anatomy on every game card:

1. Activity illustration, full width, top.
2. Activity chip.
3. Title.
4. Info rows: when, where, spots. Indoor or outdoor and level when they exist.
5. One action, or the whole card links to the game and the place link sits outside that link.

Do not invent a second card shape for public games, the dashboard, or "this weekend".

## Actions and feedback

- Mobile: the primary action sticks above the bottom nav, full width, `min-h-11`, with `pb` so it does not cover the last row.
- Every tap answers. Press: `motion-safe:active:scale-[0.98]`. Waiting: button disabled and the label changes ("Saving…"). Success: one-line status in the existing banner.
- Destructive actions stay `danger` and ask first ("Cancel this game?").
- Do not leave a control enabled if the tap does nothing.

## Empty states

Use `x-ui.empty-state`. Title, one sentence, one action. The action is the next step from `varza-voice` ("Start a game" or "See what's on"), not a dead end.

## Forms

- Every control has a `x-ui.label`. No placeholder-only fields.
- Errors use `x-ui.error` under that field, as soon as the save fails.
- Defaults: a near evening, public visibility, a known activity if they have a favorite. Do not default the place to a fake address.
- Inputs are at least 16px so iOS does not zoom. Touch targets are at least 44px (`min-h-11`).

## Accessibility

- Text on pitch-700 buttons is white. Muted text stays `ink-muted` on white or canvas, not gray on green.
- Focus rings stay `ring-pitch-600` with an offset. Do not remove them.
- Icon-only buttons need an accessible name. Decorative icons are `aria-hidden`.
- Status banners use `role="status"`.
- Motion follows the kit in the design rule. `prefers-reduced-motion: reduce` is already global. Do not override it. No transform, pulse, or entrance when it is set.

## Checklist

1. Can you point to the one primary action in one second?
2. Are date, time, place, spots, level, and indoor or outdoor info rows, each with its icon?
3. Do cards share the anatomy above?
4. Is the mobile primary sticky, and the same action as the one in the page?
5. Does every tap show press, loading, or success?
6. Does the empty state offer a next step?
7. Does every field have a label, an inline error, and a sensible default?
8. Are targets at least 44px, contrast fine, focus visible, and reduced motion left alone?
