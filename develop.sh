#!/bin/bash
set -e

export VERSION=7.1.0

./update.sh && cp develop-install.sh ${VERSION}

docker-compose down --remove-orphans
echo -e "\n----[ build vtiger ${VERSION} ]----"
docker-compose build vtiger
echo -e "\n----[ debug vtiger ${VERSION} ]----"
docker-compose up
