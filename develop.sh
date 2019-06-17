#!/bin/bash
set -e

## Version      | Tested
## ----------------------
## 7.1.0        | Yes
## 7.1.0-RC     |
## 7.0.1        |
## 7.0.0
## 6.5.0
## 6.4.0
## 6.3.0
## 6.2.0
## 6.1.0
## 6.1.0-Beta
## 6.0.0
## 6.0.0-RC
## 6.0.0-Beta
## 5.4.0
## 5.4.0-RC
## 5.3.0
## 5.3.0-RC
## 5.2.1
## 5.2.0
## 5.2.0-RC
## 5.2.0-VB2
## 5.2.0-VB1
## 5.1.0

source .env
[[ ! -d ./volumes/logs ]] && mkdir -p ./volumes/logs
[[ ! -d ./volumes/storage ]] && mkdir -p ./volumes/storage
echo -e "\n----[ build vtiger ${VERSION} ]----"
docker-compose down -v --remove-orphans
docker-compose up -d mysql && sleep 5
docker-compose run --rm update
cp develop-install.sh ${VERSION}
docker-compose stop  vtiger
docker-compose rm -f vtiger
docker-compose build vtiger
docker-compose up vtiger
#docker-compose logs vtiger
