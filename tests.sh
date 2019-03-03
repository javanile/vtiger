#!/bin/bash
set -e

for version in */; do
    [ -d "$version" ] || continue
    export VERSION=$(basename ${version})
    echo "----[ javanile/vtiger:${VERSION} ]----"
    docker-compose -f docker-compose.tests.yml build vtiger

    #docker-compose -f docker-compose.tests.yml up -d vtiger && sleep 10s
    #docker-compose -f docker-compose.tests.yml logs vtiger

    #docker-compose -f docker-compose.tests.yml down -v --remove-orphans
    exit
done
