#!/bin/bash

printf "This script will transmit sensitive material to and from this machine, and will create and drop temporary databases.\n" 1>&2
read -p "Is this ok [y/N]: " PROMPT
if [ "${PROMPT}" != "Y" ] && [ "${PROMPT}" != "y" ]; then
  printf "Operation aborted.\n" 1>&2
  exit 1
fi

SRCDIR="$(git rev-parse --show-toplevel)"

MYSQLHOST="$1"
if [ -z "${MYSQLHOST}" ]; then
  read -p "Enter the MySQL server hostname: " MYSQLHOST
fi
if [ -z "${MYSQLHOST}" ]; then
  printf "MySQL hostname not provided, assuming localhost...\n" 1>&2
  MYSQLHOST="localhost"
fi

MYSQLUSER="$2"
if [ -z "${MYSQLUSER}" ]; then
  read -p "Enter the MySQL user: " MYSQLUSER
fi
if [ -z "${MYSQLUSER}" ]; then
  printf "MySQL user not provided, assuming root...\n" 1>&2
  MYSQLUSER="root"
fi

MYSQLPASS="$3"
if [ -z "${MYSQLPASS}" ]; then
  read -s -p "Enter the MySQL password for ${MYSQLUSER}: " MYSQLPASS
  echo
fi
if [ -z "${MYSQLPASS}" ]; then
  printf "MySQL password not provided, assuming it's not set...\n" 1>&2
  MYSQLPASS=''
fi

MYSQLSCHEMA="$4"
if [ -z "${MYSQLSCHEMA}" ]; then
  read -p "Enter the MySQL database schema: " MYSQLSCHEMA
  echo
fi
if [ -z "${MYSQLSCHEMA}" ]; then
  printf "MySQL database schema not provided, assuming bnetdocs_phoenix_dev...\n" 1>&2
  MYSQLSCHEMA='bnetdocs_phoenix_dev'
fi

set -e

printf "[1/7] Dumping the database...\n"
mysqldump --host="${MYSQLHOST}" --user="${MYSQLUSER}" --password="${MYSQLPASS}" \
  --opt --order-by-primary \
  --complete-insert --single-transaction --triggers --routines \
  --hex-blob --add-drop-database --result-file /tmp/.database.sample.sql \
  --databases "${MYSQLSCHEMA}"

printf "[2/7] Performing local pattern replacements...\n"
sed -i 's#Current Database: `'"${MYSQLSCHEMA}"'`#Current Database: `'"${MYSQLSCHEMA}_backup"'`#' /tmp/.database.sample.sql
sed -i 's#DROP DATABASE IF EXISTS `'"${MYSQLSCHEMA}"'`#DROP DATABASE IF EXISTS `'"${MYSQLSCHEMA}_backup"'`#' /tmp/.database.sample.sql
sed -i 's#CREATE DATABASE /\*!32312 IF NOT EXISTS\*/ `'"${MYSQLSCHEMA}"'`#CREATE DATABASE /*!32312 IF NOT EXISTS*/ `'"${MYSQLSCHEMA}_backup"'`#' /tmp/.database.sample.sql
sed -i 's#USE `'"${MYSQLSCHEMA}"'`#USE `'"${MYSQLSCHEMA}_backup"'`#' /tmp/.database.sample.sql

printf "[3/7] Uploading modified database so queries can be performed...\n"
mysql --host="${MYSQLHOST}" --user="${MYSQLUSER}" --password="${MYSQLPASS}" --database='' < /tmp/.database.sample.sql

printf "[4/7] Redacting private user information...\n"
mysql --host="${MYSQLHOST}" --user="${MYSQLUSER}" --password="${MYSQLPASS}" --database="${MYSQLSCHEMA}_backup" << EOF
  START TRANSACTION;
  USE ${MYSQLSCHEMA}_backup;
  TRUNCATE TABLE event_log;
  INSERT INTO event_log (id, event_type_id, event_datetime, user_id, ip_address, meta_data)
    VALUES (0,0,NOW(),NULL,NULL,'Initial event log');
  TRUNCATE TABLE packet_used_by;
  TRUNCATE TABLE packets;
  TRUNCATE TABLE servers;
  TRUNCATE TABLE user_profiles;
  TRUNCATE TABLE user_sessions;
  TRUNCATE TABLE users;
  INSERT INTO users
    (id, email, username, display_name, created_datetime, verified_datetime,
      verifier_token, password_hash, password_salt, options_bitmask, timezone)
    VALUES (NULL, 'nobody@example.com', 'nobody', NULL, NOW(), NULL, NULL, NULL, NULL, 0, NULL);
  COMMIT;
EOF

printf "[5/7] Dumping the redacted database...\n"
mysqldump --host="${MYSQLHOST}" --user="${MYSQLUSER}" --password="${MYSQLPASS}" \
  --opt --order-by-primary \
  --complete-insert --single-transaction --triggers --routines \
  --skip-extended-insert --hex-blob --add-drop-database \
  --result-file /tmp/.database.sample.sql \
  --databases "${MYSQLSCHEMA}_backup"

printf "[6/7] Deleting the redacted database from the server...\n"
mysql --host="${MYSQLHOST}" --user="${MYSQLUSER}" --password="${MYSQLPASS}" --database="${MYSQLSCHEMA}_backup" << EOF
  DROP DATABASE IF EXISTS ${MYSQLSCHEMA}_backup;
EOF

printf "[7/7] Copying database into current working directory...\n"
pushd "$(git rev-parse --git-dir)"
cp /tmp/.database.sample.sql ${SRCDIR}/etc/database.sample.sql
popd
rm /tmp/.database.sample.sql

printf "Operation complete!\n"
