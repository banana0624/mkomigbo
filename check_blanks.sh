#!/bin/bash
## project-root/check_blanks.sh

echo "🔍 Checking for empty files..."
find ./ -type f -empty

echo "🔍 Checking for tiny files (<10 bytes)..."
find ./ -type f -size -10c