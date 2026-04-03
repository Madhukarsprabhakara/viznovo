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

if (-not (Test-Path $uninstallScript)) {
    throw "Could not find an installed app at: $appRoot"
}

$args = @()
if ($PurgeImages) {
    $args += '-PurgeImages'
}
if ($Yes) {
    $args += '-Yes'
}

& powershell -ExecutionPolicy Bypass -File $uninstallScript @args

if (-not $KeepApp -and (Test-Path $appRoot)) {
    Remove-Item -Recurse -Force $appRoot
    Write-Host "Removed downloaded app directory: $appRoot"
} else {
    Write-Host "Kept downloaded app directory: $appRoot"
}