<?php
/**
 * This file is used to provide extra files/packages outside package.xml
 * More information: http://pear.php.net/manual/en/pyrus.commands.package.php#pyrus.commands.package.extrasetup
 */

$extrafiles = array();
include(
    __DIR__ .
    DIRECTORY_SEPARATOR . 'buildenv' .
    DIRECTORY_SEPARATOR . 'extrafiles.php'
);

