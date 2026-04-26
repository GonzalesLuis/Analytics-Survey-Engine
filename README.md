## AnalyticsEngine (Thesis Project)

A Laravel web app for running a **tutoring session evaluation workflow** and generating **analytics metrics + rubric-based interpretations** from multiple surveys.

The app guides a user through a session lifecycle:
- Start a tutoring session
- Answer **pre-session** (baseline) survey
- End the session
- Answer **post-session** surveys (reflection, satisfaction, compatibility, tutor performance)
- Answer **tutee evaluation**
- View the **computed metrics** and **dimension breakdowns** on the results screen

### What gets computed

The system stores:
- **Raw responses** (per-question scores)
- **Derived dimension scores** (per survey dimension: average + normalized 0..1)
- **Derived metrics** (session-level metrics like SRLG/PLG/TMES and their components)
- **Rubric interpretations** for each metric result (status level + interpretation + recommended action)

### Tech stack

- **Backend**: Laravel (PHP)
- **Frontend tooling**: Node.js + Vite (used via `composer run dev`)
- **Database**: PostgreSQL (recommended; configured via `.env`)

## Project structure (important files)

- **Routes**
  - `routes/web.php`: entry points for the workflow pages
- **Controllers**
  - `app/Http/Controllers/HomeController.php`: start/end session + home page state
  - `app/Http/Controllers/*SurveyController.php`: render/submit surveys
  - `app/Http/Controllers/MetricResultsController.php`: results page
- **Domain logic**
  - `app/Services/SurveyService.php`: loads questions, computes scores/metrics, persists results, matches rubrics
- **Database**
  - `database/migrations/*`: schema for surveys, responses, scores, metrics, and rubrics
  - `database/seeders/SurveySeeder.php`: seeds surveys/dimensions/questions
  - `database/seeders/MetricRubricSeeder.php`: seeds metrics + rubric ranges
  - `database/seeders/DatabaseSeeder.php`: runs the seeders and creates a default test user

## Install requirements (manual)

Install these first:
- **PHP** (compatible with the Laravel version in `composer.json`)
- **Composer**
- **Node.js** (npm)
- **PostgreSQL**

Optional (Windows):
- Use **WSL** for a more Linux-like dev environment (recommended if you run into tooling issues on Windows).

## Alternatively (WSL + Nix dev shell)

If you want a reproducible environment, you can use WSL and Nix.

1) Install Nix in WSL:

```bash
sh <(curl --proto '=https' --tlsv1.2 -L https://nixos.org/nix/install) --no-daemon
```

2) Enable flakes:

```bash
mkdir -p ~/.config/nix
echo "experimental-features = nix-command flakes" >> ~/.config/nix/nix.conf
```

3) Enter the dev shell:

```bash
nix develop
```

## Clone and install dependencies

```bash
git clone <your-repo-url>
cd AnalyticsEngine

npm install
composer install
```

## Initial setup (first run)

1) Create your environment file:

```bash
cp .env.example .env
php artisan key:generate
```

2) Configure PostgreSQL connection in `.env`.

Example:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=peer_matching
DB_USERNAME=dev
DB_PASSWORD=123
```

3) Create the database in Postgres (if it doesn’t exist yet).

4) Run migrations:

```bash
php artisan migrate
```

## Seeding the database (required)

Seeding is required because survey pages are **data-driven** (questions come from the database).

Run:

```bash
php artisan db:seed
```

This will:
- Create a default test user (see `database/seeders/DatabaseSeeder.php`)
- Seed all surveys/dimensions/questions (`SurveySeeder`)
- Seed metrics and rubric ranges (`MetricRubricSeeder`)

If you ever want to reset everything:

```bash
php artisan migrate:fresh --seed
```

## Run the project (development)

Start the dev server (Laravel + Vite):

```bash
composer run dev
```

Then open:
- `http://127.0.0.1:8000`

## Notes for VS Code (especially WSL)

- If you're using WSL, install and run **VS Code through WSL** so PHP/Laravel tooling (LSP, formatters) detects the right executables.
- If you're not using WSL, make sure VS Code is pointing to the correct `php` executable in your system PATH.

## Workflow summary (how to use the app)

1) Go to the home page (`/`)
2) Click **Start session**
3) Complete **Pre-session** survey
4) Click **End session**
5) Complete **Post-session** surveys
6) Complete **Tutee evaluation**
7) View **Metric results** (`/metric_results`)
