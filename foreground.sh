#!/bin/bash

##
./vendor/bin/mysql-import vtiger.sql

## run cron
cron

## run apache
apache2-foreground
