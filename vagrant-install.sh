#!/usr/bin/env bash

# This script circumvents the error caused by Virtualbox Guest Additions
# not finding some deprecated packages during the initial vagrant up.

export FIRST_RUN='true'
vagrant up --no-provision
vagrant ssh -c 'sudo apt-get -yq update'
vagrant ssh -c 'DEBIAN_FRONTEND=noninteractive sudo apt-get -yq -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" upgrade'
vagrant ssh -c 'sudo apt-get install -y build-essential linux-headers-amd64 linux-image-amd64 python-pip'
vagrant halt
export FIRST_RUN='false'
vagrant up
