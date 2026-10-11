# Database files

`SEN381_database_initial_version.sql` is the original schema design, exported from PostgreSQL 18 with `pg_dump`. It is kept as a design reference.

The live schema is now defined by the Laravel migrations in `database/migrations/`, which were converted from this file. Build a database with `php artisan migrate`, not by loading the dump.

Differences between the dump and the migrations:

| Dump | Migrations |
|---|---|
| Table `service_request` | `service_requests` |
| Table `request_status_transition` | `request_status_transitions` |
| `users` has only name, email, password, role and timestamps | `users` also has Laravel's `email_verified_at` and `remember_token` |
| No Laravel tables | Adds `sessions`, `cache`, `jobs` and `failed_jobs` |

If you do need to load the dump, use `psql -f`. pgAdmin's Query Tool cannot run it, because the file contains `psql`-only commands (`\restrict` and `COPY ... FROM stdin`).
