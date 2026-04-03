param(
    [switch]$KeepEnv,
    [switch]$PurgeImages,
    [switch]$Yes
)

$ErrorActionPreference = 'Stop'

$RootDir = Split-Path -Parent $PSScriptRoot
$ComposeFile = Join-Path $RootDir 'docker-compose.install.yml'
$EnvFile = Join-Path $RootDir 'src/.env.install'
$ProjectName = 'irep-install'

function Invoke-Compose {
    param(
        [Parameter(ValueFromRemainingArguments = $true)]
        [string[]]$Args
    )

    & docker compose -p $ProjectName -f $ComposeFile @Args
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

    $reply = Read-Host 'This will remove the local installer stack, database volume, and generated installer env file. Continue? [y/N]'
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

Invoke-Compose @downArgs

if (-not $KeepEnv -and (Test-Path $EnvFile)) {
    Remove-Item -Force $EnvFile
}

Write-Host 'Installer deployment removed from this checkout.'