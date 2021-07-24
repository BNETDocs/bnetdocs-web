# Contributing
## Getting Started
First off, thanks for having the willingness to contribute, and for taking the
time to read this document!

This project is the engine behind the [bnetdocs.org](https://bnetdocs.org)
website.

Releases are made by pushing commits to the **phoenix** branch. This fires off
a GitHub event to the BNETDocs [Jenkins server](https://jenkins.bnetdocs.org/),
which will build and deploy the code to production.

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
  * The operating system and its version number (e.g. Windows 10 Pro 64-bit).
  * The web browser and its version number, or its user agent (e.g. Firefox 90).

Giving the information above will help immensely when troubleshooting the issue,
and could even lead directly to a code change in the best scenario.

## Project directory structure
The project is structured such that it can support tools, sample configurations,
documentation, and of course the actual code, all in the same repository.

| Path  | Description                                                          |
|-------|----------------------------------------------------------------------|
| /bin/ | Binaries, scripts, and utilities for working with this project.      |
| /etc/ | Configuration files.                                                 |
| /lib/ | Vendor files from composer.                                          |
| /src/ | The project source code.                                             |
| /tmp/ | An intentionally empty directory for use with scripts.               |

Any file in the root of this repository is used by Git, GitHub, or otherwise
serves a very specific purpose such as licensing and the read me.

## How to create a development environment
The subsections here are a general guide, not an exact step by step. Use your
own expertise and follow along. You may alter where you see fit for your own
preferences. If a different environment is preferred, go ahead, but these steps
will need to be adapted and that will be on you to do.

### VirtualBox
The environment requires Oracle VirtualBox or other virtualization software
that is capable of running a RHEL-like system, such as
[**Fedora Server**](https://getfedora.org/server/).

* [VirtualBox](https://www.virtualbox.org/)

A compute server provider may be used instead if preferred, and typically will
have their own Linux image for use, which would allow the next step of getting
the Linux iso image and installing the OS to be skipped.

### OS
Using the virtualization software, create a virtual machine instance using the
latest Fedora iso image available for download
[here](https://getfedora.org/server/), and install either the minimal or server
operating system suite.

Details of the OS install are not important at this point in time, tune the OS
however is preferred.

### Post-Install
The following software will need to be installed to run this project:

* [Nginx](https://www.nginx.com/resources/wiki/start/topics/tutorials/install/)
* [PHP 7.4](https://blog.remirepo.net/post/2019/12/03/Install-PHP-7.4-on-CentOS-RHEL-or-Fedora)
* [MariaDB](https://mariadb.com/kb/en/library/yum/)
* [Composer](https://getcomposer.org/)

PHP should be capable of retrieving geoip information, generating gmp values,
encoding and decoding json, and access to mysql with PDO. It also requires the
pecl http library. The full list of required extensions is in the
[composer.json](./composer.json) file.

### Configuring
Configure nginx so that it is a fastcgi proxy to php-fpm. You will then run
`composer install` to get additional vendor files required for this project.

Nginx should be pointed at the [src/main.php](./src/main.php) file for all
requests. A sample config exists at
[etc/nginx-vhost-sample.conf](./etc/nginx-vhost-sample.conf).

MariaDB should be configured with the `TRADITIONAL,NO_AUTO_VALUE_ON_ZERO` modes.
A sample configuration can be found at
[etc/mysql-server.sample.cnf](./etc/mysql-server.sample.cnf). MariaDB data can
be seeded by importing the contents of
[etc/database.sample.sql](./etc/database.sample.sql).

The PHP date and MariaDB server timezones should be in UTC, though the code will
configure for this on its own.

The [etc/config.sample.json](./etc/config.sample.json) should be copied to
`etc/config.phoenix.json` and modified for the environment settings. It will
need at the very least the settings for MariaDB.
