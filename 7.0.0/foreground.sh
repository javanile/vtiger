#!/bin/bash
set -e

## import database using environment variables
./vendor/bin/mysql-import vtiger.sql

## fix bootstrap configuration


## run cron
cron

## run apache
apache2-foreground
