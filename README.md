# Laravel 12 Starter

<p align="center">
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/badge/laravel-12.x-brightgreen.svg" alt="Laravel Version"></a>
  <a href="https://packagist.org/packages/php"><img src="https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg" alt="PHP Version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"></a>
</p>

A robust and feature-rich Laravel 12 boilerplate for rapid project setup. Pre-configured with essential tools and modules to accelerate your development workflow so you can focus on building your application's core functionality right away.

---

## What's Included

### Authentication & Security
- **Email/password login** via Laravel UI (registration disabled by default)
- **Google OAuth** via Laravel Socialite — social sign-in out of the box
- **User impersonation** — admins can log in as another user and switch back
- **Sanctum** for API token authentication

### Roles & Permissions
Powered by `spatie/laravel-permission` with granular, module-scoped permissions.

Pre-seeded roles and permissions:

| Module | Permissions |
|---|---|
| Users | access, create, edit, change password, delete, impersonate |
| Roles | access, create, edit, delete |
| Companies | access, create, edit, delete |
| Positions | access, create, edit, delete |
| Org Structures | access, create, edit, delete |
| AI | access |
| System | settings, logs, trash bin |

### User Management
- Full CRUD for users with soft deletes and a **Trash Bin** for restore/force-delete
- User profile page
- Password change (with permission guard)
- Livewire-powered user list, settings, and activity views

### Organizational Structure
- **Companies** — manage company records
- **Positions** — define job positions
- **Org Structures** — hierarchical org chart using `orgchart.js`

### Real-time Features
- **Laravel Reverb** (WebSockets) for real-time broadcasting
- **Laravel Echo** client integration
- **Online users** indicator (Livewire component)
- **Notifications** — database notifications with real-time updates and a dedicated notifications page

### Activity Logs
- Powered by `spatie/laravel-activitylog`
- Track model changes across the application
- Livewire activity log viewer per user

### AI Integration
- **Laravel AI SDK** (`laravel/ai`) wired up and ready
- AI testing page (permission-gated)
- Agent conversations and messages tables pre-migrated

### System & Admin Tools
- **System Settings** — key-value settings stored in the database
- **System Logs** — log viewer via `rap2hpoutre/laravel-log-viewer`
- **Laravel Pulse** dashboard for application monitoring (performance, queues, exceptions)
- **AdminLTE 3** admin panel layout (`jeroennoten/laravel-adminlte`)
- **Debugbar** in development (`barryvdh/laravel-debugbar`)
- **Ignition** error pages in development

### Internationalization
- Language switcher with support for **English**, **Japanese**, and **Simplified Chinese**
- `SetLocale` middleware stores the active locale in the session

### Frontend
- **Tailwind CSS v4** with Vite
- **Bootstrap 5** and **Sass** also bundled
- **Alpine.js** for lightweight client-side interactions
- **Dark mode toggle** (Livewire component)
- **PWA support** via `silviolleite/laravelpwa` — installable on desktop and mobile

### Image Handling
- **Image optimization middleware** (`spatie/laravel-image-optimizer`) — images are automatically optimized on upload
- `spatie/laravel-html` for fluent HTML generation

### Developer Experience
- **Pest v3** for testing with a test suite ready to extend
- **Laravel Pint** for opinionated code style enforcement
- **Laravel Sail** for Docker-based local development
- Soft deletes with a global **Trash Bin** page
- `DeleteModel` Livewire component for reusable delete confirmations

---

## Default Credentials

After seeding, the following test accounts are available (all use password `p4ssw0rd`):

| Name | Email | Role |
|---|---|---|
| Administrator | admin@admin | superadmin |
| John Doe | john@test | superadmin |
| User 1 | test1@test | superadmin |
| User 2 | test2@test | superadmin |
| User 3 | test3@test | superadmin |

> Change these credentials immediately in any non-local environment.

---

## Prerequisites

- PHP >= 8.4
- Composer
- Node.js & npm
- A database (MySQL, PostgreSQL, SQLite, etc.)

---

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/bevi-webdev-jm/laravel-12-starter.git <app-name>
cd <app-name>
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Run the setup command

```bash
php artisan starter:setup
```

This copies `.env.example` → `.env`, prompts you to configure the app name, URL, and database credentials, generates an application key, and creates the storage symlink.

> For additional credentials (Google OAuth, Reverb, AI providers, mail, etc.), open `.env` and fill in the relevant values manually.

### 4. Run migrations and seed the database

```bash
php artisan migrate:fresh --seed
```

### 5. Build frontend assets

```bash
npm run build
```

Or for hot-reload during development:

```bash
npm run dev
```

### 6. Start the development server

```bash
php artisan serve
```

For the full stack (server + queue + Vite + Reverb) in one command:

```bash
composer run dev
```

### 7. Access the application

```
http://127.0.0.1:8000
```

Log in with `admin@admin` / `p4ssw0rd`.

---

## Optional Services

### Laravel Pulse (monitoring dashboard)

```
http://127.0.0.1:8000/pulse
```

Requires the queue worker to be running: `php artisan queue:work`

### Real-time broadcasting (Reverb)

```bash
php artisan reverb:start
```

### PWA

The app is PWA-ready. Visit the site over HTTPS and use the browser's "Install" prompt to add it to your home screen.

---

## Code Quality

Run the test suite:

```bash
php artisan test --compact
```

Fix code style:

```bash
vendor/bin/pint
```

---

## License

This project is open-source and licensed under the [MIT License](LICENSE).
