#!/usr/bin/env bash

#### Import script args ####

timezone=$(echo "$1")
domain=$(echo "$2")
ROOT_FOLDER=$(echo "$3")

# PostgreSQL settings
DB_USER=$(echo "$4")
DB_USER_PASSWORD=$(echo "$5")
DB_NAME=$(echo "$6")
POSTGRES_VERSION=$(echo "$7")

#### Bash helpers ####

function info {
  echo " "
  echo "--> $1"
  echo " "
}

#### Provision script ####

info "Provision-script user: `whoami`"

# Configure hu_HU locale on system
info "Configuring locales"
echo 'hu_HU.UTF-8 UTF-8' >> /etc/locale.gen
locale-gen

# Update timezone to current timezone
info "Configuring timezone"
timedatectl set-timezone $timezone

# Update repository
info "Updateing OS software"
DEBIAN_FRONTEND=noninteractive apt-get -yq update
DEBIAN_FRONTEND=noninteractive apt-get -yq -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" upgrade
DEBIAN_FRONTEND=noninteractive apt-get --purge -y autoremove

# Install additional OS software
info "Installing additional software"
apt-get install -y vim ssh screen sudo less ntp ntpdate lsof rsync mc whois htop sysstat bzip2 tcpdump dstat dnsutils telnet curl

# Set default editor to VIM
update-alternatives --set editor /usr/bin/vim.basic

# Install apache
info "Installing apache2"
apt-get install -y apache2

# Install php7 and some necessary additions
info "Installing php7 and some necessary additions"
apt-get install -y php7.3 php7.3-cli php7.3-common php7.3-curl php7.3-gd php7.3-intl php7.3-mbstring php7.3-pgsql php7.3-soap php7.3-xml php7.3-zip php-mail php-memcached php-xdebug phpunit

# Install git
info "Installing git"
apt-get install -y git

# Install composer
info "Installing composer"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#### Install and configure PostgreSQL ####

# Install postgreSQL
info "Installing PostgreSQL version: $POSTGRES_VERSION"
apt-get -y install "postgresql-$POSTGRES_VERSION"

# Fix postgreSQL permissions
info "Fixing postgreSQL permissions"
sed -i '/^local[[:space:]]*all[[:space:]]*all[[:space:]]*peer/ s/peer/trust/' /etc/postgresql/$POSTGRES_VERSION/main/pg_hba.conf

# Restart postgreSQL to finalize config files
service postgresql restart

# Create postgreSQL user
info "Creating postgres $DB_USER role with $DB_USER_PASSWORD password"
su postgres -c "psql -c \"CREATE ROLE $DB_USER NOSUPERUSER LOGIN PASSWORD '$DB_USER_PASSWORD'\" "

# Create postgreSQL database
info "-Creating $DB_NAME database for user $DB_USER"
su postgres -c "createdb -E UTF8 -T template0 --locale=hu_HU.utf-8 -O $DB_USER $DB_NAME"

# Configure apache2
info "Configuring apache2"
sed -i 's/APACHE_RUN_USER=www-data/APACHE_RUN_USER=vagrant/' /etc/apache2/envvars
sed -i 's/APACHE_RUN_GROUP=www-data/APACHE_RUN_GROUP=vagrant/' /etc/apache2/envvars

info "Enabling site configuration"
ln -s /var/www/html/$ROOT_FOLDER/vagrant/apache/app.conf /etc/apache2/sites-enabled/0-$domain.conf
a2enmod rewrite
