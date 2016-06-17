# Lukida

Lukida (c) 2015-2016 is a Discovery-Software from VZG.
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

*** Important ***
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

###Customization

In order to get it running you need to customize your library (.../lukida/libraries/lukida_newlibrary/...) to your needs:

1) Customize the .../lukida/libraries/lukida_newlibrary/general.ini

2) Customize the .../lukida/libraries/lukida_newlibrary/discover.ini

3) Place your logo and other assets into your empty assets structure. 

4) Add more pictures and text to the .../lukida/libraries/lukida_newlibrary/start.html file

Have fun with it!