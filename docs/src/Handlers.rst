Handlers
========

By default, Plop comes with several handlers, listed below.

To replace the default handler with a custom one, use the following code
snippet:

..  sourcecode:: inline-php

    $plop = \Plop\Plop::getInstance();
    $handlers = $plop->getHandlers();
    $handlers[0] = new \Plop\Handler\Datagram('127.0.0.1', 12345);

..  seealso::

    :api:plop:`Plop\\Handler\\Datagram`
        Send logs over the network using UDP datagrams.

    :api:plop:`Plop\\Handler\\File`
        Store logs into a file, without any rotation taking place.

    :api:plop:`Plop\\Handler\\RotatingFile`
        Store logs into a file, applying a rotation whenever the file
        reaches a certain size.

    :api:plop:`Plop\\Handler\\Socket`
        Send logs over the network using a TCP stream.
        Whenever possible, the same stream is reused across transmissions.

    :api:plop:`Plop\\Handler\\Stream`
        Send logs to an already-open stream, like ``php://stderr``.

    :api:plop:`Plop\\Handler\\SysLog`
        Send logs to a system log (syslog) daemon.

    :api:plop:`Plop\\Handler\\TimedRotatingFile`
        Store logs into a file, applying a rotation based on passing time.

    :api:plop:`Plop\\Handler\\WatchedFile`
        Store logs into a file, which is reopened as necessary
        to support log rotation from external tools.

.. vim: ts=4 et
