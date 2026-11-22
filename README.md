# OpenSentinel

OpenSentinel is a Laravel-based error monitoring and logging platform. Install it into your Laravel application to collect, monitor, and manage application errors and logs.

## Requirements

- PHP 8.2+
- Laravel 11+
- Composer
- A supported database (MySQL, PostgreSQL, or SQLite)

## Installation

### 1. new app

```bash
laravel new app
cd app
```

### 1. Install the package

```bash
composer require zimmer-yan/open-sentinel
```

### 2. Publish the configuration

```bash
php artisan vendor:publish --tag=opensentinel-config
```

### 3. Publish the styles / scripts

```bash
php artisan vendor:publish --tag=open-sentinel-assets
```

### 3. Generate app key

```bash
php artisan key:generate
```

### 3. Run database migrations

```bash
php artisan migrate
```

### 4. Start your application

```bash
php artisan serve
```

### 5. User subclass
```php
use OpenSentinel\Models\User as OsUser;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends OsUser
```

Your application is now ready to integrate with OpenSentinel.

## OpenSentinel Dashboard

To access the monitoring dashboard, visit your OpenSentinel installation:

```text
https://your-opensentinel-instance.com
```

Register an account, create an organization, and create a project to obtain your project connection credentials.

## Documentation

For configuration, SDK integration, and advanced usage, see the project documentation.

## License

OpenSentinel is open-source software. See the [LICENSE](LICENSE) file for details.
