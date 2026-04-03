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

ensure_docker() {
    command -v docker >/dev/null 2>&1 || {
        echo "Docker is required to remove the installer stack."
        exit 1
    }

    docker info >/dev/null 2>&1 || {
        echo "Docker is installed but not running. Start Docker Desktop and rerun this command."
        exit 1
    }
}

confirm() {
    if (( YES == 1 )); then
        return
    fi

    printf 'This will remove the local installer stack, database volume, and generated installer env file. Continue? [y/N] '
    read -r reply

    if [[ ! "$reply" =~ ^[Yy]$ ]]; then
        echo "Uninstall cancelled."
        exit 0
    fi
}

fallback_uninstall() {
    local app_root="$1"
    local compose_file="$app_root/docker-compose.install.yml"
    local env_file="$app_root/src/.env.install"
    local down_args=(compose -p irep-install -f "$compose_file" down -v --remove-orphans)

    (( PURGE_IMAGES == 1 )) && down_args+=(--rmi local)

    ensure_docker
    confirm

    docker "${down_args[@]}"

    if [[ -f "$env_file" ]]; then
        rm -f "$env_file"
    fi
}

main() {
    parse_args "$@"
    require_macos

    local app_root="$INSTALL_PARENT_DIR/$APP_DIR_NAME"
    local uninstall_script="$app_root/scripts/uninstall-local.sh"
    local compose_file="$app_root/docker-compose.install.yml"

    if [[ ! -d "$app_root" ]]; then
        echo "Could not find an installed app at: $app_root"
        exit 1
    fi

    if [[ -f "$uninstall_script" ]]; then
        cd "$app_root"

        if (( PURGE_IMAGES == 1 && YES == 1 )); then
            bash "$uninstall_script" --purge-images --yes
        elif (( PURGE_IMAGES == 1 )); then
            bash "$uninstall_script" --purge-images
        elif (( YES == 1 )); then
            bash "$uninstall_script" --yes
        else
            bash "$uninstall_script"
        fi
    elif [[ -f "$compose_file" ]]; then
        echo "Installed app does not contain the new uninstall helper script. Falling back to direct stack teardown."
        fallback_uninstall "$app_root"
    else
        echo "Could not find uninstall metadata in: $app_root"
        exit 1
    fi

    if (( KEEP_APP_DIR == 0 )); then
        rm -rf "$app_root"
        echo "Removed downloaded app directory: $app_root"
    else
        echo "Kept downloaded app directory: $app_root"
    fi
}

main "$@"