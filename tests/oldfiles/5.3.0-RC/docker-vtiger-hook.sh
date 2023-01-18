#!/usr/bin/env bash

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2020 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

file="/etc/docker-vtiger-hook/$1.sh"

if [[ -f "${file}" ]]; then
  bash "${file}"
fi
