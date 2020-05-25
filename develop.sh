#!/usr/bin/env bash
set -e

export version=$1

if [[ -z "$version" ]]; then
    echo "Syntax error: Specify a suitable version (eg. ./develop.sh 7.1.0)"
    exit 1
fi

if [[ ! -d "$version" ]]; then
    echo "Version error: The specified version not exists."
    exit 1
fi

echo -e "\n----[ build vtiger ${version} ]----"

#docker-compose down -v --remove-orphans
#docker-compose run --rm debian
rm -fr ./vtiger && true
mkdir -p vtiger && true
#docker-compose run --rm debian
./update.sh ${version}

cp develop-install.sh ${version}

docker-compose build vtiger

docker-compose down -v --remove-orphans
docker-compose up vtiger
