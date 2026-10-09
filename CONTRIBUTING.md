# Contributing
## Getting Started
First off, thanks for having the willingness to contribute, and for taking the
time to read this document!

This project is the engine behind the [bnetdocs.org](https://bnetdocs.org)
website.

Day-to-day work happens on the **develop** branch, and releases are made by
merging **develop** into the **phoenix** branch. Please open pull requests
against **develop**.

Every push and pull request runs the
[ci-cd](./.github/workflows/ci-cd.yml) GitHub Actions workflow, which lints
every PHP file and runs the PHPUnit tests. Pushes to **develop** and **phoenix**
are then deployed automatically:

| Branch  | Deployed to                                      |
|---------|--------------------------------------------------|
| develop | [dev.bnetdocs.org](https://dev.bnetdocs.org)     |
| phoenix | [bnetdocs.org](https://bnetdocs.org) (production) |

## Code of Conduct
### Feature requests
All feature requests for the project must be vetted by one of the maintainers.
The maintainer will decide whether or not a request is valid and will
subsequently either approve or deny a request. If approved, someone will
integrate the feature or enhancement into the project when possible and when
time permits.

### Issues
If an issue or bug is found with the project, do not hesitate to let the
maintainers know. All bug reports are welcome and will be handled in a
respectful manner.

#### Submitting an issue
When submitting an issue, make sure to include as much detail where possible,
such as the following:

* A screenshot of the issue.
* Steps to reproduce the issue.
* How the issue was encountered.
* What the intention was and what was or was not expected to happen.
* Details about the environment:
  * The operating system and its version number (e.g. Windows 11 Pro).
  * The web browser and its version number, or its user agent (e.g. Firefox 140).

Giving the information above will help immensely when troubleshooting the issue,
and could even lead directly to a code change in the best scenario.

## Project directory structure
The project is structured such that it can support tools, sample configurations,
documentation, and of course the actual code, all in the same repository.

| Path     | Description                                                       |
|----------|-------------------------------------------------------------------|
| /bin/    | Binaries, scripts, and utilities for working with this project.   |
| /etc/    | Configuration files, samples, and Docker build files.             |
| /lib/    | Vendor files from composer.                                       |
| /src/    | The project source code. This is the web server's document root.  |
| /tests/  | PHPUnit tests, mirroring the layout of `/src/`.                   |
| /tmp/    | An intentionally empty directory for use with scripts.            |

Any file in the root of this repository is used by Git, GitHub, Docker,
Composer, or otherwise serves a very specific purpose such as licensing and the
read me.

## How to create a development environment
### Requirements
* [PHP](https://www.php.net/) 8.4 or newer. Production and CI run PHP 8.5.
* [MariaDB](https://mariadb.org/) 10.7 or newer.
* [Nginx](https://nginx.org/), as a FastCGI proxy to php-fpm.
* [Composer](https://getcomposer.org/).
* The MaxMind [GeoLite2 City](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data)
  database, at `/usr/share/GeoIP/GeoLite2-City.mmdb` by default.

The full list of required PHP extensions is in the
[composer.json](./composer.json) file.

The [Docker](#docker) setup below provides PHP, Nginx, and MariaDB for you, so
it only needs Docker, Composer, and the GeoLite2 database on the host.

### Sample database
> [!WARNING]
> [etc/database.sample.sql](./etc/database.sample.sql) is out of date. It was
> last updated in 2021, and the current code needs tables and columns it does
> not have (for example `config`, `user_access`, and `documents.brief`), so
> most pages return an error with only the sample loaded. It also creates and
> switches to its own database named `bnetdocs_phoenix_dev_backup`, instead of
> importing into the database you choose. Ask a maintainer for a current
> schema until the sample is regenerated.

### Docker
The [docker-compose.yml](./docker-compose.yml) file runs Nginx, PHP-FPM (built
from [etc/docker/php/Dockerfile](./etc/docker/php/Dockerfile)), and MariaDB,
with this repository mounted into the containers.

1. Install the vendor files with `composer install`. The containers use the
   `lib/` directory from your checkout, so your host PHP needs the extensions
   listed in `composer.json`. On Ubuntu 26.04, CI gets them from the
   `php8.5-cli`, `php8.5-curl`, `php8.5-mbstring`, and `php8.5-xml` packages.
2. Copy [etc/config.sample.json](./etc/config.sample.json) to
   `etc/config.phoenix.json` and point the `mysql` section at the MariaDB
   container, using the credentials from `docker-compose.yml`:
   ```json
   "mysql": {
     "hostname": "mariadb",
     "database": "bnetdocs_docker",
     "username": "bnetdocs_docker",
     "password": "bnetdocs_docker"
   }
   ```
   Keep the other keys in that section from the sample.
3. Make sure the GeoLite2 City database exists at
   `/usr/share/GeoIP/GeoLite2-City.mmdb` on the host, since it is mounted into
   the containers from there.
4. Start everything with `docker compose up -d`. The site is served at
   <http://localhost:8080>.
5. Load a database schema into the `bnetdocs_docker` database (see the
   [warning](#sample-database) above), for example:
   ```bash
   docker compose exec -T mariadb mariadb -ubnetdocs_docker -pbnetdocs_docker bnetdocs_docker < schema.sql
   ```
   On the first start, wait until MariaDB has finished initializing, or the
   import fails with an access denied error.

The Docker Nginx config sets `display_errors = On`, so errors are returned as
JSON with a stack trace instead of the friendly error page.

Stop the containers with `docker compose down`. Add `-v` to also delete the
MariaDB data volume.

### Manual setup
Configure Nginx so that it is a FastCGI proxy to php-fpm, and point it at the
[src/main.php](./src/main.php) file for all requests. A sample config exists at
[etc/nginx-vhost-sample.conf](./etc/nginx-vhost-sample.conf). Then run
`composer install` to get the vendor files required for this project.

MariaDB should be configured with the `TRADITIONAL,NO_AUTO_VALUE_ON_ZERO` modes.
A sample configuration can be found at
[etc/mysql-server.sample.cnf](./etc/mysql-server.sample.cnf).

The PHP date and MariaDB server timezones should be in UTC, though the code will
configure for this on its own.

The [etc/config.sample.json](./etc/config.sample.json) should be copied to
`etc/config.phoenix.json` and modified for the environment settings. It will
need at the very least the settings for MariaDB.

## Error handling
Any PHP error that is reported, including warnings and notices, stops the
request with a 500 Internal Server Error. The exception is deprecations, which
are written to the error log and the request continues. Both Nginx configs set
`error_reporting = E_ALL`, so check the php-fpm error log for deprecations
before upgrading PHP.

## Testing
Run the same checks as CI before opening a pull request:

```bash
# Lint every PHP and template file
find src/ tests/ -name "*.php" -o -name "*.phtml" | xargs -I{} php -e -l -f {}

# Run the PHPUnit tests
lib/bin/phpunit
```

If the tests behave differently from CI, run `composer install` first, since an
old `lib/` directory can hold an older PHPUnit than the one `composer.lock`
pins.

## Version information
The deploy job writes the deployed tag, commit hash, and commit date to
`etc/deploy-version.txt`. In a Git checkout that file is ignored, both by Git
and by the code, and the same information is read from Git instead. It is shown
in the page footer, sets the end year of the copyright notice, and versions
asset URLs when `bnetdocs.asset_versioning` is enabled. The start
year is `VersionInfo::COPYRIGHT_START_YEAR` in
[src/Libraries/Core/VersionInfo.php](./src/Libraries/Core/VersionInfo.php).
