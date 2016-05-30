#!/bin/bash

if [ -z "${SOURCE_DIRECTORY}" ]; then
  SOURCE_DIRECTORY="$(git rev-parse --show-toplevel)/"
fi
if [ -z "${TARGET_DIRECTORY}" ]; then
  TARGET_DIRECTORY="/home/nginx/bnetdocs-www"
fi

DEPLOY_TARGET="$1"
if [ -z "${DEPLOY_TARGET}" ]; then
  DEPLOY_TARGET="$(cat ${SOURCE_DIRECTORY}/.rsync-target 2>/dev/null)"
fi
if [ -z "${DEPLOY_TARGET}" ]; then
  read -p "Enter the server to deploy to: " DEPLOY_TARGET
fi
if [ -z "${DEPLOY_TARGET}" ]; then
  printf "Deploy target not provided, aborting...\n" 1>&2
  exit 1
fi
echo "${DEPLOY_TARGET}" > ${SOURCE_DIRECTORY}/.rsync-target

set -e

printf "[1/5] Verifying this is the correct repository...\n"
[ "$(grep "BNETDocs\/bnetdocs-web" ${SOURCE_DIRECTORY}/.git/config)" != "" ] \
  || $(
    printf "Error: Wrong repository currently in working directory.\n" 1>&2
    exit 1
)

printf "[2/5] Getting version identifier of this deploy...\n"
DEPLOY_VERSION="$(git describe --always --tags)"

printf "[3/5] Building version information into this deploy...\n"
printf "${DEPLOY_VERSION}" > ${SOURCE_DIRECTORY}/.rsync-version

printf "[4/5] Syncing to deploy target...\n"
rsync -avzc --delete --delete-excluded --delete-after --progress \
  --exclude-from="${SOURCE_DIRECTORY}/rsync-exclude.txt" \
  --chown=nginx:www-data --rsync-path="sudo rsync" \
  "${SOURCE_DIRECTORY}" \
  ${DEPLOY_TARGET}:"${TARGET_DIRECTORY}"

printf "[5/5] Post-deploy clean up...\n"
rm ${SOURCE_DIRECTORY}/.rsync-version

printf "Operation complete!\n"
