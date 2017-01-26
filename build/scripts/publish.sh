#!/usr/bin/env bash

# Check to make sure that our build environment is right.
test -n "$TRAVIS" || { echo "This script is only designed to be run on Travis."; exit 1; }
test -n "$GITHUB_TOKEN" || { echo "GITHUB_TOKEN environment variable must be set to run this script."; exit 1; }
test "${TRAVIS_BRANCH}" == "master" || { echo "Skipping build, we only work with the master branch"; exit 0; }
test "${TRAVIS_PHP_VERSION:0:3}" == "5.6" || { echo "Skipping for PHP $TRAVIS_PHP_VERSION -- only update for PHP 5.6 build."; exit 0; }
test "${TRAVIS_PULL_REQUEST}" == false || { echo "Skipping pull request from building."; exit 0; }

# Create work env
rm -rf tmp/
mkdir tmp/
cd tmp/

# Branch env
RELEASE_BRANCH="release/$NEW_VERSION"
MASTER="master"

# Clone it
git clone https://$GITHUB_TOKEN@github.com/RaymondBenc/socialengine-console.git .
git config --global user.email $GITHUB_USER_EMAIL
git config --global user.name $GITHUB_USER_NAME
git checkout -b $RELEASE_BRANCH origin/$MASTER

# Create a new version
CURRENT_VERSION=$(composer config version)
IFS=. components=(${CURRENT_VERSION##*-})
MAJOR_VERSION=$((components[0]))
MINOR_VERSION=$((components[1]+1))
NEW_VERSION="$MAJOR_VERSION.$MINOR_VERSION"

# Update composer.json
echo "Incrementing version to: $NEW_VERSION"
composer config version "$NEW_VERSION"

# Add new version and merge
git add --all
git commit -m "Incrementing version to $NEW_VERSION [$TRAVIS_BUILD_NUMBER]"
git checkout $MASTER
git merge --no-edit --no-ff $RELEASE_BRANCH
git tag v$NEW_VERSION -m "Autobuild [$NEW_VERSION][$TRAVIS_BUILD_NUMBER]" $MASTER

# Push to github
git push origin $MASTER
git push origin $RELEASE_BRANCH
git push origin refs/tags/v$NEW_VERSION

# Cleanup
cd ../
rm -rf tmp/
