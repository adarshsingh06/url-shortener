#  URL Shortener

A multi-company URL shortener built with Laravel 12. The UI follows the supplied dashboard flow with a minimal-brutalist visual style: high-contrast typography, square corners, exposed borders, restrained color, and responsive tables/forms.

## Implemented requirements

- Multi-company tenancy: each company owns its users, invitations, and short URLs.
- Roles stored on users: `SuperAdmin`, `Admin`, `Member`, `Sales`, and `Manager`.
- A SuperAdmin is created by a database seeder using a raw SQL `INSERT` statement.
- Session-based sign in and sign out.
- SuperAdmin can create a company and invite its first Admin.
- An Admin can invite an Admin or Member into their own company.
- Invitations have hashed tokens, seven-day expiration, one-time acceptance, and a password setup form.
- Admin and Member can create short URLs; SuperAdmin cannot.
- SuperAdmin sees URLs across all companies; Admin sees URLs for their own company; Member sees only URLs they created.
- Public short URLs redirect to the original HTTP/HTTPS URL and increment the click counter.
- Dashboard date filters: this month, last month, last week, and today.
- CSV download respects the authenticated user's visibility scope and selected date filter.
- Feature tests cover role access, scoping, redirects, invitations, and creation permissions.

### Roles not fully described in the brief

The brief names `Sales` and `Manager` in its authentication section, but does not define their specific permissions or invitation flows. Those role values are supported in the model. Until additional business rules are supplied, they can only view their own short URLs and cannot create URLs or send invitations (least-privilege behavior). The invitation UI follows the explicit rules in the requirements and offers only Admin and Member roles.

## Prerequisites

- PHP **8.2 or later** (PHP 8.4 is also compatible with the configured Laravel version).
- Composer **2.7 or later**.
- SQLite with the `pdo_sqlite` PHP extension, or MySQL **8.0+** with `pdo_mysql` enabled.
- PHP extensions commonly required by Laravel: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `PDO`, `session`, `tokenizer`, and `xml`.
- A current browser.
- Node.js/npm are **not required** for this implementation because the frontend uses plain Blade, HTML, CSS, and a small inline clipboard helper; no asset build is needed.

## Dependencies and versions

The project pins compatible version ranges in `composer.json`. Run `composer show --direct` after installation to inspect the precise patch versions resolved by Composer and recorded in `composer.lock`.

| Dependency | Version constraint | Purpose |
|---|---:|---|
| PHP | `^8.2` | Runtime |
| `laravel/framework` | `^12.0` | Routing, sessions, Eloquent, validation, Blade, mail |
| `fakerphp/faker` | `^1.23` | Test data tooling |
| `mockery/mockery` | `^1.6` | Test doubles |
| `nunomaduro/collision` | `^8.6` | Console test output |
| `phpunit/phpunit` | `^11.5` | Automated tests |

There are no JavaScript package dependencies.

> **Version note:** Composer resolves the latest patch release satisfying each constraint when `composer install` runs without a lock file. The exact resolved patch versions are fixed in the generated `composer.lock`; that file should be committed after the first successful install. This source package intentionally does not fabricate a lock file.

## Build and run locally

### 1. Install PHP dependencies

From the project root:

```bash
composer install
```

### 2. Configure the environment

Copy the example file and generate a unique application key.

**macOS/Linux/Git Bash:**

```bash
cp .env.example .env
php artisan key:generate
```

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Keep `.env` local. Do not commit mail credentials, passwords, app keys, or other secrets. The frontend does not contain any API keys or credentials.

### 3. Prepare the database

SQLite is the default and needs no server. Ensure `database/database.sqlite` exists; an empty file is included in the project.

```bash
php artisan migrate
php artisan db:seed
```

To use MySQL instead, create an empty database and update the following values in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=url_shortener
DB_USERNAME=your_local_database_user
DB_PASSWORD=your_local_database_password
```

Then run `php artisan migrate --seed` again. Put real credentials only in `.env`, never in source code.

### 4. Set the initial SuperAdmin credentials

For local development, the seeder reads the following optional values from `.env` before inserting the SuperAdmin through raw SQL:

```dotenv
SUPERADMIN_EMAIL=superadmin@example.com
SUPERADMIN_PASSWORD=ChangeMe!12345
```

Change these values before running the seeder in any shared environment. The default credentials are for local evaluation only. If the seed has already created the SuperAdmin, change the password through your database administration tool or add a password-reset workflow before deployment; the seeder deliberately does not overwrite an existing account.

### 5. Start the development server

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000). Sign in using the configured SuperAdmin credentials. Create a company invitation, open the generated invitation link, and finish account setup. The default `MAIL_MAILER=log` writes invitation messages to the Laravel log; the generated acceptance link is also displayed to the inviter for easy local testing.

To send real emails, configure a working SMTP mailer in `.env` on the server. Do not expose SMTP credentials to the browser.

### 6. Run the tests

```bash
php artisan test
```

The test environment uses an in-memory SQLite database. The tests should not require or alter the local development database.

## Main routes

| Method | Route | Purpose | Access |
|---|---|---|---|
| `GET` | `/login` | Sign-in screen | Guest |
| `POST` | `/login` | Create authenticated session | Guest |
| `POST` | `/logout` | End session | Authenticated |
| `GET` | `/dashboard` | Role-scoped dashboard | Authenticated |
| `POST` | `/short-urls` | Create a short URL | Admin, Member |
| `GET` | `/short-urls/download` | Export visible URLs as CSV | Authenticated |
| `POST` | `/invitations/company` | Create a company and invite its Admin | SuperAdmin |
| `POST` | `/invitations/team` | Invite an Admin or Member into own company | Admin |
| `GET/POST` | `/accept-invitation/{token}` | Accept an invitation | Guest with valid token |
| `GET` | `/{code}` | Public redirect (7 lowercase letters/digits) | Public |

The short-code route is constrained to seven lowercase alphanumeric characters and declared after the named routes to avoid collisions with application endpoints.

## Data model

- `companies`: one record per client workspace.
- `users`: belongs to a company, except the global SuperAdmin; role is stored as a string so all roles in the brief are representable.
- `invitations`: stores the SHA-256 hash of the random token, target role, inviter, company, expiry and acceptance timestamp. The plain token is only placed in the invitation URL and is not stored in the database.
- `short_urls`: belongs to both the creating user and their company; `code` is unique, and `clicks` is incremented atomically on each public redirect.

## Security and implementation notes

- Authorization is enforced in controllers and scoped queries, not only by hiding buttons in the UI.
- Short URL creation validates the URL scheme as HTTP or HTTPS.
- Login is rate-limited; invitation acceptance requires a 12-character password, an unexpired one-time token, and a unique email.
- CSV fields beginning with formula characters are escaped to reduce spreadsheet formula-injection risk.
- All user-generated values are rendered through Blade's escaped `{{ }}` syntax.
- Keep `APP_DEBUG=false` and serve over HTTPS in production. Add a proper mail transport, rate-limit monitoring, and a password reset process before production use.

## AI assistance disclosure

ChatGPT was used to assist with project scaffolding, code drafting, README preparation, and test-case planning. Review the implementation, run the tests locally, and make sure you can explain the authorization and tenancy decisions before submitting it. This disclosure follows the assignment's stated AI usage policy; the project should be submitted only after your own review and any changes you make.
