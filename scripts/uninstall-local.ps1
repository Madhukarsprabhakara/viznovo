param(
    [switch]$KeepEnv,
    [switch]$PurgeImages,
    [switch]$Yes
)

$ErrorActionPreference = 'Stop'

$RootDir = Split-Path -Parent $PSScriptRoot
$ComposeFile = Join-Path $RootDir 'docker-compose.install.yml'
$EnvFile = Join-Path $RootDir 'src/.env.install'
$PublicStorageLink = Join-Path $RootDir 'src/public/storage'
$ProjectName = 'irep-install'

function Invoke-Compose {
    param(
        [string[]]$ComposeArgs
    )

    & docker compose -p $ProjectName -f $ComposeFile @ComposeArgs
}

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

    $reply = Read-Host 'This will remove the local installer stack, installer volumes, and generated installer env file. Continue? [y/N]'
    if ($reply -notmatch '^[Yy]$') {
        Write-Host 'Uninstall cancelled.'
        exit 0
    }
}

Ensure-Docker
Confirm-Uninstall

$downArgs = @('down', '-v', '--remove-orphans')
if ($PurgeImages) {
    $downArgs += @('--rmi', 'local')
}

Invoke-Compose $downArgs

if (-not $KeepEnv -and (Test-Path $EnvFile)) {
    Remove-Item -Force $EnvFile
}

if (Test-Path $PublicStorageLink) {
    $item = Get-Item $PublicStorageLink -Force

    if ($item.LinkType) {
        Remove-Item -Force $PublicStorageLink
    }
    elseif ($item.PSIsContainer -and -not (Get-ChildItem $PublicStorageLink -Force | Select-Object -First 1)) {
        Remove-Item -Force $PublicStorageLink
    }
}

Write-Host 'Installer deployment removed from this checkout.'