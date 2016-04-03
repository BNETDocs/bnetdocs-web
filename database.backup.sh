#!/bin/bash

printf "This script uses the MySQL root user and will create/drop temporary databases.\n" 1>&2
read -p "Is this ok [y/N]: " PROMPT
if [ "${PROMPT}" != "Y" ] && [ "${PROMPT}" != "y" ]; then
  printf "Operation aborted.\n" 1>&2
  exit 1
fi

MYSQLHOST="$1"
if [ -z "${MYSQLHOST}" ]; then
  read -p "Enter the MySQL server hostname: " MYSQLHOST
fi
if [ -z "${MYSQLHOST}" ]; then
  printf "MySQL hostname not provided, assuming localhost...\n" 1>&2
  MYSQLHOST="localhost"
fi

MYSQLPASS="$2"
if [ -z "${MYSQLPASS}" ]; then
  read -s -p "Enter the MySQL root user password: " MYSQLPASS
  echo
fi
if [ -z "${MYSQLPASS}" ]; then
  printf "MySQL root user password not provided, assuming it's not set...\n" 1>&2
fi

set -e

printf "[1/8] Dumping the database...\n"
mysqldump --host="${MYSQLHOST}" --user="root" --password="${MYSQLPASS}" \
  --opt --order-by-primary \
  --complete-insert --single-transaction --triggers --routines \
  --hex-blob --add-drop-database --result-file /tmp/.database.sample.sql \
  --databases bnetdocs_phoenix

printf "[2/8] Renaming the database locally...\n"
sed -i 's/bnetdocs_phoenix/bnetdocs_phoenix_backup/g' /tmp/.database.sample.sql

printf "[3/8] Uploading the renamed database so we can redact info from it...\n"
mysql --host="${MYSQLHOST}" --user="root" --password="${MYSQLPASS}" < /tmp/.database.sample.sql

printf "[4/8] Redacting private user information...\n"
mysql --host="${MYSQLHOST}" --user="root" --password="${MYSQLPASS}" << EOF
  START TRANSACTION;
  USE bnetdocs_phoenix_backup;
  TRUNCATE TABLE event_log;
  INSERT INTO event_log (id, event_type_id, event_datetime, user_id, ip_address, meta_data)
    VALUES (0,0,NOW(),NULL,NULL,'Redacted event log');
  TRUNCATE TABLE user_profiles;
  UPDATE users SET
    username = CONCAT('redacted.username.', id),
    email = CONCAT('redacted.email.', id, '@example.com'),
    display_name = NULL,
    password_hash = NULL,
    password_salt = NULL,
    options_bitmask = 0;
  COMMIT;
EOF

printf "[5/8] Dumping the redacted database...\n"
mysqldump --host="${MYSQLHOST}" --user="root" --password="${MYSQLPASS}" \
  --opt --order-by-primary \
  --complete-insert --single-transaction --triggers --routines \
  --skip-extended-insert --hex-blob --add-drop-database \
  --result-file /tmp/.database.sample.sql \
  --databases bnetdocs_phoenix_backup

printf "[6/8] Deleting the redacted database from the server...\n"
mysql --host="${MYSQLHOST}" --user="root" --password="${MYSQLPASS}" << EOF
  DROP DATABASE bnetdocs_phoenix_backup;
EOF

printf "[7/8] Renaming the redacted database locally...\n"
sed -i 's/bnetdocs_phoenix_backup/bnetdocs_phoenix/g' /tmp/.database.sample.sql

printf "[8/8] Moving database into current working directory...\n"
mv /tmp/.database.sample.sql ./database.sample.sql

printf "Operation complete!\n"
