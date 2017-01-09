# Lukida

Lukida (c) 2015-2017 is a Discovery-Software from VZG.
Lukida-Homepage is [here](https://www.lukida.org/)

###Requirements
1) Linux or Windows webserver with PHP, MySQL and ability to send mails
2) if used with solr index-system
   PHP PECL Solr Module https://pecl.php.net/package/solr

###Installation

1) Download lukida
This is the main software 

2) Rename the libraries/lukida_newlibrary folder to something more convienient

3) Create a link from your library to kernel
Linux:
```
cd .../lukida/libraries/lukida_newlibrary
ln -s ../../kernel/application/systemassets
```
Windows:
```
cd .../lukida/libraries/lukida_newlibrary
mklink /d /j systemassets ..\..\kernel\application\systemassets
```

4) Setup and import the mysql database
a) Create an empty mysql database and an user who has full access to that new database
b) Import the mysql_import.sql file into that database
c) Configure kernel/application/config/database.php by replacing these values 
```
'hostname' => 'localhost',
'username' => 'lukida_newlibrary',
'password' => 'lukida_newlibrary',
'database' => 'lukida_newlibrary',
```
with your mysql connection information.

5) Point your webbrowser's document_root to .../lukida/libaries/lukida_newlibrary. Add to environment settings
```
# Name of library
SetEnv LIBRARY "newlibrary"

# Mode (development, test, production)
SetEnv MODE "development"
```
Remember the URL to access lukida (e.g. http://lukida.domain.tld)

**Important**
There is a .htaccess file located in this folder, which is neccessary.

6) Enter the URL in the mail config file ../lukida/libraries/lukida_newlibrary/general.ini:
if development or test mode (without protocol)
```
devurl=lukida.domain.tld
```
if production mode (with protocol)
```
produrl=http://lukida.domain.tld
```

Try the URL in your browser.

###Vagrant virtual machine
For a local developer system, you can use a virtual machine with Virtualbox and Vagrant. These are the installation steps:

1. Be sure to have [Virtualbox](https://www.virtualbox.org/wiki/Downloads) installed.
2. You need a POSIX compatible shell - on MS Windows [GIT Bash](https://git-for-windows.github.io/) is verified to work.
3. Install [Vagrant](https://www.vagrantup.com/downloads.html) (if not present) with Virtualbox guest additions plugin (execute ```vagrant plugin install vagrant-vbguest``` in a shell after the installation of Vagrant).
4. Navigate to the projects root folder in your shell (e.g. the folder with the [Vagrantfile](Vagrantfile)).
5. Run `vagrant up` to automatically download, start and initially provision the virtual machine.

Usage hints:
- Lukida can be accessed via http://192.168.33.12; the db admin via http://192.168.33.12/phpmyadmin/ (user: newlibrary, password: newlibrary)
- you can develop on your host system without immediate impact
- a connection into the virtual machine running Lukida can be done with ```vagrant ssh```
- to apply changes made in the code, run ```lukida_sync``` from inside the virtual machine
- read more about Vagrant on https://www.vagrantup.com/

###Customization

In order to get it running you need to customize your library (.../lukida/libraries/lukida_newlibrary/...) to your needs:

1) Customize the .../lukida/libraries/lukida_newlibrary/general.ini

2) Customize the .../lukida/libraries/lukida_newlibrary/discover.ini

3) Place your logo and other assets into your empty assets structure. 

4) Add more pictures and text to the .../lukida/libraries/lukida_newlibrary/start.html file

Have fun with it!
