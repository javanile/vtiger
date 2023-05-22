#!/usr/bin/env bash
set -e

##
# Vtiger CRM for Docker
# Copyright (c) 2018-2023 Francesco Bianco <bianco@javanile.org>
# MIT License <https://git.io/docker-vtiger-license>
##

declare -A source_code_hosting
source_code_hosting=(
  ["sourceforge_vtiger"]="http://sourceforge.net/projects/vtigercrm/files/"
  ["github_javanile"]="https://github.com/javanile/vtiger-core/archive/"
)

declare -A versions
versions=(
  ["7.5.0"]="versions/apache/php7/debian,7.3.12,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%207.5.0/Core%20Product/vtigercrm7.5.0.tar.gz"
  ["7.2.0"]=main,7.3.12,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%207.2.0/Core%20Product/vtigercrm7.2.0.tar.gz
  ["7.1.0"]="versions/apache/php7/debian/buster,7.0.33,mariadb-server-10.3,github_javanile,vtiger-core-7.1.0,7.1.0.tar.gz"
  ["7.1.0-RC"]=main,7.0.33,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,vtiger%20CRM%207.1.0%20RC/Core%20Product/vtigercrm7.1.0rc.tar.gz
  ["7.0.1"]=main,7.0.33,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,vtiger%20CRM%207.0.1/Core%20Product/vtigercrm7.0.1.tar.gz
  ["7.0.0"]=main,7.0.33,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,vtiger%20CRM%207.0/Core%20Product/vtigercrm7.0.0.tar.gz
  ["6.5.0"]=main,5.6.40,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,vtiger%20CRM%206.5.0/Core%20Product/vtigercrm6.5.0.tar.gz
  ["6.4.0"]=main,5.6.40,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,vtiger%20CRM%206.4.0/Core%20Product/vtigercrm6.4.0.tar.gz
  ["6.3.0"]=main,5.6.40,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,vtiger%20CRM%206.3.0/Core%20Product/vtigercrm6.3.0.tar.gz
  ["6.2.0"]=main,5.6.40,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,vtiger%20CRM%206.2.0/Core%20Product/vtigercrm6.2.0.tar.gz
  ["6.1.0"]=main,5.6.40,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,vtiger%20CRM%206.1.0/Core%20Product/vtigercrm6.1.0.tar.gz
  ["6.1.0-Beta"]=main,5.6.40,mariadb-server-10.1,sourceforge_vtiger,vtigercrm,Vtiger%20CRM%206.1.0%20Beta/Core%20Product/vtigercrm-6.1.0-ea.tar.gz
  ["6.0.0"]=robust,5.6.30,mysql-server-5.5,sourceforge_vtiger,vtigerCRM,vtiger%20CRM%206.0.0/Core%20Product/vtigercrm6.0.0.tar.gz
  ["6.0.0-RC"]=robust,5.6.40,mariadb-server-10.1,sourceforge_vtiger,vtigerCRM,vtiger%20CRM%206.0%20RC/Core%20Product/vtigercrm-6.0.0rc.tar.gz
  ["6.0.0-Beta"]=robust,5.6.40,mariadb-server-10.1,sourceforge_vtiger,vtigerCRM,vtiger%20CRM%206.0%20Beta/Core%20Product/vtigercrm-6.0Beta.tar.gz
  ["5.4.0"]=stale,5.3.29,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.4.0/Core%20Product/vtigercrm-5.4.0.tar.gz
  ["5.4.0-RC"]=stale,5.6.40,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.4.0%20RC/Core%20Product/vtigercrm-5.4.0-RC.tar.gz
  ["5.3.0"]=stale,5.6.40,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.3.0/Core%20Product/vtigercrm-5.3.0.tar.gz
  ["5.3.0-RC"]=stale,5.6.40,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.3.0%20RC/Core%20Product/vtigercrm-5.3.0-RC.tar.gz
  ["5.2.1"]=stale,5.6.40,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.2.1/Core%20Product/vtigercrm-5.2.1.tar.gz
  ["5.2.0"]=stale,5.6.40,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.2.0/Core%20Product/vtigercrm-5.2.0.tar.gz
  ["5.2.0-RC"]=stale,5.6.40,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.2.0%20RC/vtigercrm-5.2.0-RC.tar.gz
  ["5.2.0-VB2"]=stale,5.6.40,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.2.0%20VB2/vtigercrm-5.2.0-vb2.tar.gz
  ["5.2.0-VB1"]=stale,5.6.40,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.2.0%20VB1/vtigercrm-5.2.0-vb1.tar.gz
  ["5.1.0"]=stale,5.3.29,mariadb-server-10.3,sourceforge_vtiger,vtigercrm,vtiger%20CRM%205.1.0/Core%20Product/vtigercrm-5.1.0.tar.gz
)
