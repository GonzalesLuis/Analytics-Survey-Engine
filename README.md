# Analytics Survey Engine

## Core Features

- End-to-end tutoring session workflow (start session to results page)
- Pre-session, post-session, and tutee evaluation surveys
- Data-driven survey questions from seeded database records
- Automatic dimension score computation and normalization
- Session-level metric computation
- Rubric-based interpretation and recommended action generation
- Results page that summarizes computed outcomes

## Session Lifecycle and Survey/Analytics Flow

1. User opens home page (`/`)
2. User starts a tutoring session
3. User answers pre-session survey (`/pre_session`)
4. User ends the tutoring session
5. User answers post-session survey (`/post_session`)
6. User answers tutee evaluation survey (`/tutee_evaluation`)
7. System computes/updates derived scores and rubric mappings (dimension scores, metric scores, rubric interpretation)
8. User views final results (`/survey_results`)

## Project Structure

- `routes/web.php`  
  Route definitions for session workflow and survey/result pages.

- `app/Http/Controllers/`  
  Controllers for home/session controls, survey rendering/submission, and results display.

- `app/Services/SurveyService.php`  
  Core domain logic for loading survey data, scoring, metric computation, persistence, and rubric matching.

- `database/migrations/`  
  Database schema for surveys, dimensions, questions, sessions, responses, scores, metrics, and rubrics.

- `database/seeders/SurveySeeder.php`  
  Seeds surveys, dimensions, and questions.

- `database/seeders/MetricRubricSeeder.php`  
  Seeds metric definitions and rubric threshold ranges.

- `database/seeders/DatabaseSeeder.php`  
  Main seeder entry point (includes baseline bootstrap data).

## Requirements

- PHP 8.3+
- Laravel
- PostgreSQL
- composer

## Initial Run and Setup

##Dependencies
```bash
composer install
```

##Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

##Configure database
```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=analytics_engine
DB_USERNAME=postgres
DB_PASSWORD=123
```

## Migration

Run migrations:

```bash
php artisan migrate
```

Fresh reset:

```bash
php artisan migrate:fresh
```

## Seeding (Required)

- The system relies on seeded survey and rubric data.

```bash
php artisan db:seed
```

Full reset + seeded:

```bash
php artisan migrate:fresh --seed
```

## Run Application

```bash
php artisan serve
```

## Notes

- Survey pages depend on seeded questions and dimensions.
- Missing seed data can cause empty forms or incomplete analytics.
- Use `migrate:fresh --seed` whenever schema/seed changes are made during development.


## Data Overview

- Raw survey responses (per user/session/question)
- Response answer values
- Tutoring session records
- Dimension score records
- Metric result records
- Rubric result records

### Computed Data

- Dimension-level averages and normalized values
- Session-level metrics (including component metrics)
- Rubric classification per metric (level/status)
- Interpretation text and recommended action based on rubric range

