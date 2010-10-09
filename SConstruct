# -*- coding: utf-8 -*-
# vim: set filetype=python

import glob

fh = open('src/version', 'rt')
pkg_name = 'PLOP'
pkg_email = 'clicky@erebot.net'
pkg_version = fh.read().strip()
fh.close()
del fh

def irglob(dname, pattern):
    it = glob.iglob('%s/%s' % (dname, pattern))
    while True:
        try:
            yield it.next()
        except StopIteration:
            break

    it = glob.iglob('%s/*/' % dname)
    for d in it:
        for f in irglob(d, pattern):
            yield f

component = 'PLOP'
sources = list(irglob('src/', '*.php'))

env = Environment()

for tool in (
        'xgettext',
        'msgfmt',
        'msginit',
        'msgmerge',
        'phpunit',
        'doxygen',
        'make',
        'scons',
    ):
    env[tool] = env.WhereIs(tool)

# Documentation
env.Requires('%s.tagfile' % component, 'doc')
env.Command('doc', ['Doxyfile'] + sources, [
        env.Action('$doxygen', chdir=1),
        env.Action('$make -C doc/latex', chdir=1),
    ], ENV=dict(
        PLOP_VERSION=pkg_version,
    )
)
env.SideEffect(['%s.tagfile' % component], 'doc')
env.Clean('doc', ['%s.tagfile' % component, 'doc'])

# Unit tests
env.Command('test', ['phpunit.xml'], '$phpunit')
env.AlwaysBuild('test')

