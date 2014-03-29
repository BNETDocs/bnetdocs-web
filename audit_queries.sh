#!/bin/sh

LOG_DEFAULT_PATH="/home/nginx/bnetdocs-dev/audited_queries.log"
LOG_PATH="$1"

if [ "$LOG_PATH" = "" ] && test -f "$LOG_DEFAULT_PATH"; then

  echo -e "\033[35mAuto-detected Log File: \033[32m$LOG_DEFAULT_PATH\033[0m"
  LOG_PATH="$LOG_DEFAULT_PATH"

fi

if [ "$LOG_PATH" = "" ]; then

  echo -e "\033[32mUsage: $0 /path/to/bnetdocs/query/audit.log\033[0m"

else

  (tail -n 500 -F $LOG_PATH | awk '{printf "\033[40;1;35m%s %s %s \033[0;32m%s ", $1, $2, $3, $4; $1=""; $2=""; $3=""; $4=""; printf "\033[0;36m%s\033[0m\n", substr($0,5)}')

fi