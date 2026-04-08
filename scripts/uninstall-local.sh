#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE_FILE="$ROOT_DIR/docker-compose.install.yml"
ENV_FILE="$ROOT_DIR/src/.env.install"
PUBLIC_STORAGE_LINK="$ROOT_DIR/src/public/storage"
PROJECT_NAME="irep-install"
REMOVE_ENV_FILE=1
PURGE_IMAGES=0
YES=0

usage() {
    cat <<EOF
Usage: bash uninstall-local.sh [options]

Options:
  --keep-env       Keep src/.env.install after uninstall
  --purge-images   Also remove installer-built images
  --yes            Skip confirmation prompt
  --help           Show this help message
EOF
}

parse_args() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            --keep-env)
                REMOVE_ENV_FILE=0
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

compose() {
    docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" "$@"
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

    printf 'This will remove the local installer stack, installer volumes, and generated installer env file. Continue? [y/N] '
    read -r reply

    if [[ ! "$reply" =~ ^[Yy]$ ]]; then
        echo "Uninstall cancelled."
        exit 0
    fi
}

tear_down_stack() {
    if (( PURGE_IMAGES == 1 )); then
        compose down -v --remove-orphans --rmi local
    else
        compose down -v --remove-orphans
    fi
}

remove_generated_env() {
    if (( REMOVE_ENV_FILE == 1 )) && [[ -f "$ENV_FILE" ]]; then
        rm -f "$ENV_FILE"
    fi
}

remove_generated_symlink() {
    if [[ -L "$PUBLIC_STORAGE_LINK" ]]; then
        rm -f "$PUBLIC_STORAGE_LINK"
        return
    fi

    if [[ -d "$PUBLIC_STORAGE_LINK" ]] && [[ -z "$(ls -A "$PUBLIC_STORAGE_LINK")" ]]; then
        rmdir "$PUBLIC_STORAGE_LINK" 2>/dev/null || true
    fi
}

main() {
    parse_args "$@"
    ensure_docker
    confirm
    tear_down_stack
    remove_generated_env
    remove_generated_symlink
    echo "Installer deployment removed from this checkout."
}

main "$@"