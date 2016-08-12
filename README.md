# BNETDocs

[![Build Status](https://travis-ci.org/BNETDocs/bnetdocs-web.svg?branch=phoenix)](https://travis-ci.org/BNETDocs/bnetdocs-web)

BNETDocs is a web content management system (CMS) and documentation software
for the Battle.net&trade; online gaming service's protocol. It provides a means
for documenting the protocol and encouraging discussion on it.

BNETDocs is in no way affiliated with or endorsed by Blizzard
Entertainment&trade; or its brands, and is not intended to compete with
or undermine Blizzard Entertainment&trade; or Battle.net&trade;. Persons using
this software understand that the information provided within is collected
knowledge from years of observations and may be inaccurate. BNETDocs does not
provide a warranty of any kind to the nature of its content. All aforementioned
trademarks are the property of their respective owners. See the LICENSE file at
the root of this repository for more info.

**BNETDocs: Phoenix** is the successor to
[BNETDocs: Redux](https://github.com/BNETDocs/bnetdocs-web/tree/redux).

## Installation

### Clone this repository
```sh
git clone git@github.com:BNETDocs/bnetdocs-web.git ~/bnetdocs-web
```

### Install nginx
Follow the guide available over at
[carlbennett/nginx-conf](https://github.com/carlbennett/nginx-conf).

After successfully installing **carlbennett/nginx-conf**, run:

```sh
sudo cp ./etc/nginx-vhost-sample.conf \
  /etc/nginx/sites-available/local.bnetdocs.org.conf
sudo ln -s \
  /etc/nginx/sites-available/local.bnetdocs.org.conf \
  /etc/nginx/sites-enabled/
```

After running the above, modify the new file to your liking. It's recommended
to update the `server_name` directives to `local.bnetdocs.org`. Please look
over the file and perform any updates to it as you see fit.

### Install php-fpm
#### CentOS 7.x / Fedora 24
```sh
sudo yum install php-fpm
```

\* Use `dnf` instead of `yum` if you have it available.

#### Debian / Ubuntu
```sh
sudo apt-get update && sudo apt-get install php-fpm
```

### Satisfy composer
Run `composer install` at the root of the repository.

### Run nginx and php-fpm
#### CentOS 7.x / Fedora 24
```sh
sudo systemctl start nginx php-fpm
```

#### Debian / Ubuntu
```sh
sudo /etc/init.d/nginx start && sudo /etc/init.d/php-fpm start
```

### Import sample database
```sh
mysql < ./etc/database.sample.sql
```

### Configure BNETDocs
```sh
cp ./etc/config.sample.json ./etc/config.phoenix.json
```

\* Open `config.phoenix.json` in your favorite text editor and modify it to
   your liking.

### Test
Try accessing this endpoint: [local.bnetdocs.org](https://local.bnetdocs.org)

\* You may need to modify your `/etc/hosts` file if your development
   environment is not your `localhost`.

## Contributing
Please read the [CONTRIBUTING.md]
(https://github.com/BNETDocs/bnetdocs-web/blob/phoenix/CONTRIBUTING.md) file.
