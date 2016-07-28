echo "### START SYSTEM CONFIGURATION ###"
sudo apt-get update
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password lukida'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password lukida'
sudo apt-get install -y mysql-server mysql-client apache2 php5 php5-dev libcurl4-gnutls-dev libxml2-dev libapache2-mod-php5 php5-mysql php5-solr php5-xdebug htop vim mc tmux rsync
printf "\n" | sudo pecl install -n solr
echo 'extension=solr.so' | sudo tee /etc/php5/apache2/conf.d/30-solr.ini

echo "### START ENABLING REMOTE XDEBUG ###"
echo "xdebug.remote_enable = 1" | sudo tee -a /etc/php5/apache2/conf.d/20-xdebug.ini
echo "xdebug.remote_connect_back=1" | sudo tee -a /etc/php5/apache2/conf.d/20-xdebug.ini

echo "### START COPYING THE PROJECT ###"
sudo mkdir /var/www/lukida
sudo cp -r /vagrant/kernel /var/www/lukida/kernel
sudo cp -r /vagrant/libraries /var/www/lukida/libraries
sudo chmod -R uga+r /var/www/lukida
sudo ln -s /var/www/lukida/kernel/application/systemassets /var/www/lukida/libraries/Lukida_newlibrary/systemassets

echo "### START DATABASE SETUP ###"
mysql -uroot -plukida -e "CREATE USER 'newlibrary'@'localhost' IDENTIFIED BY 'newlibrary';"
mysql -uroot -plukida -e "GRANT ALL PRIVILEGES ON *.* TO 'newlibrary'@'localhost';"
mysql -unewlibrary -pnewlibrary -e "CREATE DATABASE newlibrary"
mysql -unewlibrary -pnewlibrary newlibrary < /vagrant/mysql_import.sql

echo "### START CONFIGURING THE PROJECT ###"
sudo cp /vagrant/vagrant/lukida_apache.conf /etc/apache2/sites-available/lukida.conf
sudo a2dissite 000-default.conf
sudo a2ensite lukida
sudo a2enmod rewrite
sudo cp /vagrant/vagrant/sync_project.sh /usr/local/bin/lukida_sync

echo "### INSTALL MYSQL WEBADMIN ###"
sudo debconf-set-selections <<< 'phpmyadmin phpmyadmin/dbconfig-install boolean true'
sudo debconf-set-selections <<< 'phpmyadmin phpmyadmin/app-password-confirm password lukida'
sudo debconf-set-selections <<< 'phpmyadmin phpmyadmin/mysql/admin-pass password lukida'
sudo debconf-set-selections <<< 'phpmyadmin phpmyadmin/mysql/app-pass password  '
sudo debconf-set-selections <<< 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2'
sudo apt-get -y install phpmyadmin

sudo service apache2 stop
