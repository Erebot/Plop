..  _`prerequisites`:

Prerequisites
=============

This page assumes that the reader has a working PHP setup (either installed
using some distribution's package manager or manually) and lists
the dependencies required to use Plop.

In case you compiled PHP yourself, you may need to recompile it to include
additional extensions (see the list of required PHP dependencies in the section
entitled `Getting started`_ for more information).

..  contents:: :local:


Getting started
---------------

To use Plop in your project, you need PHP 5.3.3 or later, compiled with the
following extensions:

-   pcre
-   sockets
-   SPL


Running Plop from a PHAR
------------------------

If you want to use Plop from a PHP ARchive (phar), the following additional
extensions are required:

-   openssl
-   Phar
-   SimpleXML


Validating your PHP installation
--------------------------------

You can check whether your PHP installation satisfies all the prerequisites
listed above by running the following commands, which will display information
about the PHP version and list all currently enabled extensions:

    ..  sourcecode:: console

        me@home:~$ php -v # Check PHP version
        PHP 5.4.33 (cli) (built: Sep 25 2014 23:41:02) (DEBUG)
        Copyright (c) 1997-2014 The PHP Group
        Zend Engine v2.4.0, Copyright (c) 1998-2014 Zend Technologies

        me@home:~$ php -m # Check available extensions
        [PHP Modules]
        bcmath
        bz2
        calendar
        Core
        ctype
        date
        dom
        ereg
        gd
        gettext
        gmp
        iconv
        intl
        json
        libxml
        mbstring
        mysql
        mysqli
        mysqlnd
        openssl
        pcntl
        pcre
        PDO
        pdo_mysql
        pdo_sqlite
        Phar
        posix
        readline
        Reflection
        session
        SimpleXML
        soap
        sockets
        SPL
        sqlite3
        standard
        sysvmsg
        sysvsem
        sysvshm
        tokenizer
        xdebug
        xml
        xmlreader
        xmlwriter
        xsl
        zip
        zlib

You may also consult the output of ``phpinfo()`` for the same purpose.


..  |---| unicode:: U+02014 .. em dash
    :trim:

.. vim: ts=4 et
