---
name: varza-ux
description: "Ejam UX and UI practices. Use when changing screens, layout, navigation, cards, forms, empty states, icons, or motion. Triggers on hierarchy, primary action, info row, mobile sticky button, accessibility, and empty states."
---

# Ejam UX

One screen, one next step. The person should know what to do and where to look without reading the whole page.

Visual tokens, the icon set, and the motion kit are in `.cursor/rules/varza-design.mdc`. Copy is in the `varza-voice` skill. Follow both.

Every page is the same quiet page: light grey canvas, white cards, 1px grey borders, big type. The main button is near-black with white text. No mesh, grain, glass, gradient, or hero entrance. Color is for status only. One header and one footer, from `x-ui.shell`.

## Hierarchy

- One primary action per screen. Everything else is `secondary`, `ghost`, or a text link.
- Page title first, then the primary action, then supporting facts.
- Do not make two filled buttons compete. The sticky mobile action and the in-page primary are the same action, not two different ones. Primary buttons are near-black with white text. Secondary buttons are soft grey.
- Marketing headlines are `text-5xl` or `text-6xl font-bold`. App page titles are `text-3xl font-bold`.

## Info row

Every fact on a card or a game page is an info row: icon, then text. Never a bare line of metadata.

| Fact | Icon | Text |
| --- | --- | --- |
| Date | `calendar` | Saturday, 21 November · 12:00 |
| Time | `clock` | A poll with times still to pick |
| Place | `map-pin` | Park pitch, as a maps link |
| Spots | `users` | 2 / 4 players |
| Level | `bar-chart` | All levels, or the level |
| Outdoor | `sun` | Outdoors |
| Indoor | `home` | Indoors |

Icon is Lucide, 20px, `text-ink-faint`, `aria-hidden`. Text is `text-lg text-ink` on a card, `text-base text-ink` on a page. A short label above it, when the row is alone on a page, is `text-sm text-ink-muted`. Gap is `gap-3`. The row's hit target is at least 44px when it is a link.

## Cards

Same anatomy on every game card. White, 1px grey border, big radius, generous type. Color only on the status chip. The highlight variant (`highlight` on `x-ui.game-card`) is the one exception: `#F0FAF3` fill, one thin green border, ink text, and the same near-black I'm in button. No second border.

1. Rounded photo. A frosted grey pill on the bottom left names the area. No photo from the host means the bundled picture for that sport.
2. Bold title on the left. Status chip on the right, soft green when there are spots left (`2 spots left`).
3. Grey line icons and larger text: calendar `Saturday, 21 November · 12:00`, map pin for the place, users `2 / 4 players`, bar chart for the level.
4. A thin divider. `Hosted by Alex` in muted grey on the left. Bold `€5 / person` on the right when there is a cost. `Free` when the cost is 0. Hide it when there is no cost.
5. One full-width near-black button, white text: `I'm in`. The map link sits outside the title link.

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

- Primary button text is white on near-black. Secondary text is ink on mist. Danger text stays on the white danger button. Muted text stays `ink-muted` on white.
- Focus rings stay `ring-ink` with an offset. Do not remove them.
- Icon-only buttons need an accessible name. Decorative icons are `aria-hidden`.
- Status banners use `role="status"`.
- Motion follows the kit in the design rule. `prefers-reduced-motion: reduce` is already global. Do not override it. No transform, pulse, or entrance when it is set.

## Checklist

1. Can you point to the one primary action in one second?
2. Are when, place, players, and level info rows, each with its grey icon?
3. Do cards share the anatomy above?
4. Is the mobile primary sticky, and the same action as the one in the page?
5. Does every tap show press, loading, or success?
6. Does the empty state offer a next step?
7. Does every field have a label, an inline error, and a sensible default?
8. Are targets at least 44px, contrast fine, focus visible, and reduced motion left alone?
