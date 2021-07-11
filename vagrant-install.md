# Installation

## Installing using Vagrant

1. Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
1. Install [Vagrant](https://www.vagrantup.com/downloads.html)
1. Prepare project:
   ```
   $ git clone [git-repositry]
   $ cd /path/to/application/vagrant/config/
   $ cp vagrant-local.example.yml vagrant-local.yml
   ```

1. Update `vagrant-local.yml` to suit your needs.
1. Run commands from the project root directory:
   ```
   $ cd /path/to/application/
   $ vagrant plugin install vagrant-hostmanager
   $ vagrant plugin install vagrant-vbguest
   ```
1. Virtualbox Guest Additions has problems with some deprecated packages. To circumvent it, use the following script for
   the initial machine creation:
   ```
   $ ./vagrant-install.sh
   ```

That's all. You just need to wait for completion!
