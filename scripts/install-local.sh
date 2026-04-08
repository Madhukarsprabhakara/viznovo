#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE_FILE="$ROOT_DIR/docker-compose.install.yml"
ENV_TEMPLATE="$ROOT_DIR/src/.env.install.example"
ENV_FILE="$ROOT_DIR/src/.env.install"
APP_URL="http://localhost:8088/register"
PROJECT_NAME="irep-install"

compose() {
    docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" "$@"
}

require_macos() {
    if [[ "$(uname -s)" != "Darwin" ]]; then
        echo "This installer script targets macOS. On Windows use scripts/install-local.ps1."
        exit 1
    fi
}

wait_for_docker() {
    local attempts=0

    until docker info >/dev/null 2>&1; do
        attempts=$((attempts + 1))

        if (( attempts > 60 )); then
            echo "Docker is installed but the daemon is not ready. Start Docker Desktop, then rerun this command."
            exit 1
        fi

        sleep 2
    done
}

ensure_docker() {
    if command -v docker >/dev/null 2>&1; then
        open -a Docker >/dev/null 2>&1 || true
        wait_for_docker
        return
    fi

    if command -v brew >/dev/null 2>&1; then
        echo "Docker not found. Installing Docker Desktop with Homebrew..."
        brew install --cask docker
        open -a Docker
        wait_for_docker
        return
    fi

    echo "Docker Desktop is required. Opening the download page."
    open "https://www.docker.com/products/docker-desktop/"
    echo "Install Docker Desktop, launch it once, then rerun: bash scripts/install-local.sh"
    exit 1
}

ensure_compose() {
    docker compose version >/dev/null 2>&1 || {
        echo "Docker Compose v2 is required and was not found."
        exit 1
    }
}

ensure_env_file() {
    local app_key
    local db_password
    local reverb_secret

    app_key="base64:$(openssl rand -base64 32 | tr -d '\r\n')"
    db_password="$(openssl rand -hex 16)"
    reverb_secret="$(openssl rand -hex 16)"

    if [[ -f "$ENV_FILE" ]]; then
        if grep -Eq '^APP_KEY=$|^APP_KEY=__APP_KEY__$' "$ENV_FILE"; then
            sed -i.bak -E "s|^APP_KEY=$|APP_KEY=$app_key|; s|^APP_KEY=__APP_KEY__$|APP_KEY=$app_key|" "$ENV_FILE"
        fi

        if grep -Eq '^DB_PASSWORD=__DB_PASSWORD__$' "$ENV_FILE"; then
            sed -i.bak -E "s|^DB_PASSWORD=__DB_PASSWORD__$|DB_PASSWORD=$db_password|" "$ENV_FILE"
        fi

        if grep -Eq '^REVERB_APP_SECRET=__REVERB_APP_SECRET__$' "$ENV_FILE"; then
            sed -i.bak -E "s|^REVERB_APP_SECRET=__REVERB_APP_SECRET__$|REVERB_APP_SECRET=$reverb_secret|" "$ENV_FILE"
        fi

        rm -f "$ENV_FILE.bak"

        return
    fi

    sed \
        -e "s|__APP_KEY__|$app_key|g" \
        -e "s|__DB_PASSWORD__|$db_password|g" \
        -e "s|__REVERB_APP_SECRET__|$reverb_secret|g" \
        "$ENV_TEMPLATE" > "$ENV_FILE"
}

wait_for_database() {
    local attempts=0

    until compose exec -T irep_install_db pg_isready -U postgres -d irep_install >/dev/null 2>&1; do
        attempts=$((attempts + 1))

        if (( attempts > 60 )); then
            echo "Database did not become ready in time."
            exit 1
        fi

        sleep 2
    done
}

synchronize_database_password() {
    compose exec -T irep_install_db sh -lc "psql -v ON_ERROR_STOP=1 -U postgres -d postgres -c \"ALTER USER postgres WITH PASSWORD '$IREP_INSTALL_DB_PASSWORD';\"" >/dev/null
}

run_cli() {
    compose run --rm irep_install_cli "$1"
}

run_composer_install() {
    run_cli "php artisan migrate --force"
}

open_browser() {
    open "$APP_URL"
}

main() {
    require_macos
    ensure_docker
    ensure_compose
    ensure_env_file

    export IREP_INSTALL_DB_PASSWORD
    IREP_INSTALL_DB_PASSWORD="$(grep '^DB_PASSWORD=' "$ENV_FILE" | cut -d '=' -f 2-)"

    compose build irep_install_php
    compose build irep_install_nginx
    compose up -d irep_install_db irep_install_php
    wait_for_database
    synchronize_database_password

    run_composer_install
    run_cli "php artisan db:seed --class='Database\\Seeders\\AIModelsSeeder' --force"
    run_cli "php artisan db:seed --class='Database\\Seeders\\CsvDataTypeSeeder' --force"
    run_cli "php artisan storage:link || true"
    run_cli "php artisan config:clear && php artisan route:clear && php artisan view:clear"

    compose up -d irep_install_nginx irep_install_reverb irep_install_supervisord

    open_browser

    echo "Installer stack is running."
    echo "App: $APP_URL"
    echo "API keys: http://localhost:8088/apikeys"
}

main "$@"