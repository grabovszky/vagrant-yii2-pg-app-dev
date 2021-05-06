# Vagrant, Yii2, PostgreSQL app template

## Installation

1. Install [VirtualBox](https://www.virtualbox.org/)
2. Install [Vagrant](https://www.vagrantup.com/)
3. Create a GitHub personal [API token](https://github.com/settings/tokens)
4. Clone repository:

```bash
  git clone https://github.com/grabovszky/vagrant-yii2-pg-app-dev
```

5. Configure `vagrant-local.yml` with your Github API token:

```bash
  cd /path/to/application/vagrant/config
  cp vagrant-local.example.yml vagrant-local.yml
```

6. Execute init and select developement environment:

```bash
  cd cd /path/to/yii-application/
  ./init
```

7. Update /path/to/application/common/config/`main-local.php` with your postgres connection

```php
  db' => [
              'class' => 'yii\db\Connection',
              'dsn' => 'pgsql:host=localhost;dbname=vagrant',
              'username' => 'vagrant',
              'password' => 'password',
              'charset' => 'utf8',
          ],
```

8. Install vagrant plugins and run vagrant

```bash
  cd /path/to/yii-application/
  vagrant plugin install vagrant-hostmanager
  vagrant plugin install vagrant-vbguest
  vagrant up
```

9. You are all set, you can view your application at [vagrant-yii2-pg-app-dev.test](http://vagrant-yii2-pg-app-dev.test)
