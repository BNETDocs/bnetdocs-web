#!/bin/bash

function check_bash_version () {
  if [[ "${BASH_VERSION}" != "4."* ]]; then
    printf "[%s] bash is too old\n" "$(date)" >&2
    exit 1
  fi
}

function deploy_project () {
  cd "$(git rev-parse --show-toplevel)"
  printf "[%s] deploying to lstn.carlbennett.me...\n" "$(date)"
  rsync -avzc --delete --delete-excluded --delete-after \
    --progress --exclude-from="./rsync-exclude.txt" \
    --chown=nginx:www-data --rsync-path="sudo rsync" \
    "./" lstn.carlbennett.me:"/home/nginx/bnetdocs-dev" ||
    return $?
}

function main () {
  printf "[%s] initializing...\n" "$(date)"
  check_bash_version
  deploy_project &&
    printf "[%s] success\n" "$(date)" ||
    printf "[%s] failed to deploy project\n" "$(date)"
}

main
