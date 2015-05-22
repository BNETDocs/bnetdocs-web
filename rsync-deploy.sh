#!/bin/bash

function check_bash_version () {
  if [[ "${BASH_VERSION}" != "4."* ]]; then
    printf "[%s] bash is too old\n" "$(date)" >&2
    exit 1
  fi
}

function deploy_project () {
  printf "[%s] deploying...\n" "$(date)"
  cd "$(git rev-parse --show-toplevel)"
  rsync -avzc --delete --delete-excluded --delete-after \
    --progress --exclude-from="./rsync-exclude.txt" \
    "./" web1.localdomain:"/home/nginx/bnetdocs-dev"
  rsync -avzc --delete --delete-excluded --delete-after \
    --progress --exclude-from="./rsync-exclude.txt" \
    "./" web2.localdomain:"/home/nginx/bnetdocs-dev"
}

function main () {
  printf "[%s] initializing...\n" "$(date)"
  check_bash_version
  deploy_project
  printf "[%s] success\n" "$(date)"
}

main
