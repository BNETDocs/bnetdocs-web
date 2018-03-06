# BNETDocs

[![Build Status](https://travis-ci.org/BNETDocs/bnetdocs-web.svg?branch=phoenix)](https://travis-ci.org/BNETDocs/bnetdocs-web)

BNETDocs is a documentation and discussion website for Blizzard Entertainment's
Battle.net&trade; and in-game protocols.

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
Please read the [CONTRIBUTING.md](/CONTRIBUTING.md) file.

## Copyright Disclaimer
This project is licensed under the AGPLv3. A copy of the GNU Affero General
Public License can be found [here](/LICENSE.md).

    BNETDocs, the documentation and discussion website for Blizzard protocols
    Copyright (C) 2003-2018  "Arta", Don Cullen "Kyro", Carl Bennett, others

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

BNETDocs is a documentation and discussion website for Blizzard Entertainment's
Battle.net&trade; and in-game protocols. You hereby acknowledge that BNETDocs
content is offered as is and without warranty. BNETDocs content may be
inaccurate as it is solely from third-party observations. BNETDocs is not
affiliated or partnered with Blizzard Entertainment in absolutely any way.
Battle.net&trade; is a registered trademark of Blizzard Entertainment.
