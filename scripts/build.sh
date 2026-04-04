#!/usr/bin/env bash

set -e

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_ZIP="$PLUGIN_DIR/dist/local_devtools.zip"
TEMP_DIR="$PLUGIN_DIR/temp/build"

cd "$PLUGIN_DIR"

echo "Creating $OUTPUT_ZIP..."

rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR/devtools"
mkdir -p "$(dirname "$OUTPUT_ZIP")"

cp -r classes db demo lang version.php vendor README.md "$TEMP_DIR/devtools/"

cd "$TEMP_DIR"
zip -r "$OUTPUT_ZIP" .

rm -rf "$TEMP_DIR"

echo "Done: $OUTPUT_ZIP"
