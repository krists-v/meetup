# Meetup

Meetup is a small web app for organising sports with friends. A group can create a padel, basketball, football, tennis, hockey, volleyball, or other event, paste one invite link into WhatsApp, and collect Going / Maybe / Not going replies. It is meant to be used on a phone.

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

Feature tests cover creating an event, public versus private visibility, joining through an invite link, RSVP capacity, creator-only edit and delete, time polls (voting, private visibility, and locking with the capacity limit).

Laravel Boost is a dev dependency for Cursor. Its generated guidelines are in `AGENTS.md`, with skills and MCP config under `.cursor/`. Production installs skip dev dependencies, so Boost is not part of the Railway image.

## Deploy on Railway

The repo includes a `Dockerfile` and `railway.toml`. Railway builds the image, runs `php artisan migrate --force` on each boot, and serves the app on Railway's `PORT`.

Set these variables on the service:

| Variable | Value |
| --- | --- |
| `APP_KEY` | output of `php artisan key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | the public https URL Railway gives the service |

`APP_URL` is what invite links use, so set it before sharing links.

### SQLite on a volume

Leave `DATABASE_URL` unset. The container stores the database at `/data/database.sqlite`. In the Railway service, add a volume mounted at `/data` so the file survives deploys and restarts. Use one instance. SQLite does not suit multiple replicas writing at once.

### Postgres

Add the Railway Postgres plugin. It sets `DATABASE_URL`. Do not set `DB_CONNECTION=sqlite`. On boot the app sees `DATABASE_URL`, uses the Postgres connection, and migrates. The Railway URL's `sslmode` is honoured. You do not need the `/data` volume in this mode.

Sessions and cache use the database, so they persist with either option. The health check path is `/up`.

## Next steps

- Per-event reminders the day before a game.
- A simple "need a goalkeeper" note, still without a chat thread.
- Time zones per user, so kickoff times match the group.
- Email or WhatsApp click-to-chat that pre-fills the invite message.
