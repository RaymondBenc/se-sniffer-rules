#!/usr/bin/env bash

CURRENT_VERSION=$(composer config version)
IFS=. components=(${CURRENT_VERSION##*-})
MAJOR_VERSION=$((components[0]))
MINOR_VERSION=$((components[1]+1))
NEW_VERSION="$MAJOR_VERSION.$MINOR_VERSION"

echo "Incrementing version to: $NEW_VERSION"

composer config version "$NEW_VERSION"
