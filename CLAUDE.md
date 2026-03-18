# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

BNETDocs: Phoenix is a PHP 8.3+ web application for documenting Blizzard Entertainment's Battle.net and in-game protocols. Production site: https://bnetdocs.org

## Commands

### Dependencies
```bash
composer install                    # Install all dependencies
composer update -o --no-dev         # Production update with optimization
```

### Linting
```bash
# Lint a single PHP file
php -e -l -f src/path/to/file.php

# Lint all PHP/PHTML files (mirrors CI)
find src/ -name "*.php" -o -name "*.phtml" | xargs -I{} php -e -l -f {}
```

### Testing
```bash
lib/bin/phpunit                     # Run all tests
```

### Docker (local development)
```bash
docker compose up -d                # Start nginx, PHP-FPM, MariaDB
docker compose down                 # Stop containers
```

## Architecture

### Request Flow

```
HTTP Request → Nginx (FastCGI) → PHP-FPM → src/main.php
  → Router::invoke()
  → Pattern-matched Controller::invoke($args)    # sets $model data
  → Content-negotiated View::invoke($model)      # renders output
  → Response with headers and status code
```

### MVC Pattern

**Entry point:** `src/main.php` — initializes autoloader, session/auth, logs Blizzard/Slack bot traffic, then delegates to `Libraries/Core/Router`.

**Router** (`Libraries/Core/Router.php`): Regex-based route matching with content negotiation (RFC 7231). Each route maps a URI pattern to a Controller class and an array of View classes (HTML, JSON, Plain, RSS, language-specific). The router selects the View based on the HTTP `Accept` header.

**Controllers** (`src/Controllers/`): Implement `Interfaces\Controller`. The `invoke(?array $args): bool` method processes the request, populates `$model`, sets HTTP status/headers, and returns whether the view should render.

**Models** (`src/Models/`): Implement `Interfaces\Model` and `JsonSerializable`. Extend `Models\Base` (has `_responseCode`/`_responseHeaders`). Key base classes:
- `Models\ActiveUser` — adds current user authentication context
- `Models\Core\AccessControl` — extends ActiveUser with ACL checks

**Views** (`src/Views/`): Implement `Interfaces\View` with static `invoke(Model $model): void` and `mimeType(): string`. Base classes: `Html`, `Json`, `Plain`, `RSS`, and language-specific code renderers (`Cpp`, `Go`, `Java`, `Php`, `Vb`).

**Templates** (`src/Templates/`): `.phtml` files used by HTML views. Template path mirrors the view class name (e.g., `Document/View.phtml` for `Document\ViewHtml`).

### Key Libraries (`src/Libraries/`)

- `Core/Config` — Loads `etc/config.phoenix.json` with dot-notation key access; falls back to the `config` DB table.
- `Core/Router` — Route definitions live here; modifying routes means updating the `$routes` array.
- `Db/MariaDb` — PDO singleton: `MariaDb::instance()`. Requires `TRADITIONAL,NO_AUTO_VALUE_ON_ZERO` SQL mode.
- `User/Authentication` — Called early in `main.php`; sets `$_SESSION['bnetdocs']['authenticated_user_id']`.

### Configuration

Copy `etc/config.sample.json` to `etc/config.phoenix.json` and customize. Key sections:
- `mysql` — database credentials
- `bnetdocs` — maintenance mode, GeoIP country bans, password policy
- `geoip` — path to MaxMind GeoLite2-City `.mmdb` file
- `discord` / `email` / `recaptcha` / `slack` — integrations

Sample configs: `etc/nginx-vhost-sample.conf`, `etc/mysql-server.sample.cnf`

### Namespacing & Autoloading

Namespace root: `BNETDocs\`. PSR-4 autoloading configured in `composer.json` mapping to `src/`.

### Database

MariaDB 10.7+ with UTF8MB4. Initial schema at `etc/database.sample.sql`. Key tables: `documents`, `packets`, `comments`, `users`, `news_posts`, `event_log`, `change_log`, `data_structures`, `servers`, `products`.

## CI/CD

- **php-linter.yml**: Runs on every push/PR — validates `composer.json` and lints all PHP files.
- **deployment.yml**: Auto-deploys on push to `develop` or `phoenix` branches via rsync. Deploy target defined via GitHub secrets (`SSH_HOST`, `SSH_USER`, etc.). Deployment injects `WEB_CONFIG_JSON` secret as the live config file.
