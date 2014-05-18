#!/bin/sh

php -f /home/nginx/bnetdocs/check-servers.php
EXIT_CODE=$?

if [ $EXIT_CODE -ne 0 ]; then
  echo -ne "\e[1;29m["
  echo -n "`date +'%Y-%m-%d %H:%M:%S %z'`"
  echo -ne "] \e[0;0m"
  echo -e "\e[1;31mScript did not finish successfully.\e[0;0m"
fi

exit $EXIT_CODE

