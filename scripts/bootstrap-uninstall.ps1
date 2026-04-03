param(
    [string]$InstallDir = $env:IREP_INSTALL_PARENT_DIR,
    [string]$AppDir = $env:IREP_INSTALL_DIR_NAME,
    [switch]$KeepApp,
    [switch]$PurgeImages,
    [switch]$Yes
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($InstallDir)) {
    $InstallDir = Join-Path $HOME 'Applications'
}

if ([string]::IsNullOrWhiteSpace($AppDir)) {
    $AppDir = 'irep-local'
}

$appRoot = Join-Path $InstallDir $AppDir
$uninstallScript = Join-Path $appRoot 'scripts/uninstall-local.ps1'
$composeFile = Join-Path $appRoot 'docker-compose.install.yml'

function Ensure-Docker {
    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw 'Docker is required to remove the installer stack.'
    }

    & docker info *> $null
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker is installed but not running. Start Docker Desktop and rerun this command.'
    }
}

function Confirm-Uninstall {
    if ($Yes) {
        return
    }

    $reply = Read-Host 'This will remove the local installer stack, database volume, and generated installer env file. Continue? [y/N]'
    if ($reply -notmatch '^[Yy]$') {
        Write-Host 'Uninstall cancelled.'
        exit 0
    }
}

if (-not (Test-Path $appRoot)) {
    throw "Could not find an installed app at: $appRoot"
}

if (Test-Path $uninstallScript) {
    $args = @()
    if ($PurgeImages) {
        $args += '-PurgeImages'
    }
    if ($Yes) {
        $args += '-Yes'
    }

    & powershell -ExecutionPolicy Bypass -File $uninstallScript @args
} elseif (Test-Path $composeFile) {
    Write-Host 'Installed app does not contain the new uninstall helper script. Falling back to direct stack teardown.'

    Ensure-Docker
    Confirm-Uninstall

    $downArgs = @('compose', '-p', 'irep-install', '-f', $composeFile, 'down', '-v', '--remove-orphans')
    if ($PurgeImages) {
        $downArgs += @('--rmi', 'local')
    }

    & docker @downArgs

    $envFile = Join-Path $appRoot 'src/.env.install'
    if (Test-Path $envFile) {
        Remove-Item -Force $envFile
    }
} else {
    throw "Could not find uninstall metadata in: $appRoot"
}

if (-not $KeepApp -and (Test-Path $appRoot)) {
    Remove-Item -Recurse -Force $appRoot
    Write-Host "Removed downloaded app directory: $appRoot"
} else {
    Write-Host "Kept downloaded app directory: $appRoot"
}