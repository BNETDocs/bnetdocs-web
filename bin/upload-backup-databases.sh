#!/usr/bin/env bash

set -e

if [ -z "$OS_PASSWORD" ] || [ -z "$OS_TENANT_ID" ]; then
  if [ -s $HOME/.openrc ]; then
    printf "Loading OpenStack credentials from $HOME/.openrc...\n"
    . $HOME/.openrc
  fi
fi

if [ -z "$OS_PASSWORD" ] || [ -z "$OS_TENANT_ID" ]; then
  printf "Please load your OpenStack credentials file.\n"
  exit 1
fi

SOURCE_DIRECTORY="$(git rev-parse --show-toplevel)"
CONTAINER_NAME="bnetdocs"

if [ ! -d "$SOURCE_DIRECTORY/tmp/sql-backups" ]; then
  printf "No sql backups to upload.\n"
  exit 0
fi

set -x

pushd "$SOURCE_DIRECTORY/tmp"

swift-3 \
  --os-auth-url "$OS_AUTH_URL" \
  --auth-version 3 \
  --os-project-id "$OS_TENANT_ID" \
  --os-username "$OS_USERNAME" \
  --os-password "$OS_PASSWORD" \
  upload "$CONTAINER_NAME" \
  sql-backups

popd

