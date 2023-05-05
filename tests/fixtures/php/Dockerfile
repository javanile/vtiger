FROM php:7.0.33-apache

RUN echo "deb http://archive.debian.org/debian stretch main" > /etc/apt/sources.list

RUN apt-get update && \
    apt-get install --no-install-recommends -y nano && \
    rm -rf /tmp/* /var/tmp/* /var/lib/apt/lists/*
