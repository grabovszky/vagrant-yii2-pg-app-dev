# Yii2 Enhanced

## Table of Contents

1. [Purpose](#purpose)
2. [Description](#description)
3. [Technologies used](#technologies-used)
4. [Code conventions](#code-conventions)
5. [Directory structure](#directory-structure)
6. [Installation](#installation)

## Purpose

The purpose of this project is to provide an advanced Yii2 template.

## Description

Yii2 enhanced provides an easily set uppable and configurable Yii2 environment with Vagrant, with a preconfigured dev server for forntend and backend.

## Technologies used

- [Vagrant](https://www.vagrantup.com/)
    - Used for setting up the development environment

- [Yii2](https://www.yiiframework.com/)
    - Robust PHP framework
    - [Guide](https://www.yiiframework.com/doc/guide/2.0/en)
    - The project uses [Yii2 advanced template](https://github.com/yiisoft/yii2-app-advanced)

- [PostgreSQL 11](https://www.postgresql.org/)

## Code conventions

- [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) software design pattern
- The project uses the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard for PHP

## Directory structure

```
project
|   LICENSE.md
|   README.md
|   Vagrantfile         // Used for an easy development environment setup
└───backend
|   └─── controllers
|   └─── models
|   └───views
|   └───web
└───common
|   └───config          // Database configuration here
|   └───models          // Contains all database models
└───console
|   └───controllers
|   └───dummy-data      // Use for an easy database test data fillup
|   └───migrations      // Contains all necessary migrations
└───frontend
|   └───controllers
|   └───models
|   └───views
|   └───web
└───vagrant
|   └───config          // Contains the vagrant configurations files
|   └───provision
```

## Installation

1. Install [VirtualBox](https://www.virtualbox.org/)
2. Install [Vagrant](https://www.vagrantup.com/)
3. Create a GitHub personal [API token](https://github.com/settings/tokens)
4. Clone repository:

```bash
  git clone https://github.com/grabovszky/yii2-enhanced.git
```

5. Configure `vagrant-local.yml` with your GitHub API token:

```bash
  cd /path/to/application/vagrant/config
  cp vagrant-local.example.yml vagrant-local.yml
```

6. Execute init and select development environment:

```bash
  cd cd /path/to/yii-application/
  ./init
```

8. Install vagrant plugins and run vagrant

```bash
  cd /path/to/yii-application/
  vagrant plugin install vagrant-hostmanager
  vagrant plugin install vagrant-vbguest
  vagrant up
```

9. You are all set, you can view your application at [ticketing-system.test](http://ticketing-system.test)
