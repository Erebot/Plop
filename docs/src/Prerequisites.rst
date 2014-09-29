..  _`prerequisites`:

Prerequisites
=============

This page assumes that the reader has a working PHP setup (either installed
using some distribution's package manager or manually) and lists
the dependencies required to use Plop.
In case you compiled PHP yourself, you may need to recompile it to include
additional extensions (see the list of required PHP dependencies in the section
entitled `Getting started`_ for more information).

Plop is known to work with most PHP versions.
Plop should run correctly on both Windows (XP or later) and Linux (most distros).
The code is tested using an automated process on Windows Vista (64 bits),
Windows 7 (64 bits), Windows 8.1 (64 bits), Debian Stable (64 bits)
and CentOS 6 (64 bits), as reflected by our `Continuous Integration server`_.

..  contents:: :local:


Getting started
---------------

To use Plop in your project, you only need a version of PHP compiled with the
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


Checking whether these requirements are fulfilled
-------------------------------------------------

You can check whether your version of PHP contains all the necessary extensions
by running the following command, which will list all the extensions currently
enabled for your installation:

    ..  sourcecode:: console

        me@home:~$ php -m
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

        [Zend Modules]
        Xdebug

You may also consult the output of ``phpinfo()`` for the same purpose.


..  |---| unicode:: U+02014 .. em dash
    :trim:

..  _`Continuous Integration server`:
    https://ci.erebot.net/components/

.. vim: ts=4 et
