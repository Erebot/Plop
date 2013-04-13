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

/**
 * \brief
 *      A class that can be used to remove a prefix
 *      from a file's path.
 */
class       Plop_PrefixesCollection
implements  Plop_PrefixesCollectionInterface
{
    const NOESCAPE      = 0x01; /*!< Disable backslash escaping. */
    const PATHNAME      = 0x02; /*!< Slash must be matched by slash. */
    const PERIOD        = 0x04; /*!< Period must be matched by period. */
    const LEADING_DIR   = 0x08; /*!< Ignore /<tail> after match. */
    const CASEFOLD      = 0x10; /*!< Case insensitive search. */
    const PREFIX_DIRS   = 0x20; /*!< Directory prefixes of pattern match too. */

    /// List of prefixes.
    protected $_prefixes;

    /// Construct a new collection of prefixes.
    public function __construct()
    {
        $this->_prefixes = array();
    }

    /**
     * \seealso
     *  http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247.aspx
     *  for more information on file/path names on Microsoft® Windows®.
     * \seealso
     *  http://msdn.microsoft.com/en-us/library/gg465305.aspx
     *  for more information on Universal Naming Convention (UNC).
     */
    static private function normalizePath($path, $directory = TRUE)
    {
        // Ignore Windows-only "extended paths prefix",
        // but propagate UNC information if present.
        if (!strncmp($path, '\\\\?\\', 4)) {
            $path = (string) substr($path, 4);
            if (!strncmp($path, 'UNC\\', 4)) {
                $path = '\\' . substr($path, 3);
            }
        }

        // Normalize case.
        if (!strncasecmp(PHP_OS, "Win", 3)) {
            $path = strtolower($path);
        }

        // Initialization.
        $UNC  = !strncmp($path, '\\\\', 2);
        $path = strtr($path, array(DIRECTORY_SEPARATOR => '/', '\\' => '/'));
        $relative = !!strncmp($path, '/', 1);
        $stack = array();
        $parts = explode('/', $path);
        $prefix = '';
        $wd = '.';

        // Handle UNC information.
        if ($UNC) {
            // Remove UNC prefix.
            array_shift($parts);
            array_shift($parts);
            // Copy original UNC information.
            $prefix = '\\\\' .
                        array_shift($parts) . '\\' .    // Server
                        array_shift($parts) . '\\';     // Share
        }

        // Windows disk designation.
        if (count($parts) &&
                 strlen($parts[0]) >= 2 &&
                 $parts[0][1] == ':') {
            if (!strncasecmp(PHP_OS, 'Win', 3)) {
                $wd = substr($parts[0], 0, 2) . ".";
            }
            else {
                $wd = '.';
            }
            // Eg. "C:\foo.txt"
            if (strlen($parts[0]) == 2) {
                $prefix .= $parts[0] . '\\';
                array_shift($parts);
                $relative = FALSE;
            }
            // Eg. "C:foo.txt"
            else {
                $parts[0] = substr($parts[0], 2);
                $relative = TRUE;
            }
        }

        // Push current directory into the stack for relative paths.
        if ($relative) {
            $cwd = strtr(
                realpath($wd),
                array(DIRECTORY_SEPARATOR => '/', '\\' => '/')
            );
            // Windows disk designation.
            if (strlen($cwd) >= 2 && $cwd[1] == ':') {
                $prefix .= substr($cwd, 0, 2) . '\\';
                $cwd = (string) substr($cwd, 2);
            }
            $stack = explode('/', trim($cwd, '/'));
        }

        // Handle "/./", "/../" & "//".
        foreach ($parts as $part) {
            if ($part == '.' || $part == '')
                continue;
            if ($part == '..') {
                if (count($stack))
                    array_pop($stack);
            }
            else
                $stack[] = $part;
        }

        // Create normalized path.
        $path = $prefix . '/' . implode('/', $stack);
        if (count($stack) && $directory) {
            $path .= '/';
        }
        return $path;
    }

    /// \copydoc Countable::count().
    public function count()
    {
        return count($this->_prefixes, COUNT_RECURSIVE);
    }

    /// \copydoc IteratorAggregate::getIterator().
    public function getIterator()
    {
        return new RecursiveIteratorIterator(
            new RecursiveArrayIterator($this->_prefixes)
        );
    }

    /// \copydoc ArrayAccess::offsetGet().
    public function offsetGet($offset)
    {
        throw new Plop_Exception('Write-only collection');
    }

    /// \copydoc ArrayAccess::offsetSet().
    public function offsetSet($offset, $value)
    {
        if (!is_string($value)) {
            throw new Plop_Exception('A string was expected');
        }
        $value = self::normalizePath($value);
        $this->_prefixes[] = $value;
    }

    /// \copydoc ArrayAccess::offsetExists().
    public function offsetExists($offset)
    {
        if (!is_string($offset)) {
            throw new Plop_Exception('A string was expected');
        }
        $offset = self::normalizePath($offset);
        $key = array_search($this->_prefixes, $offset, TRUE);
        return ($key !== FALSE);
    }

    /// \copydoc ArrayAccess::offsetUnset().
    public function offsetUnset($offset)
    {
        if (!is_string($offset)) {
            throw new Plop_Exception('A string was expected');
        }
        $offset = self::normalizePath($offset);
        $key = array_search($offset, $this->_prefixes, TRUE);
        if ($key !== FALSE) {
            unset($this->_prefixes[$key]);
        }
    }

    /// \copydoc Plop_PrefixesCollectionInterface::stripLongestPrefix().
    public function stripLongestPrefix($path)
    {
        if (!is_string($path)) {
            throw new Plop_Exception('A string was expected');
        }
        $path       = self::normalizePath($path, FALSE);
        $tmpPath    = $path . "\0";

        $longestLength = 0;
        foreach ($this->_prefixes as $prefix) {
            $longestLength = max(
                $longestLength,
                self::prefixMatch(
                    $prefix . "\0",
                    $tmpPath,
                    self::PATHNAME |
                    self::PERIOD
                )
            );
        }
        return (string) substr($path, $longestLength);
    }

    /*
     * Copyright (c) 1989, 1993, 1994
     *      The Regents of the University of California.  All rights reserved.
     *
     * This code is derived from software contributed to Berkeley by
     * Guido van Rossum.
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     * 1. Redistributions of source code must retain the above copyright
     *    notice, this list of conditions and the following disclaimer.
     * 2. Redistributions in binary form must reproduce the above copyright
     *    notice, this list of conditions and the following disclaimer in the
     *    documentation and/or other materials provided with the distribution.
     * 3. All advertising materials mentioning features or use of this software
     *    must display the following acknowledgement:
     *      This product includes software developed by the University of
     *      California, Berkeley and its contributors.
     * 4. Neither the name of the University nor the names of its contributors
     *    may be used to endorse or promote products derived from this software
     *    without specific prior written permission.
     *
     * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
     * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
     * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
     * ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
     * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
     * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
     * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
     * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
     * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
     * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
     * SUCH DAMAGE.
     *
     * From FreeBSD fnmatch.c 1.11
     */
    static private function prefixMatch(
        $pattern,
        $string,
        $flags
    )
    {
        for ($ppos = 0, $spos = 0;;) {
            switch ($pattern[$ppos++]) {
                case "\0":
                    if (($flags & self::LEADING_DIR) && $string[$spos] == '/')
                        return $spos;
                    return $spos;

                case '?':
                    if ($string[$spos] == "\0")
                        return 0;
                    if ($string[$spos] == '/' && ($flags & self::PATHNAME))
                        return 0;
                    if ($string[$spos] == '.' && ($flags & self::PERIOD) &&
                        ($spos == 0 ||
                        (($flags & self::PATHNAME) && $string[$spos-1] == '/')))
                        return 0;
                    ++$spos;
                    break;

                case '*':
                    while ($pattern[$ppos] == '*')
                        $ppos++;

                    if ($string[$spos] == '.' && ($flags & self::PERIOD) &&
                        ($spos == 0 || 
                        (($flags & self::PATHNAME) && $string[$spos-1] == '/')))
                        return 0;

                    if ($pattern[$ppos] == "\0") {
                        if ($flags & self::PATHNAME)
                            return (($flags & self::LEADING_DIR) ||
                                    strpos($string, '/', $spos) === FALSE ?
                                    $spos : 0);
                        else
                            return $spos;
                    }
                    else if ($pattern[$ppos] == '/' && ($flags & self::PATHNAME)) {
                        if (($spos = strpos($string, '/', $spos)) === FALSE)
                            return 0;
                        break;
                    }

                    while (($test = $string[$spos]) !== "\0") {
                        $res = self::prefixMatch(
                            (string) substr($pattern, $ppos),
                            (string) substr($string, $spos,
                            $flags & ~self::PERIOD)
                        );
                        if ($res != 0)
                            return $spos + $res;
                        if ($test == '/' && ($flags & self::PATHNAME))
                            break;
                        ++$spos;
                    }
                    return 0;

                case '[':
                    if ($string[$spos] == "\0")
                        return 0;
                    if ($string[$spos] == '/' && ($flags & self::PATHNAME))
                        return 0;
                    $res = self::rangeMatch(
                        (string) substr($pattern, $ppos),
                        ord($string[$spos]),
                        $flags
                    );
                    if ($res == 0)
                        return 0;
                    else
                        $ppos += $res;
                    ++$spos;
                    break;

                case '\\':
                    if (!($flags & self::NOESCAPE)) {
                        if ($pattern[++$ppos] == "\0")
                            --$ppos;
                    }

                default:
                    if ($pattern[$ppos-1] == $string[$spos])
                        ;
                    // Case-folding comparison omitted as it's already
                    // being handled within self::normalizePath().
                    else if (($flags & self::PREFIX_DIRS) && $string[$spos] == "\0" &&
                        ($pattern[$ppos-1] == '/' && $spos != 0 ||
                        $spos == 1 && $string[0] == '/'))
                        return $spos;
                    else
                        return 0;
                    $spos++;
                    break;
            }
        }
        /* Never reached. */
    }

    /*
     * Copyright (c) 1989, 1993, 1994
     *      The Regents of the University of California.  All rights reserved.
     *
     * This code is derived from software contributed to Berkeley by
     * Guido van Rossum.
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     * 1. Redistributions of source code must retain the above copyright
     *    notice, this list of conditions and the following disclaimer.
     * 2. Redistributions in binary form must reproduce the above copyright
     *    notice, this list of conditions and the following disclaimer in the
     *    documentation and/or other materials provided with the distribution.
     * 3. All advertising materials mentioning features or use of this software
     *    must display the following acknowledgement:
     *      This product includes software developed by the University of
     *      California, Berkeley and its contributors.
     * 4. Neither the name of the University nor the names of its contributors
     *    may be used to endorse or promote products derived from this software
     *    without specific prior written permission.
     *
     * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
     * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
     * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
     * ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
     * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
     * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
     * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
     * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
     * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
     * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
     * SUCH DAMAGE.
     *
     * From FreeBSD fnmatch.c 1.11
     */
    static private function rangeMatch($pattern, $test, $flags)
    {
        $ppos = 0;
        if ($negate = ($pattern[0] == '!' || $pattern[0] == '^'))
            $ppos++;
        // Case-folding ignored (already handled by self::normalizePath()).

        for ($ok = 0; ($c = $pattern[$ppos++]) != ']';) {
            if ($c == '\\' && !($flags & self::NOESCAPE))
                $c = $pattern[++$ppos];
            if ($c == "\0")
                return 0;

            // Case-folding ignored (already handled by self::normalizePath()).

            if ($pattern[$ppos] == '-' &&
                ($c2 = $pattern[$ppos+1]) != "\0" && $c2 != ']') {
                $ppos += 2;
                if ($c2 == '\\' && !($flags & self::NOESCAPE))
                    $c2 = $pattern[++$ppos];
                if ($c2 == "\0")
                    return 0;

                // Case-folding ignored (already handled
                // with self::normalizePath()).

                if (ord($c) <= $test && $test <= ord($c2))
                    $ok = 1;
            }
            else if (ord($c) == $test)
                $ok = 1;
        }
        return ($ok == $negate ? 0 : $ppos);
    }
}

