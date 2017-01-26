#!/usr/bin/env bash

# Check to make sure that our build environment is right.
test -n "$TRAVIS" || { echo "This script is only designed to be run on Travis."; exit 1; }
test "${TRAVIS_BRANCH}" == "master" || { echo "Skipping build, we only work with the master branch"; exit 0; }
test "${TRAVIS_PHP_VERSION:0:3}" == "5.6" || { echo "Skipping for PHP $TRAVIS_PHP_VERSION -- only update for PHP 5.6 build."; exit 0; }
test -n "$GITHUB_TOKEN" || { echo "GITHUB_TOKEN environment variable must be set to run this script."; exit 1; }

# Create work env
rm -rf tmp/
mkdir tmp/
cd tmp/

# Clone it
git clone https://$GITHUB_TOKEN@github.com/RaymondBenc/socialengine-console.git .
git config --global user.email $GITHUB_USER_EMAIL
git config --global user.name $GITHUB_USER_NAME
git status

# Create a new version
CURRENT_VERSION=$(composer config version)
IFS=. components=(${CURRENT_VERSION##*-})
MAJOR_VERSION=$((components[0]))
MINOR_VERSION=$((components[1]+1))
NEW_VERSION="$MAJOR_VERSION.$MINOR_VERSION"

echo "Incrementing version to: $NEW_VERSION"

# Cleanup
cd ../
rm -rf tmp/

# composer config version "$NEW_VERSION"
