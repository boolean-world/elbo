Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/xenial64"
  config.vm.network "forwarded_port", guest: 8000, host: 8000

  config.vm.provider "virtualbox" do |vb|
    vb.gui = false
    vb.memory = "1024"
  end

  config.vm.provision "shell", inline: <<-SHELL
    export LC_ALL=C.UTF-8
    export DEBIAN_FRONTEND=noninteractive
    debconf-set-selections <<< "mysql-server mysql-server/root_password password root"
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password root"
    add-apt-repository -y ppa:ondrej/php
    curl -sL https://deb.nodesource.com/setup_8.x | bash -
    apt-get purge -y snapd lxcfs lxd ed ftp ufw accountsservice policykit-1
    apt-get autoremove -y
    apt-get update
    sed -ri 's/^bind /#&/;s/^(port ).*$/\\10/;s/^# (unixsocket)/\\1/;s/^(unixsocketperm )[0-9]+/\\1777/' /etc/redis/redis.conf
    systemctl restart redis-server.service
    apt-get install -y htop git unzip php-zip php-cli php-gd php-mbstring php-curl php-intl php-bcmath php-mbstring php-gmp php-mysql php-redis mysql-server mysql-client redis-server redis-tools nodejs
    apt-get upgrade -y
    mkdir -p /home/vagrant/.local/bin/
    wget https://git.io/psysh -O /home/vagrant/.local/bin/psysh
    chmod +x /home/vagrant/.local/bin/psysh
    wget https://getcomposer.org/installer -O - | php
    mv composer.phar /home/vagrant/.local/bin/composer
    chown -R vagrant: /home/vagrant/.local
  SHELL
end