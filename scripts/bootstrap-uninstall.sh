#!/usr/bin/env bash

set -euo pipefail

INSTALL_PARENT_DIR="${IREP_INSTALL_PARENT_DIR:-$HOME/Applications}"
APP_DIR_NAME="${IREP_INSTALL_DIR_NAME:-irep-local}"
KEEP_APP_DIR=0
PURGE_IMAGES=0
YES=0

usage() {
    cat <<EOF
Usage: bash bootstrap-uninstall.sh [options]

Options:
  --install-dir DIR  Parent directory that contains the app folder
  --app-dir NAME     App directory name inside the install dir
  --keep-app         Keep the downloaded app folder after removing containers
  --purge-images     Also remove installer-built images
  --yes              Skip confirmation prompt
  --help             Show this help message
EOF
}

parse_args() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            --install-dir)
                INSTALL_PARENT_DIR="$2"
                shift 2
                ;;
            --app-dir)
                APP_DIR_NAME="$2"
                shift 2
                ;;
            --keep-app)
                KEEP_APP_DIR=1
                shift
                ;;
            --purge-images)
                PURGE_IMAGES=1
                shift
                ;;
            --yes)
                YES=1
                shift
                ;;
            --help)
                usage
                exit 0
                ;;
            *)
                echo "Unknown argument: $1"
                usage
                exit 1
                ;;
        esac
    done
}

require_macos() {
    if [[ "$(uname -s)" != "Darwin" ]]; then
        echo "This bootstrap uninstall script targets macOS. On Windows use bootstrap-uninstall.ps1."
        exit 1
    fi
}

main() {
    parse_args "$@"
    require_macos

    local app_root="$INSTALL_PARENT_DIR/$APP_DIR_NAME"
    local uninstall_script="$app_root/scripts/uninstall-local.sh"

    if [[ ! -f "$uninstall_script" ]]; then
        echo "Could not find an installed app at: $app_root"
        exit 1
    fi

    cd "$app_root"

    local args=()
    (( PURGE_IMAGES == 1 )) && args+=(--purge-images)
    (( YES == 1 )) && args+=(--yes)

    bash "$uninstall_script" "${args[@]}"

    if (( KEEP_APP_DIR == 0 )); then
        rm -rf "$app_root"
        echo "Removed downloaded app directory: $app_root"
    else
        echo "Kept downloaded app directory: $app_root"
    fi
}

main "$@"