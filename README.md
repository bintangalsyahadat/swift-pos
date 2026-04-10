# Swift POS

A Point of Sale (POS) admin panel built with **Laravel 12** and **Filament v5**.

## Tech Stack

- **PHP** ^8.3
- **Laravel** ^12.0
- **Filament** ^5.5
- **Tailwind CSS** ^4.x + **Vite** ^7.x — Frontend tooling

## Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite (default) or any other supported database

## Setup

### 1. Clone the repository

```bash
git clone <repo-url> swift-pos
cd swift-pos
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Environment configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` as needed. By default, the project uses SQLite:

```env
DB_CONNECTION=sqlite
```

### 4. Create the SQLite database file (if not exists)

```bash
touch database/database.sqlite
```

### 5. Run migrations & seeders

```bash
php artisan migrate --seed
```

This will create a default user:

- **Email:** `test@example.com`
- **Password:** `password`

> You can change the credentials in `database/seeders/DatabaseSeeder.php`.

### 6. Create a storage symlink

```bash
php artisan storage:link
```

This is required for avatar uploads to work correctly.

### 7. Build frontend assets

```bash
npm run build
```

### 8. Run the development server

```bash
composer run dev
```

This starts the Laravel dev server, queue worker, log watcher (Pail), and Vite concurrently.

Or individually:

```bash
php artisan serve
npm run dev
```

## Accessing the Admin Panel

Open your browser and navigate to:

```
http://localhost:8000/dashboard
```

Log in with the seeded credentials:

- **Email:** `test@example.com`
- **Password:** `password`

## Filament Plugins

| Plugin                                                                           | Description                                                                       |
| -------------------------------------------------------------------------------- | --------------------------------------------------------------------------------- |
| [filament-edit-profile](https://github.com/joaopaulolndev/filament-edit-profile) | Edit profile page with avatar, email, browser sessions & Sanctum token management |
| [filament-ui-switcher](https://github.com/andreia/filament-ui-switcher)          | Light / dark mode toggle inside the admin panel                                   |

## Running Tests

```bash
php artisan test
```

or via composer:

```bash
composer run test
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
