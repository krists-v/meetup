# Varza

Varza is a small web app for organising sports with friends. A group can create a padel, basketball, football, tennis, hockey, volleyball, or other event, paste one invite link into WhatsApp, and collect Going / Maybe / Not going replies. It is meant to be used on a phone.

The default app name is Varza. If a host still has `APP_NAME` set to an older value, change it to `Varza`.

Public events show up for every logged-in user. Private events are visible only to the creator and to people who open the invite link or join.

## What you can do

- Register, log in, and log out.
- Create an event with a title, sport, a fixed date and time or a few times for friends to vote on, location, optional participant limit, optional description, and public or private visibility.
- On a time poll, anyone who can see the event votes Yes, Maybe, or No. The best time (most Yes, then most Maybe) is highlighted. The creator can lock that time, which turns it into a normal event and marks Yes voters as Going while there is room.
- Copy a unique invite link and send it in WhatsApp. Opening it after login shows the event, including private ones.
- RSVP Going, Maybe, or Not going. Going is blocked once the event is full. Maybe and Not going stay available.
- See who is going, maybe, or not going, with counts.
- Edit or cancel an event you created.
- On the dashboard, see your upcoming events (created or joined) and other public upcoming events.

## Local setup

Requirements: PHP 8.2+ with `pdo_sqlite`, Composer, and Node.js (for the CSS and JS build).

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open http://127.0.0.1:8000.

`php artisan db:seed` creates three demo users. The password for each is `password`.

| Name | Email |
| --- | --- |
| Alex Rivera | alex@example.com |
| Sam Okonkwo | sam@example.com |
| Jordan Lee | jordan@example.com |

The database defaults to SQLite at `database/database.sqlite`. No other services are required.

## Tests

```bash
php artisan test
```

Feature tests cover creating an event, public versus private visibility, joining through an invite link, RSVP capacity, the waitlist and its promotion email, calendar files, repeating a game, WhatsApp sharing, profile photos and favorite sports, game comments, creator-only edit and delete, and time polls (voting, private visibility, and locking with the capacity limit).

Laravel Boost is a dev dependency for Cursor. Its generated guidelines are in `AGENTS.md`, with skills and MCP config under `.cursor/`. Production installs skip dev dependencies, so Boost is not part of the Railway image.

## Deploy on Railway

The repo includes a `Dockerfile` and `railway.toml`. Railway builds the image, runs `php artisan migrate --force` on each boot, and serves the app on Railway's `PORT`.

Railway does not run cron. On boot the container also starts `php artisan schedule:work`, so `join-requests:expire` runs every minute beside the web server. That command sends the mail. A web request only clears a due request so the page can say "Request expired"; it does not call the mailer. If a send fails, the row stays unmarked and the next minute tries again. A pending request expires 24 hours after it was made, or 2 hours before the game starts, whichever comes first. A poll has no start time yet, so only the 24 hour limit applies. The same scheduler sends `games:remind`: one note about three hours before a game, to each person who is going and to the host. A saved row keeps it to one send. Someone who left, a cancelled game, and a person who turned reminders off are skipped. A missing mail key still uses the log mailer, and a failed send is left unmarked so the next minute can try again.

Set these variables on the service:

| Variable | Value |
| --- | --- |
| `APP_KEY` | output of `php artisan key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | the public https URL Railway gives the service |
| `APP_NAME` | `Varza` |
| `APP_TIMEZONE` | `Europe/Riga`. Calendar files and Google Calendar links use this zone. |
| `QUEUE_CONNECTION` | `sync`. The image already sets this. Do not add a queue worker. |
| `MAIL_MAILER` | `resend` |
| `RESEND_API_KEY` | API key from Resend. Leave it empty and mail is written to the log instead of failing. |
| `MAIL_FROM_ADDRESS` | a sender address on a domain you have verified in Resend |
| `MAIL_FROM_NAME` | `Varza` |
| `CONTACT_EMAIL` | address on the privacy and terms pages. Player reports are emailed here when it is set. Leave it blank and the report is still saved. |
| `LOG_CHANNEL` | `stderr` in production, so logs show up in Railway. The image sets this, and `docker/entrypoint.sh` falls back to stderr when it is unset. Local `.env` keeps `stack` / `single`, which writes `storage/logs/laravel.log`. |
| `SENTRY_LARAVEL_DSN` | optional. Leave it empty and Sentry stays off: the app still boots and serves requests, and it does not call Sentry. |
| `SENTRY_TRACES_SAMPLE_RATE` | `0`. Raise it only if you want performance traces. |
| `SENTRY_SEND_DEFAULT_PII` | leave unset. It defaults to false, so Sentry does not receive user data, cookies, or IP addresses. |

`APP_URL` is what invite links and password-reset links use, so set it before sharing links. In production the app forces HTTPS URLs and marks the session cookie secure. Railway's proxy headers are trusted, so that HTTPS check sees the original request.

Password reset emails use the Varza mail theme. Private games are shared with a link, not an email address, so Varza does not send an invite email.

### SQLite on a volume

Leave `DATABASE_URL` unset. The container stores the database at `/data/database.sqlite`, profile photos at `/data/avatars`, and game photos at `/data/photos`. In the Railway service, add a volume mounted at `/data` so they survive deploys and restarts. Profile photos are limited to 2 MB. When PHP GD is available, Varza resizes them to 256 pixels and turns phone photos upright. Use one instance. SQLite does not suit multiple replicas writing at once.

### Postgres

Add the Railway Postgres plugin. It sets `DATABASE_URL`. Do not set `DB_CONNECTION=sqlite`. On boot the app sees `DATABASE_URL`, uses the Postgres connection, and migrates. The Railway URL's `sslmode` is honoured. Profile photos still use `/data/avatars` and game photos use `/data/photos`, so keep a volume mounted at `/data`, or set `AVATAR_ROOT` and `PHOTO_ROOT` to other paths that survive deploys.

Sessions and cache use the database, so they persist with either option. The health check path is `/up`.

`.github/workflows/uptime.yml` requests that URL every 15 minutes. Set the repository variable `UPTIME_URL` to check a different address. A failure opens a GitHub issue labelled `downtime`, or comments on the open one. The next successful check closes it.

## Next steps

- Per-event reminders the day before a game.
- A simple "need a goalkeeper" note, still without a chat thread.
- Time zones per user, so kickoff times match the group.
- Email or WhatsApp click-to-chat that pre-fills the invite message.
