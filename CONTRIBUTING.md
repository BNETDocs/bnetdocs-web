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

## Code of Conduct
### Feature requests
All feature requests for the project must be vetted by one of the maintainers.
The maintainer will decide whether or not a request is valid and will also
either approve or deny a request. If approved, someone will integrate the
feature or enhancement into the project when possible and when time permits.

### Issues
If an issue or bug is found with the project, do not hesitate to let the
maintainers know. All bug reports are welcome and will be handled in a
respectful manner.

#### Submitting an issue
When submitting an issue, make sure to include the following:

* A screenshot of the issue.
* Steps to reproduce the issue.
* How the issue was encountered.
* What the intention was and what was expected to happen.
* Details about the environment:
  * The operating system and its version number (e.g. Windows 7 Pro 64-bit).
  * The web browser and its version number (e.g. Chrome 50).

Giving the information above will help immensely when troubleshooting the issue,
and could even lead directly to a code change in the best scenario.

## Project directory structure
The project is structured such that it can support tools, sample configurations,
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

## How to create a development environment
The subsections here are a general guide, not an exact step by step. Use your
own expertise and follow along. You may alter where you see fit for your own
preferences. If a different environment is preferred, go ahead, but these steps
will need to be adapted and that will be on you to do.

### VirtualBox
The environment requires Oracle VirtualBox or other virtualization software
that is capable of running **CentOS 7 x86\_64**.

* [VirtualBox](https://www.virtualbox.org/)

A compute server provider may be used instead if preferred, and typically will
have their own CentOS 7 image for use, which would allow the next step of
grabbing the CentOS 7 iso image and installing it manually to be skipped.

### OS
Using the virtualization software, create a virtual machine instance using the
latest CentOS 7 x86\_64 iso image available for download
[here](https://www.centos.org/download/), and install the minimal operating
system.

Details of the OS install are not important at this point in time, tune the OS
however is preferred.

### Post-Install
The following software will need to be installed to run this project:

* [Nginx](https://www.nginx.com/resources/wiki/start/topics/tutorials/install/)
* [Remi's PHP 7.2 repository](https://blog.remirepo.net/post/2017/12/04/Install-PHP-7.2-on-CentOS-RHEL-or-Fedora)
* [MariaDB](https://mariadb.com/kb/en/library/yum/)
* [Composer](https://getcomposer.org/)
* [Memcached](https://memcached.org/)

PHP should be capable of retrieving geoip information, generating gmp values,
encoding and decoding json, accessing mysql with PDO, and able to manage a
Memcached store. Aside from that, it also needs the pecl http library. The full
list of required extensions is in the [composer.json](./composer.json) file.

### Configuring
Configure nginx so that it is a fastcgi proxy to php-fpm. You will then run
`composer install` to get additional vendor files required for this project.

Nginx should be pointed at the [src/main.php](./src/main.php) file for all
requests.

MariaDB should be configured with the `TRADITIONAL,NO_AUTO_VALUE_ON_ZERO` modes.
A sample configuration can be found at
[etc/mysql-server.sample.cnf](./etc/mysql-server.sample.cnf). MariaDB data can
be seeded by importing the contents of
[etc/database.sample.sql](./etc/database.sample.sql).

Memcached does not require extra configuration from its package defaults, but
a sample configuration is available at
[etc/memcached.sample.conf](./etc/memcached.sample.conf). Memcached
settings typically are saved to `/etc/sysconfig/memcached` and increasing the
memory Memcached can use is typically recommended but not required for a
development environment.

The PHP date timezone should be in UTC, though the code will configure PHP for
this on its own.

The [etc/config.sample.json](./etc/config.sample.json) should be copied to
`etc/config.phoenix.json` and modified for the environment settings. It will
need at the very least the settings for MariaDB and Memcached.
