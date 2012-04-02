#!/bin/bash
#
# This command was taken from the following webpage
#
#	http://www.unix.com/unix-dummies-questions-answers/13861-search-files-dont-contain-string.html
#
/usr/bin/find -name *.js -type f -size +1c ! -exec /bin/grep -q 'jslint' {} \; -print | /bin/grep -v node
