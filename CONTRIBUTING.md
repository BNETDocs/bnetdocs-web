# Contributing
## Getting Started
First off, thanks for having the willingness to contribute, and for taking the
time to read this document!

This project is the engine behind the [bnetdocs.org](https://bnetdocs.org)
website.

Releases are made by pushing commits to the **phoenix** branch. This fires off
a GitHub event to [@carlbennett](https://github.com/carlbennett)'s [Jenkins
server](https://jenkins.carlbennett.me/), which will build and deploy the code
to production.

## GitHub Code of Conduct
### Feature requests / enhancements
All feature requests for the website must be vetted by one of the maintainers.
The maintainer will decide whether or not your request is valid and will also
either approve or deny your request. If approved, someone will integrate your
feature/enhancement into the project when possible and when time permits.

### Issues
If you have found a bug or issue with the project/website, don't hesitate to
let us know. We accept all bug reports and will handle them in a respectful
manner.

#### Submitting an issue
When you are submitting an issue, make sure you include the following:

- A screenshot of the issue you're describing.
- Steps to reproduce the issue.
- How you encountered the issue.
- What your intention was and what you expected to happen.
- If you feel it necessary, or if asked for, details about your environment.
 - Your operating system and its version number (e.g. Windows 7 Pro 64-bit).
 - Your browser and its version number (e.g. Chrome 50).

Giving us the information above will help immensely when troubleshooting your
issue on our side, and could even lead directly to a bugfix in the best
scenario.

## Project directory structure
The project is structured such that we can add tools, sample configurations,
documentation, and of course the actual code, all in the same repository.

| Path  | Description                                                         |
|-------|---------------------------------------------------------------------|
| /bin/ | Miscellaneous scripts and utilities for working with this project.  |
| /etc/ | Sample configurations and other configuration-related files.        |
| /lib/ | Vendor files from composer.                                         |
| /src/ | The project itself.                                                 |
| /tmp/ | An intentionally empty directory for use with scripts.              |

Any file in the root of this repository is used by Git, GitHub, or otherwise
serves a very specific purpose such as licensing and the read me.

## How to create your development environment
The subsections here are more like a guide than an exact step by step. Use your
own expertise and follow along. You may alter where you see fit for your own
preferences.

### VirtualBox
You'll need to procure Oracle VirtualBox or other virtualization software
that is capable of running **CentOS 7 x86\_64**.

- [VirtualBox](https://www.virtualbox.org/)

### OS
Using your virtualization software, create an instance using the latest CentOS 7
x86\_64 iso image, and install the minimal operating system.

Other details are not important, tune it however you like.

### Post-Install
You'll need to install additional software to run this project.

- [Nginx](https://www.nginx.com/resources/wiki/start/topics/tutorials/install/)
- [Remi's PHP 7.2 repository](https://blog.remirepo.net/post/2017/12/04/Install-PHP-7.2-on-CentOS-RHEL-or-Fedora)
- [MariaDB](https://mariadb.com/kb/en/library/yum/)
- [Composer](https://getcomposer.org/)
- Memcached

PHP should be capable of retrieving geoip information, generating gmp values,
encoding and decoding json, accessing mysql with PDO, and able to manage a
Memcached store. Aside from that, it also needs the pecl http library. The full
list of required extensions is in the `/composer.json` file in the root of this
repository.

### Configuring
Configure these such that nginx is a fastcgi proxy to php-fpm. You'll then run
`composer install` to get additional vendor files required for this project.

You should point nginx/php-fpm at the `/main.php` file for all requests.

MariaDB can be seeded by importing the contents of: `/etc/database.sample.sql`

Memcached memory could be increased in: `/etc/sysconfig/memcached`

The PHP date timezone should be in UTC, though the code will configure PHP for
this on its own.

The `etc/config.sample.json` should be copied to `etc/config.phoenix.json` and
modified for your environment settings. It will need at the very least the
settings for MariaDB and Memcached.
