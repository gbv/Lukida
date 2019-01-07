# Lukida

Lukida (c) 2015-2019 is a Discovery-Software from VZG.
Lukida-Homepage is [here](https://www.lukida.org/)

## Requirements

1) Linux or Windows webserver with PHP, MySQL and ability to send mails
2) if used with Solr index-system
PHP PECL Solr Module https://pecl.php.net/package/solr
3) pear class File_MARC with MARC record-system

    ```
    pear install File_MARC
    ```

## Installation

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
   1) Create an empty mysql database and an user who has full access to that new database
   2) Import the mysql_import.sql file into that database
   3) Remember the database connection
5) Point your webbrowser's document_root to the correct path by replacing /var/www/html/lukida/libaries/lukida_newlibrary and use the rewrite lines from this **Example VirtualHost**

    ```
    <VirtualHost *:80>
        # Server Name
        ServerName lukida.domain.tld

        # Path to your new library 
        DocumentRoot /var/www/html/lukida/libaries/lukida_newlibrary
        
        # Name of library
        SetEnv LIBRARY "New Library"
        
        # Mode (development, test, production)
        SetEnv MODE "development"
        
        # Path to your new library 
        <Directory /var/www/html/lukida/libaries/lukida_newlibrary>
            DirectoryIndex index.php
            AllowOverride All
            Require all granted
            RewriteEngine On
            RewriteRule ^\.htaccess$ - [F]
            RewriteRule ^.*ini$ / [R,L]
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_URI} !robots.txt
            RewriteCond %{REQUEST_URI} !favicon.ico
            RewriteRule ^(.*)$ index.php/$1 [L]
        </Directory>
    </VirtualHost>
    ```

    Remember the URL (ServerName) to access lukida (e.g. http://lukida.domain.tld)

## Customization

In order to get it running you need to customize your library (.../lukida/libraries/lukida_newlibrary/...) to your needs:

1) Customize the .../lukida/libraries/lukida_newlibrary/general.ini
   - Configure the general settings

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

   - Enter the remembered URL in den domains-sections
        
        ```
        [domains]
        
        ; Develpment URLs 
        devurl=lukida.local

        ; Test URLs
        testurl=lukida.testdomain.tld,https://lukida.test2.tld

        ; Production URLs
        produrl=https://lukida.domain.tld

        ```

   - Enter the remembered database connection

        ```
        [database]
        type=mysql
        hostname=localhost
        username=lukida_newlibrary
        password=lukida_newlibrary
        database=lukida_newlibrary
        ```

   - Configure the other sections as well

2) Customize the .../lukida/libraries/lukida_newlibrary/discover.ini
3) Place your logo and other assets into your empty assets structure. 
4) Add more pictures and text to the .../lukida/libraries/lukida_newlibrary/start.html file

## Test

Try the URL in your browser.

Have fun with it!
