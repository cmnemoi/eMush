#!/bin/bash

TITLE="$CI_MERGE_REQUEST_TITLE"
REGEX="^(feat|fix|docs|style|refactor|test|chore|ci|internal|revert)(\(.+\))?: .+$"

if [[ ! "$TITLE" =~ $REGEX ]]; then
    echo -e "ERROR: Merge request title does not follow Conventional Commits format (https://www.conventionalcommits.org/en/v1.0.0/).\n"
    echo -e "Your change won't appear in the changelog!\n"
    echo -e "Please follow the format: <type>(<scope>): <description> and relaunch the pipeline to fix this."
    echo -e "Example - fix: Other player skills are ordered by selection date"
    exit 1
fi

echo "Merge request title is valid."
exit 0
