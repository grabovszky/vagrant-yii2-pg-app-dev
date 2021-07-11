require 'yaml'
require 'fileutils'

config = {
  local: './vagrant/config/vagrant-local.yml',
  example: './vagrant/config/vagrant-local.example.yml'
}

# Copy config from example if local config not exists
FileUtils.cp config[:example], config[:local] unless File.exist?(config[:local])
# Read config
options = YAML.load_file config[:local]

# Configure vagrant
Vagrant.configure(2) do |config|
  # Select the OS box
  config.vm.box = 'debian/buster64'

  # Update on every vagrant up?
  config.vm.box_check_update = options['box_check_update']

  # Update virtual machine provider
  config.vm.provider 'virtualbox' do |vb|
    # Machine cpus count
    vb.cpus = options['cpus']
    # Machine memory size
    vb.memory = options['memory']
    # Machine name (for VirtualBox UI)
    vb.name = options['machine_name']
  end

  # Machine name (for vagrant console)
  config.vm.define options['machine_name']

  # Machine name (for guest machine console)
  config.vm.hostname = options['machine_name']

  # Network settings
  config.vm.network options['network'], ip: options['ip']

  if ENV['FIRST_RUN'] == 'true'
    config.vbguest.auto_update = false
    config.vm.synced_folder '.', '/vagrant', disabled: true
  else
    # Sync: folder 'ticketing-system-template' (host machine) -> folder '/var/www/html/ticketing-system-template' (guest machine)
    config.vm.synced_folder '.', '/var/www/html/ticketing-system-template', owner: 'vagrant', group: 'vagrant'

    # Disable folder '/vagrant' (guest machine)
    config.vm.synced_folder '.', '/vagrant', disabled: true
  end

  # Hosts settings (host machine)
  config.vm.provision :hostmanager
  config.hostmanager.enabled = true
  config.hostmanager.manage_host = true
  config.hostmanager.ignore_private_ip = false
  config.hostmanager.include_offline = true
  config.hostmanager.aliases = options['domains'].values

  # Provisioners
  config.vm.provision 'shell', path: './vagrant/provision/once-as-root.sh', args: [options['timezone'], options['domains']['frontend'], options['root_folder'], options['db_user'], options['db_user_password'], options['db_name'], options['postgres_version']]

  config.vm.provision 'shell', path: './vagrant/provision/once-as-vagrant.sh', args: [options['github_token'], options['root_folder']], privileged: false

  config.vm.provision 'shell', path: './vagrant/provision/always-as-root.sh', run: 'always'

  # Post-install message (vagrant console)
  config.vm.post_up_message = "Website URL: http://#{options['domains']['frontend']}"
end
