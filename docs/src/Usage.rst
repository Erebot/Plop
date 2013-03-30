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

There are 4 different types of objects that you can configure in Plop.
Each one is described below.

..  _`loggers`:

Loggers
~~~~~~~

A logger intercepts logging messages for a given method, class, file
or directory. This is decided at construction time based on the arguments
passed to :api:`Plop_Logger::__construct`.

Internally, Plop builds up a hierarchy of loggers like so::

    root-level logger
        directory-level logger
            file-level logger
                class-level logger
                    method/function-level logger

Logging messages "bubble up". That is, Plop first looks for a method or
function-level logger to handle the message. If none can be found, it looks
for a class-level logger (in case the message was emitted from a method).
Then it looks for a file-level logger, then a logger for the directory
containing the file, then a logger for that directory's parent, etc. until
it reaches the root-level logger, which always exists.

Whichever logger is found first will be the one to handle the message.

..  note::
    The root-level logger (root logger) comes pre-configured with a handler
    that logs messages to ``STDERR`` using basic formatting.

Several aspects of a logger can be configured, such as:

-   The logging level. Whenever a message is received whose level is lower
    than the logger's logging level, the message is ignored, **but** no other
    logger will be called to handle the message (effectively preventing the
    message from bubbling further).

-   The record factory. This factory is used to create records of logging
    messages, intended to keep track of the message's contextual information.
    This factory must implement the :api:`Plop_RecordFactoryInterface`
    interface and is usually an instance of :api:`Plop_RecordFactory`.

-   :ref:`Filters`.

-   :ref:`Handlers`.

Once a logger has been created and configured, you can tell Plop about it,
using the following code snippet:

..  sourcecode:: inline-php

    $logging = Plop::getInstance();
    $logging[] = $newlyCreatedLogger;

This will add the logger to the list of loggers already known to Plop.
If a logger had already been registered in Plop with the same "identity"
(file/directory, class and method names), it will be replaced with the new one.

..  seealso::

    :api:`Plop_LoggerInterface`
        Detailed API documentation on the interface implemented by loggers.

    :api:`Plop_LoggerAbstract`
        An abstract class that can be useful when implementing your own logger.

    :api:`Plop_IndirectLoggerAbstract`
        An abstract class that can be useful when implementing an indirect
        logger. An indirect logger is a logger which relies on another logger
        to work. Plop's main class (:api:`Plop`) is an example of such a logger.

    :api:`Plop_Logger`
        The most commonly found type of loggers.

..  _`filters`:

Filters
~~~~~~~

Filters are associated with either :ref:`loggers <Loggers>` or
:ref:`handlers <handlers>` through an object implementing
:api:`Plop_FiltersCollectionInterface` (usually an instance of
:api:`Plop_FiltersCollection`) and are used to restrict what messages will be
handled.
They are applied once the message has been turned into a log record
and work by defining various criteria such a record must respect.

If a record respects all of the criteria given in the collection, the
:ref:`handlers <Handlers>` associated with the logger are called in turn
to do their work.

..  note::
    The "level" associated with a logger acts like a lightweight filter.
    In fact, the same effect could be obtained by defining a collection
    containing an instance of :api:`Plop_Filter_Level` with the level
    desired.

..  warning::
    Not all handlers make use of filters. Therefore, depending on the handlers
    used, it is possible that the filters will be ignored entirely.

To associate a new filter with a logger or handler, use the following code
snippet:

..  sourcecode:: inline-php

    $filters = $logger_or_handler->getFilters();
    $filters[] = $newFilter;

Please note that this will **not** replace existing filters.
Records will still have to pass the previous filters, but they will also
have to pass the new filter before they can be handled.

..  seealso::

    :api:`Plop_FiltersCollectionInterface`
        Detailed API documentation for the interface representing a collection
        of filters.

    :api:`Plop_FilterInterface`
        Detailed API documentation for the interface implemented by all filters.
        This page also references all the filters that can be used in a
        collection.

..  _`handlers`:

Handlers
~~~~~~~~

Handlers are associated with :ref:`loggers <Loggers>` through an object
implementing :api:`Plop_HandlersCollectionInterface` (usually an instance of
:api:`Plop_HandlersCollection`) and are used to define the treatment applied
to log records.

Various types of handlers exist that can be used to log message to different
locations such as the system's event logger (syslog), a (rotated) file,
a network socket, ...

Like with loggers, several aspects of a handler can be configured:

-   :ref:`Its associated formatter <Formatters>`.

-   :ref:`Filters`.

To associate a new handler with a logger, use the following code snippet:

..  sourcecode:: inline-php

    $handlers = $logger->getHandlers();
    $handlers[] = $newHandler;

Please note that this will **not** replace existing handlers.
Also, both the previously defined handlers and the newly added one
will be called when a log record must be handled.

..  seealso::

    :api:`Plop_HandlersCollectionInterface`
        Detailed API documentation for the interface representing a collection
        of handlers.

    :api:`Plop_HandlerAbstract`
        An abstract class that can be useful when implementing a new handler.

    :api:`Plop_HandlerInterface`
        Detailed API documentation for the interface implemented by all
        handlers. This page also references all the handlers that can be
        used in a collection.

..  _`formatters`:

Formatters
~~~~~~~~~~

Each :ref:`handler <Handlers>` has an associated formatter, which is used
when a record needs formatting.
A formatter defines how the final message will look like.

There are a few things about a formatter that you can configure:

-   The main format. This string serves as a pattern for the final message.

    When using an instance of :api:`Plop_Formatter` as the formatter,
    it may contain `Python-like string formats`__ using the syntax for
    dictionaries.

    That is, it may contain something like the following::

        [%(asctime)s] %(levelname)s - %(message)s

    The default format in that case is defined in
    :api:`Plop_Formatter::DEFAULT_FORMAT`.

    Several pre-defined formats exist that depend on the particular
    implementation used to represent records.
    For example, :api:`Plop_Record` closely follows the formats defined
    by `Python's logging module`__ whenever they are applicable.

-   The format for dates and times.

    When using an instance of :api:`Plop_Formatter` as the formatter,
    it uses the formatting options from PHP's `date()`__ function.
    Also, the default format for dates and times is then defined in
    :api:`Plop_Formatter::DEFAULT_DATE_FORMAT`.


-   The current timezone as a `DateTimeZone`__ object.
    This information is used when formatting dates and times for log records
    that were created in a timezone that does not match the local timezone.

To associate a new formatter with a handler, use the following code snippet:

..  sourcecode:: inline-php

    $handler->setFormatter($newFormatter);

Please note that this **will** replace any formatter previously in place.

..  seealso::

    :api:`Plop_FormatterInterface`
        Detailed API documentation for the interface implemented by all
        formatters.

    :api:`Plop_Formatter`
        The most common implementation of formatters.

    :api:`Plop_Record`
        The most common implementation for log records.

    http://www.php.net/class.datetime.php#datetime.constants.types
        PHP's predefined constants to represent several popular
        types of date/time formatting.

    http://php.net/timezones.php
        List of timezone identifiers supported by PHP.

..  __: http://docs.python.org/2/library/stdtypes.html#string-formatting
..  __: http://docs.python.org/2/library/logging.html#logrecord-attributes
..  __: http://www.php.net/function.date.php
..  __: http://www.php.net/class.datetimezone.php

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

