..  _`Using Plop`:

Usage
=====

Using Plop usually involves 3 steps, detailed below.

..  contents::


Loading Plop's classes
----------------------

The way to load Plop's classes depends on the installation method selected:

-   For the PHAR installation method, add this snippet near the top of
    your main PHP file:

    ..  sourcecode:: inline-php

        require_once('path/to/Plop-latest.phar');

    (adjust the path with the name of the PHAR archive you downloaded)

-   For the Composer installation method, add this snippet instead near
    the top of your main PHP file:

    ..  sourcecode:: inline-php

        require_once('path/to/vendor/autoload.php');

-   For the PEAR installation method or for an installation from sources,

    ..  sourcecode:: inline-php

        require_once('Plop/Autoloader.php');
        Plop_Autoloader::register();

    Also, make sure the full path to your PEAR installation's ``php_dir``
    (or to Plop's :file:`src/` directory in case you installed it from sources)
    is part of your PHP installation's ``include_path``.

    On Linux, you may use the following command to display your PHP
    installation's ``include_path``:

    ..  sourcecode:: console

        me@home:~$ php -r 'echo ini_get("include_path") . PHP_EOL;'

    On Windows, you may use this command instead (assuming :file:`php.exe`
    is located somewhere on your :envvar:`PATH`) to do the same:

    ..  sourcecode:: bat

        C:\WINDOWS> php -r "echo ini_get('include_path') . PHP_EOL;"


Configuring Plop
----------------

@TODO


Logging some messages
---------------------

Logging messages with Plop usually only involves the following sequence:

..  sourcecode:: inline-php

    // First, grab an instance of Plop.
    // Plop uses a singleton pattern, so the same instance will be returned
    // every time you use this method, no matter where you're calling it from.
    $logging = Plop::getInstance();

    // Now, send a log.
    // Various log levels are available by default:
    // debug, info, warning, error & critical.
    $logging->debug('Hello world');

Log messages may contain variables, which will be replaced with their actual
value when the logging method is called. This comes handy when you need to
apply :abbr:`I18N (Internationalization)` methods on the messages. Eg.

..  sourcecode:: inline-php

    $logging = Plop::getInstance();
    $logging->error(
        _('Sorry %(nick)s, now is not the time for that!'),
        array(
            'nick' => 'Ash',
        )
    );

