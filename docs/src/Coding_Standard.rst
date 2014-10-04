Coding standard
===============

The Plop project uses the `PSR-2`_ coding standard.

Check your code
---------------

To check that your code complies with the standard, run the following
command in the project's root directory:

..  sourcecode:: bash

    vendor/bin/phing qa_codesniffer

This will check your code against the standards described here.

You may also be interested in running the full
:abbr:`QA (Quality Assurance)` test suite with:

..  sourcecode:: bash

    vendor/bin/phing qa


..  _`PSR-2`:
    http://www.php-fig.org/psr/psr-2/

..  vim: et ts=4
