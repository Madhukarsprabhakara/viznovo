#!/usr/bin/env bash

set -euo pipefail

DEFAULT_ARCHIVE_URL="https://github.com/Madhukarsprabhakara/viznovo/archive/refs/heads/main.zip"
ARCHIVE_URL="${IREP_INSTALL_ARCHIVE_URL:-$DEFAULT_ARCHIVE_URL}"
INSTALL_PARENT_DIR="${IREP_INSTALL_PARENT_DIR:-$HOME/Applications}"
APP_DIR_NAME="${IREP_INSTALL_DIR_NAME:-irep-local}"
FORCE_DOWNLOAD=0

usage() {
    cat <<EOF
Usage: bash bootstrap-install.sh [options]

Options:
  --archive-url URL   ZIP archive URL for the application source
  --install-dir DIR   Parent directory that will contain the app folder
  --app-dir NAME      App directory name inside the install dir
  --force             Re-download and replace any existing extracted app folder
  --help              Show this help message

Environment variables:
  IREP_INSTALL_ARCHIVE_URL
  IREP_INSTALL_PARENT_DIR
  IREP_INSTALL_DIR_NAME

Example:
  bash bootstrap-install.sh \
    --archive-url https://github.com/OWNER/REPO/archive/refs/heads/main.zip
EOF
}

parse_args() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            --archive-url)
                if [[ $# -lt 2 ]]; then
                    echo "Missing value for --archive-url"
                    usage
                    exit 1
                fi
                ARCHIVE_URL="$2"
                shift 2
                ;;
            --install-dir)
                if [[ $# -lt 2 ]]; then
                    echo "Missing value for --install-dir"
                    usage
                    exit 1
                fi
                INSTALL_PARENT_DIR="$2"
                shift 2
                ;;
            --app-dir)
                if [[ $# -lt 2 ]]; then
                    echo "Missing value for --app-dir"
                    usage
                    exit 1
                fi
                APP_DIR_NAME="$2"
                shift 2
                ;;
            --force)
                FORCE_DOWNLOAD=1
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
        echo "This bootstrap script targets macOS. On Windows use bootstrap-install.ps1."
        exit 1
    fi
}

ensure_tools() {
    command -v curl >/dev/null 2>&1 || {
        echo "curl is required but was not found."
        exit 1
    }

    command -v unzip >/dev/null 2>&1 || {
        echo "unzip is required but was not found."
        exit 1
    }
}

download_and_extract() {
    local target_dir="$INSTALL_PARENT_DIR/$APP_DIR_NAME"
    local temp_dir
    local archive_path
    local extracted_root

    mkdir -p "$INSTALL_PARENT_DIR"

    if [[ -d "$target_dir" && $FORCE_DOWNLOAD -ne 1 ]]; then
        echo "Using existing app directory: $target_dir"
        APP_ROOT="$target_dir"
        return
    fi

    rm -rf "$target_dir"

    temp_dir="$(mktemp -d)"
    archive_path="$temp_dir/app.zip"

    echo "Downloading application archive..."
    curl -fL "$ARCHIVE_URL" -o "$archive_path"

    echo "Extracting application archive..."
    unzip -q "$archive_path" -d "$temp_dir/extracted"

    extracted_root="$(find "$temp_dir/extracted" -mindepth 1 -maxdepth 1 -type d | head -n 1)"

    if [[ -z "$extracted_root" ]]; then
        echo "Could not find extracted application directory."
        rm -rf "$temp_dir"
        exit 1
    fi

    mv "$extracted_root" "$target_dir"
    rm -rf "$temp_dir"

    APP_ROOT="$target_dir"
}

run_installer() {
    if [[ ! -f "$APP_ROOT/scripts/install-local.sh" ]]; then
        echo "Downloaded archive does not contain scripts/install-local.sh."
        exit 1
    fi

    echo "Starting application installer from: $APP_ROOT"
    cd "$APP_ROOT"
    bash scripts/install-local.sh
}

main() {
    parse_args "$@"
    require_macos
    ensure_tools
    download_and_extract
    run_installer
}

APP_ROOT=""
main "$@"