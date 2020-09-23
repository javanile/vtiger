#!/usr/bin/env bash

file="/etc/docker-vtiger-hook/$1.sh"

[[ -f "${file}" ]] && bash "${file}"
