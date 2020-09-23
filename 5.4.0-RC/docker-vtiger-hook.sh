#!/usr/bin/env bash

file="/etc/docker-vtiger-hook/$1.sh"

if [[ -f "${file}" ]]; then
  bash "${file}"
fi
