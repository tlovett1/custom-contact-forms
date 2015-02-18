#!/usr/bin/env bash

echo "Are you sure you want to do this? Uncommitted work could be lost. [y/n]"
read input_variable

if [ "$input_variable" == "y" ]; then
    DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

    cd "$DIR/../"

    rm bin/install-wp-tests.sh > /dev/null 2>&1

    rm -rf bower_components > /dev/null 2>&1
    rm -rf node_modules > /dev/null 2>&1
    rm -rf scss > /dev/null 2>&1
    rm -rf tests > /dev/null 2>&1
    rm -rf .sass-cache > /dev/null 2>&1
    rm -rf vendor/wp-api/wp-api/.git > /dev/null 2>&1

    rm .travis.yml > /dev/null 2>&1
    rm Gruntfile.js > /dev/null 2>&1
    rm Dockunit.json > /dev/null 2>&1
    rm phpunit.xml > /dev/null 2>&1
    rm .jshintrc > /dev/null 2>&1

    bower install --production > /dev/null 2>&1

    echo "Done! Custom Contact Forms is cleaned up and production ready."
fi
