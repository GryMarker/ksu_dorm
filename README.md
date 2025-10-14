# KSU Dorm Management System (MVP)

Laravel 11 application that manages admission, interview scheduling, room assignments, transfers, reservations, and attendance logging for Kalinga State University dormitories. The project ships with Laravel Breeze authentication (email verification enabled), role-based authorization via policies, queue-ready notification mailables, and seed data for quick demos.

## Tech Stack
- Laravel 11 (PHP 8.2+)
- MySQL (production target) / SQLite (quick dev testing)
- Blade + Tailwind UI scaffolding from Breeze
- Laravel Queues (sync driver for MVP)

## Getting Started
1. **Install dependencies**
   ```bash
   composer install
   npm install
   npm run build
   ```
2. **Environment**
   - Duplicate `.env.example` into `.env` if needed.
   - Update the `APP_KEY`, database (`DB_*`), and mail settings for your environment.
   - Queue driver defaults to `sync`; update `QUEUE_CONNECTION` when you add a worker.
3. **Application key**
   ```bash
   php artisan key:generate
   ```
4. **Database**
   - Create a MySQL database (e.g., `ksu_dorm`).
   - Update `.env` with the credentials.
   - Run migrations and seeders (fresh start recommended):
     ```bash
     php artisan migrate:fresh --seed
     ```
     The `DevSeeder` loads demo users, interview slots, room inventory, reservations, assignments, and attendance logs.
5. **Serve the app**
   ```bash
   php artisan serve
   ```

## Default Accounts
| Role                          | Email                       | Password |
| ----------------------------- | --------------------------- | -------- |
| Dorm Master                   | dormmaster@ksu.test         | password |
| Student Director              | director@ksu.test           | password |
| Draft Applicant (Tenant)      | applicant@ksu.test          | password |
| Approved Tenant Samples       | tenant1@ksu.test ... tenant5@ksu.test | password |
| Interview-Pending Tenants     | tenant6@ksu.test ... tenant9@ksu.test | password |

> New registrations default to the `tenant` role and must verify email. Tenants are blocked from the tenant dashboard until they complete the onboarding funnel described below.

## Tenant Onboarding Flow
1. **Submit the application form** - all profile fields are required together with the policies/terms acknowledgement.
2. **Self-book an interview slot** - students see live capacity for each slot and can rebook until a decision is rendered.
3. **Attend interview / await decision** - dorm staff record the result (approved / rejected / recheck). Result notifications are emailed and logged.
4. **Post-approval access** - only approved tenants can reach the tenant dashboard, room availability, reservations, transfers, and attendance features. Pending/rejected/recheck tenants are always redirected back to `/apply` with guidance.

## Core Features
- Admission workflow with draft -> interview -> approval statuses enforced by middleware.
- Interview slot management with tenant self-booking and admin result logging (email notifications queued/logged).
- Room inventory with automatic six-bed generation (A-F) and capacity enforcement.
- Reservation & transfer requests, admin approvals that maintain bed occupancy/assignment history.
- Attendance logging (web + `/api/attendance/scan` endpoint) with duplicate-entry guard.
- Role-scoped navigation:
  - **Dorm Master**: full CRUD, approvals, attendance reports, administrative dashboard.
  - **Student Director**: read-only dashboards and listings.
  - **Tenant**: onboarding funnel, availability lookup, reservation/transfer requests, attendance history, "My Room" view.
- Notification logging via `notification_logs` for every email that is queued.

## API
- `POST /api/attendance/scan`
  - Payload: `tenant_id`, `type` (`in`/`out`), optional `timestamp`, `mode`, `device_id`, `ip`, `remarks`.
  - Blocks consecutive identical scans unless you extend the logic for overrides.

## Development Notes
- Mail uses the `log` driver by default; view emails in `storage/logs/laravel.log`.
- Queues default to `sync`. Swap to `database` or `redis` when you introduce workers.
- Breeze's frontend build is already compiled; re-run `npm run dev` for live reloads.
- For quick local checks without MySQL you can point `DB_CONNECTION=sqlite` and use the bundled `database/database.sqlite` file (run migrations accordingly).

## Testing Ideas (not yet implemented)
- Feature tests for the onboarding funnel and reservation approvals.
- API test coverage for the attendance scan endpoint.
- Policy unit tests for each actor role.

Happy hacking!


