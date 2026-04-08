$ErrorActionPreference = 'Stop'

$RootDir = Split-Path -Parent $PSScriptRoot
$ComposeFile = Join-Path $RootDir 'docker-compose.install.yml'
$EnvTemplate = Join-Path $RootDir 'src/.env.install.example'
$EnvFile = Join-Path $RootDir 'src/.env.install'
$AppUrl = 'http://localhost:8088/register'
$ProjectName = 'irep-install'

function Invoke-Compose {
    param(
        [string[]]$ComposeArgs
    )

    & docker compose -p $ProjectName -f $ComposeFile @ComposeArgs
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
    $random = [System.Security.Cryptography.RandomNumberGenerator]::Create()

    try {
        $random.GetBytes($bytes)
    }
    finally {
        $random.Dispose()
    }

    return ([System.BitConverter]::ToString($bytes).Replace('-', '')).ToLowerInvariant()
}

function Ensure-EnvFile {
    $appKeyBytes = New-Object byte[] 32
    $random = [System.Security.Cryptography.RandomNumberGenerator]::Create()

    try {
        $random.GetBytes($appKeyBytes)
    }
    finally {
        $random.Dispose()
    }

    $appKey = 'base64:' + [Convert]::ToBase64String($appKeyBytes)
    $dbPassword = New-RandomHex 16
    $reverbSecret = New-RandomHex 16

    if (Test-Path $EnvFile) {
        $content = Get-Content $EnvFile -Raw
        if ($content -match '(?m)^APP_KEY=$|^APP_KEY=__APP_KEY__$') {
            $content = [regex]::Replace($content, '(?m)^APP_KEY=$', "APP_KEY=$appKey")
            $content = [regex]::Replace($content, '(?m)^APP_KEY=__APP_KEY__$', "APP_KEY=$appKey")
        }

        $content = [regex]::Replace($content, '(?m)^DB_PASSWORD=__DB_PASSWORD__$', "DB_PASSWORD=$dbPassword")
        $content = [regex]::Replace($content, '(?m)^REVERB_APP_SECRET=__REVERB_APP_SECRET__$', "REVERB_APP_SECRET=$reverbSecret")
        Set-Content -Path $EnvFile -Value $content -NoNewline

        return
    }

    $content = Get-Content $EnvTemplate -Raw
    $content = $content.Replace('__APP_KEY__', $appKey)
    $content = $content.Replace('__DB_PASSWORD__', $dbPassword)
    $content = $content.Replace('__REVERB_APP_SECRET__', $reverbSecret)
    Set-Content -Path $EnvFile -Value $content -NoNewline
}

function Wait-ForDatabase {
    for ($i = 0; $i -lt 60; $i++) {
        Invoke-Compose @('exec', '-T', 'irep_install_db', 'pg_isready', '-U', 'postgres', '-d', 'irep_install') *> $null
        if ($LASTEXITCODE -eq 0) {
            return
        }

        Start-Sleep -Seconds 2
    }

    throw 'Database did not become ready in time.'
}

function Sync-DatabasePassword {
    $sqlPassword = $Env:IREP_INSTALL_DB_PASSWORD.Replace("'", "''")
    $alterUserSql = "ALTER USER postgres WITH PASSWORD '$sqlPassword';"

    Invoke-Compose @('exec', '-T', 'irep_install_db', 'psql', '-v', 'ON_ERROR_STOP=1', '-U', 'postgres', '-d', 'postgres', '-c', $alterUserSql) *> $null
}

function Invoke-Cli([string]$Command) {
    Invoke-Compose @('run', '--rm', 'irep_install_cli', '/bin/sh', '-lc', $Command)
}

function Invoke-ComposerInstall {
    Invoke-Cli 'php artisan migrate --force'
}

Ensure-Docker
Ensure-Compose
Ensure-EnvFile

$dbPasswordLine = (Get-Content $EnvFile | Where-Object { $_ -like 'DB_PASSWORD=*' } | Select-Object -First 1)
$Env:IREP_INSTALL_DB_PASSWORD = $dbPasswordLine.Substring('DB_PASSWORD='.Length)

Invoke-Compose @('build', 'irep_install_php')
Invoke-Compose @('build', 'irep_install_nginx')
Invoke-Compose @('up', '-d', 'irep_install_db', 'irep_install_php')
Wait-ForDatabase
Sync-DatabasePassword

Invoke-ComposerInstall
Invoke-Cli "php artisan db:seed --class='Database\Seeders\AIModelsSeeder' --force"
Invoke-Cli "php artisan db:seed --class='Database\Seeders\CsvDataTypeSeeder' --force"
Invoke-Cli 'php artisan config:clear && php artisan route:clear && php artisan view:clear'

Invoke-Compose @('up', '-d', 'irep_install_nginx', 'irep_install_reverb', 'irep_install_supervisord')

Start-Process $AppUrl

Write-Host 'Installer stack is running.'
Write-Host "App: $AppUrl"
Write-Host 'API keys: http://localhost:8088/apikeys'