#!/usr/bin/env bash

#### Import script args ####

# Import your personalized github token, located in ./vagrant/config/vagrant-local.yml
GITHUB_TOKEN=$(echo "$1")

# Import the root folder name
ROOT_FOLDER=$(echo "$2")

#### Bash helpers ####

function info {
  echo " "
  echo "--> $1"
  echo " "
}

#### Provision script ####

info "Provision-script user: `whoami`"

# Configure composer
info "Configureing composer"
composer config --global github-oauth.github.com ${GITHUB_TOKEN}

# Install Yii2 necessary composer plugin
info "Installing composer-asset-plugin"
composer global require "fxp/composer-asset-plugin:^1.1.1" --no-progress

# Install project dependencies
info "Installing project dependencies with composer"
cd /var/www/html/$ROOT_FOLDER
composer install -o --prefer-dist --no-progress

# Appy Yii2 migrations
info "Migrating"
./yii migrate <<< "yes"
