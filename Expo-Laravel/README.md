# UniHack 2025 - Exposition Registry

Curate, publish, and explore 3D galleries built for UniHack 2025. This Laravel 12 + Livewire 3 application lets exhibitors upload OBJ/MTL assets, configure themed virtual spaces, and gather community engagement through likes and comments.

## Table of contents

1. [Highlights](#highlights)
2. [Tech stack](#tech-stack)
3. [Prerequisites](#prerequisites)
4. [Quick start](#quick-start)
5. [Local development](#local-development)
6. [Database & storage](#database--storage)
7. [Testing](#testing)
8. [Project layout](#project-layout)
9. [Troubleshooting](#troubleshooting)
10. [License](#license)

## Highlights

- Manage public or private expositions using the `ExpositionsManager` Livewire experience with preset themes and custom environment assets.
- Upload curated exhibits with OBJ/MTL files, optional textures (up to 10), and automatic media storage per exhibit.
- Showcase engagement through likes, threaded comments, and real-time exhibit size adjustments within `ExpositionExhibits`.
- Discover public galleries with the type-ahead `ExpoScan` search that filters by title, creator, or numeric IDs and sorts by likes or recency.
- Starter homepage and dashboard views powered by Laravel Breeze authentication and Tailwind-styled Volt components.

## Tech stack

- **Backend:** Laravel 12 (PHP 8.2), Eloquent ORM, Breeze auth scaffolding
- **Realtime UI:** Livewire 3 + Volt, file uploads, queue-ready events
- **Frontend tooling:** Vite 7, Tailwind CSS 3, @tailwindcss/forms
- **Build tooling:** Composer scripts, NPM scripts, `concurrently` for unified dev loop
- **Testing:** Pest 3 with Laravel plugin

## Prerequisites

- PHP 8.2+ with `ext-fileinfo`, `ext-json`, `ext-openssl`, and SQLite/MySQL/Postgres driver of your choice
- Composer 2.7+
- Node.js 18+ and npm 10+
- SQLite (bundled) or another database supported by Laravel

## Quick start

```bash
git clone https://github.com/lluca1/unihack-2025-ereg.git
cd unihack-2025-ereg

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

npm install
npm run build   # or npm run dev for watch mode
```

Update `.env` with your DB connection (defaults to SQLite) plus any mail/login providers you need before running migrations.

## Local development

- **All-in-one loop:** `composer run dev` spins up `php artisan serve`, queue listeners, pail logs, and Vite with colored output.
- **Manual control:**
  - `php artisan serve` to expose the API/UI
  - `php artisan queue:listen --tries=1` for background jobs if you enable them later
  - `npm run dev` for hot module reload via Vite

Default routes:

- `/` - Public landing page with featured expositions
- `/dashboard` - Auth-only hub
- `/expositions` - CRUD surface for a curator's own galleries
- `/expositions/{id}` - Exhibit workspace with uploads, likes, comments, and environment controls

## Database & storage

- Run `php artisan migrate --seed` after configuring the database. The seeder creates a demo `test@example.com` account for quick sign-in.
- File uploads are stored on the `public` disk (`storage/app/public`). Make sure `php artisan storage:link` is executed so `public/storage` serves assets.
- Model uploads enforce the following caps:
  - OBJ: `max:512000` (approx 500 MB)
  - MTL: `max:51200` (approx 50 MB)
  - Textures: up to 10 x 20 MB each (`png|jpg|jpeg|bmp|webp`)
  - Ambient audio: 30 MB (`mp3|wav|ogg|flac|m4a`)

## Testing

```bash
php artisan test          # Runs the Laravel test suite
./vendor/bin/pest         # Direct Pest runner with watch/coverage options
```

Before pushing changes, clear compiled config for parity with CI: `php artisan config:clear`.

## Project layout

- `app/Livewire/ExpositionsManager.php` - CRUD for expositions, uploads for thumbnails + environment textures
- `app/Livewire/ExpositionExhibits.php` - Exhibit upload pipeline, interactions, likes/comments, and environment controls
- `app/Livewire/ExpoScan.php` - Public search experience for curated spaces
- `routes/web.php` - Home, dashboard, exposition, and auth routes (Breeze)
- `resources/views/` - Blade templates for landing pages plus Livewire views under `livewire/`
- `database/migrations/` - Schema for expositions, exhibits, interactions, and tile layouts

## Troubleshooting

- **Missing media:** Re-run `php artisan storage:link` and confirm the web server can serve `public/storage`.
- **Large uploads failing:** Check PHP `upload_max_filesize` / `post_max_size` and ensure they exceed the Livewire validators listed above.
- **Queues not processing:** Start `php artisan queue:work` or `composer run dev` so Livewire uploads and notifications can finish.
- **Database reset:** Use `php artisan migrate:fresh --seed` during local iterations.

## License

Released under the [GPL-3.0 License](LICENSE). See the license file for details.
