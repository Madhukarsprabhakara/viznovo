$ErrorActionPreference = 'Stop'

$RootDir = Split-Path -Parent $PSScriptRoot
$ComposeFile = Join-Path $RootDir 'docker-compose.install.yml'
$EnvTemplate = Join-Path $RootDir 'src/.env.install.example'
$EnvFile = Join-Path $RootDir 'src/.env.install'
$AppUrl = 'http://localhost:8088/register'
$ProjectName = 'irep-install'

function Invoke-Compose {
    param(
        [Parameter(ValueFromRemainingArguments = $true)]
        [string[]]$Args
    )

    & docker compose -p $ProjectName -f $ComposeFile @Args
}

function Wait-ForDocker {
    for ($i = 0; $i -lt 60; $i++) {
        if ((& docker info 2>$null) -ne $null) {
            return
        }

        Start-Sleep -Seconds 2
    }

    throw 'Docker is installed but the daemon is not ready. Start Docker Desktop, then rerun this command.'
}

function Ensure-Docker {
    if (Get-Command docker -ErrorAction SilentlyContinue) {
        $dockerDesktop = Join-Path $Env:ProgramFiles 'Docker\Docker\Docker Desktop.exe'

        if (Test-Path $dockerDesktop) {
            Start-Process $dockerDesktop | Out-Null
        }

        Wait-ForDocker
        return
    }

    if (Get-Command winget -ErrorAction SilentlyContinue) {
        Write-Host 'Docker not found. Installing Docker Desktop with winget...'
        & winget install -e --id Docker.DockerDesktop --accept-source-agreements --accept-package-agreements

        $dockerDesktop = Join-Path $Env:ProgramFiles 'Docker\Docker\Docker Desktop.exe'
        if (Test-Path $dockerDesktop) {
            Start-Process $dockerDesktop | Out-Null
        }

        Wait-ForDocker
        return
    }

    Start-Process 'https://www.docker.com/products/docker-desktop/'
    throw 'Docker Desktop is required. Install it, launch it once, then rerun: powershell -ExecutionPolicy Bypass -File .\scripts\install-local.ps1'
}

function Ensure-Compose {
    & docker compose version *> $null
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker Compose v2 is required and was not found.'
    }
}

function New-RandomHex([int]$ByteCount) {
    $bytes = New-Object byte[] $ByteCount
    [System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
    return [Convert]::ToHexString($bytes).ToLowerInvariant()
}

function Ensure-EnvFile {
    if (Test-Path $EnvFile) {
        return
    }

    $content = Get-Content $EnvTemplate -Raw
    $content = $content.Replace('__DB_PASSWORD__', (New-RandomHex 16))
    $content = $content.Replace('__REVERB_APP_SECRET__', (New-RandomHex 16))
    Set-Content -Path $EnvFile -Value $content -NoNewline
}

function Prepare-HostDirs {
    $paths = @(
        'src/storage/app/private',
        'src/storage/framework/cache',
        'src/storage/framework/sessions',
        'src/storage/framework/views',
        'src/bootstrap/cache'
    )

    foreach ($path in $paths) {
        New-Item -ItemType Directory -Force -Path (Join-Path $RootDir $path) | Out-Null
    }
}

function Wait-ForDatabase {
    for ($i = 0; $i -lt 60; $i++) {
        Invoke-Compose exec -T irep_install_db pg_isready -U postgres -d irep_install *> $null
        if ($LASTEXITCODE -eq 0) {
            return
        }

        Start-Sleep -Seconds 2
    }

    throw 'Database did not become ready in time.'
}

function Invoke-Cli([string]$Command) {
    Invoke-Compose run --rm irep_install_cli $Command
}

Ensure-Docker
Ensure-Compose
Ensure-EnvFile
Prepare-HostDirs

$dbPasswordLine = (Get-Content $EnvFile | Where-Object { $_ -like 'DB_PASSWORD=*' } | Select-Object -First 1)
$Env:IREP_INSTALL_DB_PASSWORD = $dbPasswordLine.Substring('DB_PASSWORD='.Length)

Invoke-Compose up -d --build irep_install_db irep_install_php
Wait-ForDatabase

Invoke-Cli 'composer install --no-interaction --prefer-dist'
Invoke-Cli 'npm ci'
Invoke-Cli 'php artisan key:generate --force'
Invoke-Cli 'php artisan migrate --force'
Invoke-Cli "php artisan db:seed --class='Database\Seeders\AIModelsSeeder' --force"
Invoke-Cli "php artisan db:seed --class='Database\Seeders\CsvDataTypeSeeder' --force"
Invoke-Cli 'php artisan storage:link || true'
Invoke-Cli 'php artisan config:clear && php artisan route:clear && php artisan view:clear'
Invoke-Cli 'npm run build'

Invoke-Compose up -d --build irep_install_nginx irep_install_reverb irep_install_supervisord

Start-Process $AppUrl

Write-Host 'Installer stack is running.'
Write-Host "App: $AppUrl"
Write-Host 'API keys: http://localhost:8088/apikeys'