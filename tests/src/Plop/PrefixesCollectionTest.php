<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2012 François Poirotte

    Plop is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Plop is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Plop.  If not, see <http://www.gnu.org/licenses/>.
*/

class   Plop_PrefixesCollection_Test
extends Plop_TestCase
{
    public function setUp()
    {
        parent::setUp();
        chdir(dirname(__FILE__));
        $this->_collection = new Plop_PrefixesCollection();
    }

    public function providePaths()
    {
        $cwd = dirname(__FILE__);
        $winCwd = $cwd;
        return array(
            array('foo', "$cwd/foo/"),
            array('./foo', "$cwd/foo/"),
            array('../foo', dirname($cwd) . '/foo/'),
            array('/foo/bar', '/foo/bar/'),
            array('/foo//bar', '/foo/bar/'),
            array('/foo/./bar', '/foo/bar/'),
            array('/foo/../bar', '/bar/'),
            array('C:foo', "$cwd/foo/"),
            array('C:/foo/bar', 'C:\\/foo/bar/'),
            array('C:\\foo/bar', 'C:\\/foo/bar/'),
            array('\\\\foo\\bar\\baz', '\\\\foo\\bar\\/baz/'),
            array('\\\\foo\\bar/baz', '\\\\foo\\bar\\/baz/'),
            array('\\\\?\\UNC\\foo\\bar\\baz', '\\\\foo\\bar\\/baz/'),
            array('\\\\?\\UNC\\foo\\bar/baz', '\\\\foo\\bar\\/baz/'),
            array('\\\\?\\C:foo', "$cwd/foo/"),
            array('\\\\?\\C:/foo', 'C:\\/foo/'),
            array('\\\\?\\C:\\foo', 'C:\\/foo/'),
        );
    }

    /**
     * @covers          Plop_PrefixesCollection::normalizePath
     * @dataProvider    providePaths
     */
    public function testPathNormalization($path, $normalization)
    {
        $this->_collection[] = $path;
        $path = $this->_collection->getIterator()->current();
        $this->assertSame($normalization, $path);
    }

    public function providePrefixes()
    {
        $dir = dirname(__FILE__);
        return array(
            array($dir,             "$dir/foo.php",     "foo.php"),
            array("/*/",            "/foo/bar.php",     "bar.php"),
            array("/*",             "/foo/bar.php",     "bar.php"),
            array("/ba*/",          "/foo/bar.php",     "/foo/bar.php"),
            array("/ba*",           "/foo/bar.php",     "/foo/bar.php"),
            array("/*/bar.php",     "/foo/bar.php",     "/foo/bar.php"),
            array("/[a-c]a?/",      "/bar/foo.php",     "foo.php"),
            array("/[a-c]a??/",     "/bar/foo.php",     "/bar/foo.php"),
            array("/[b][a][r]/",    "/bar/foo.php",     "foo.php"),
            array("/[^b][a][r]/",   "/bar/foo.php",     "/bar/foo.php"),
            array("/[!b][a][r]/",   "/bar/foo.php",     "/bar/foo.php"),
        );
    }

    /**
     * @covers          Plop_PrefixesCollection::stripLongestPrefix
     * @covers          Plop_PrefixesCollection::prefixMatch
     * @covers          Plop_PrefixesCollection::rangeMatch
     * @dataProvider    providePrefixes
     */
    public function testPrefixStripping($prefix, $path, $result)
    {
        $this->_collection[] = $prefix;
        $this->assertSame(
            $result,
            $this->_collection->stripLongestPrefix($path)
        );
    }
}
