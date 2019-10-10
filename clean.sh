#!/bin/bash

[[ -d ./vtiger ]] && rm -fr ./volumes || true

[[ ! -d ./volumes/logs ]] && mkdir -p ./volumes/logs || true
[[ ! -d ./volumes/storage ]] && mkdir -p ./volumes/storage || true
