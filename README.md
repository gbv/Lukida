# Lukida

Lukida (c) 2015-2017 is a Discovery-Software from VZG.
Lukida-Homepage is [here](https://www.lukida.org/)

###Requirements
1) Linux or Windows webserver with PHP, MySQL and ability to send mails

2) if used with Solr index-system
PHP PECL Solr Module https://pecl.php.net/package/solr

3) pear class File_MARC with MARC record-system
```
pear install File_MARC
```

###Installation

1) Download lukida
This is the main software 

2) Rename the libraries/lukida_newlibrary folder to something more convienient like your library/institution name

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

c) Remember the database connection

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

###Customization

In order to get it running you need to customize your library (.../lukida/libraries/lukida_newlibrary/...) to your needs:

1) Customize the .../lukida/libraries/lukida_newlibrary/general.ini
a) Configure the general settings
```
[general]
title=Lukida Library
softwarename=Lukida
language=ger
iln=<your iln>
isil=<your isil>
frontpage=false
frontpagewithoutlinks=false
frontpagewithoutlukida=false
imprint=http://somewhere
;about=
mailfrom=
```

b) Enter the remembered URL 
if development or test mode (without protocol)
```
; Multiple Develpment URLs (separated by , without protocol)
devurl=lukida.domain.tld
```
if production mode (with protocol)
```
; One Production URL (with protocol)
produrl=http://lukida.domain.tld
```

c) Enter the remembered database connection
```
[database]
type=mysql
hostname=localhost
username=lukida_newlibrary
password=lukida_newlibrary
database=lukida_newlibrary
```

d) Configure the other sections as well

2) Customize the .../lukida/libraries/lukida_newlibrary/discover.ini

3) Place your logo and other assets into your empty assets structure. 

4) Add more pictures and text to the .../lukida/libraries/lukida_newlibrary/start.html file

Try the URL in your browser.

Have fun with it!
