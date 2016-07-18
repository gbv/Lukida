#!/bin/bash
sudo rm -rf /var/www/lukida/*
sudo rsync -r --perms --chmod=uga+r /vagrant/kernel /var/www/lukida/
sudo rsync -rtu --perms --chmod=uga+r /vagrant/libraries /var/www/lukida/
sudo ln -sf /var/www/lukida/kernel/application/systemassets /var/www/lukida/libraries/Lukida_newlibrary/

## enframe all templates with path information
for f in /var/www/lukida/kernel/application/views/*;
  do 
     [ -d $f ] && cd "$f" && find -name "*.php" -exec sh -c 'echo \<\!-- START of file \<?php echo __FILE__\; ?\> \--\> | cat - "$0" > /tmp/out && mv /tmp/out "$0"' {} \; -exec sh -c 'echo \\n\<\!-- END of file \<?php echo __FILE__\; ?\> \--\> >> "$0"' {} \;
  done;

## change config for database and url
sudo sed -i 's/lukida_newlibrary/newlibrary/g' /var/www/lukida/kernel/application/config/database.php
sudo sed -i 's/example.local/lukida.dev/g' /var/www/lukida/libraries/Lukida_newlibrary/general.ini

## use original versions of plain php files
sudo cp -f /vagrant/kernel/application/views/formats/preview/standard.php /var/www/lukida/kernel/application/views/formats/preview/standard.php
sudo cp -f /vagrant/kernel/application/views/formats/fullview/standard.php /var/www/lukida/kernel/application/views/formats/fullview/standard.php