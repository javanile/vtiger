#!/bin/bash

##
DBCHECK=$(php dbcheck.php)

## run cron
cron

## run apache
apache2-foreground