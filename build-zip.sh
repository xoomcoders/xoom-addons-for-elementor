#!/usr/bin/env bash
#
# Packages the plugin as dist/<plugin-slug>.zip - a WordPress-installable
# archive that contains exactly one plugin root folder.
#
# Normally run through `npm run build`, which compiles the SCSS first:
#   npm install && npm run build
#
# Nothing here modifies or deletes your source files: the only thing removed
# is the previous build inside dist/.
#
set -euo pipefail

cd "$(dirname "$0")"

PLUGIN_SLUG="$(basename "$PWD")"
DIST_DIR="dist"
ARCHIVE="${DIST_DIR}/${PLUGIN_SLUG}.zip"
ARCHIVER="node_modules/.bin/dir-archiver"

if [ ! -x "$ARCHIVER" ]; then
    echo "dir-archiver is not installed - run 'npm install' first." >&2
    exit 1
fi

# Never ship a stale archive.
rm -rf "$DIST_DIR"
mkdir -p "$DIST_DIR"

# Development-only paths. dir-archiver matches a plain name against the
# basename at any depth, and a name containing a slash against the exact path
# relative to the plugin root. It does not accept globs.
excludes=(
    node_modules
    dist
    tests
    test
    assets/scss
    .git
    .github
    .commandcode
    .idea
    .vscode
    .gitignore
    .gitattributes
    .editorconfig
    .DS_Store
    package.json
    package-lock.json
    npm-shrinkwrap.json
    README.md
)

# Source maps are excluded by name rather than deleted, so a running
# `npm run dev` watcher can keep writing them without them reaching the ZIP.
while IFS= read -r map_file; do
    excludes+=("$(basename "$map_file")")
done < <(find assets -type f -name '*.map' 2>/dev/null)

"$ARCHIVER" \
    --src . \
    --dest "$ARCHIVE" \
    --includebasedir true \
    --exclude "${excludes[@]}"

echo "Install-ready: ${ARCHIVE}"
