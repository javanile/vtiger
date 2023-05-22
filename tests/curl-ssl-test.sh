#!/usr/bin/env bash
set -e

docker compose run --rm vtiger bash -c "curl -I https://httpstat.us/200"
