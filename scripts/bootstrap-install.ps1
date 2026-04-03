param(
    [string]$ArchiveUrl = $env:IREP_INSTALL_ARCHIVE_URL,
    [string]$InstallDir = $env:IREP_INSTALL_PARENT_DIR,
    [string]$AppDir = $env:IREP_INSTALL_DIR_NAME,
    [switch]$Force
)

$ErrorActionPreference = 'Stop'

$DefaultArchiveUrl = 'https://github.com/Madhukarsprabhakara/viznovo/archive/refs/heads/main.zip'

if ([string]::IsNullOrWhiteSpace($ArchiveUrl)) {
    $ArchiveUrl = $DefaultArchiveUrl
}

if ([string]::IsNullOrWhiteSpace($InstallDir)) {
    $InstallDir = Join-Path $HOME 'Applications'
}

if ([string]::IsNullOrWhiteSpace($AppDir)) {
    $AppDir = 'irep-local'
}

$targetDir = Join-Path $InstallDir $AppDir

if ((Test-Path $targetDir) -and -not $Force) {
    Write-Host "Using existing app directory: $targetDir"
} else {
    if (Test-Path $targetDir) {
        Remove-Item -Recurse -Force $targetDir
    }

    New-Item -ItemType Directory -Force -Path $InstallDir | Out-Null

    $tempDir = Join-Path ([System.IO.Path]::GetTempPath()) ([System.Guid]::NewGuid().ToString())
    $archivePath = Join-Path $tempDir 'app.zip'
    $extractDir = Join-Path $tempDir 'extracted'

    New-Item -ItemType Directory -Force -Path $tempDir | Out-Null
    New-Item -ItemType Directory -Force -Path $extractDir | Out-Null

    Write-Host 'Downloading application archive...'
    Invoke-WebRequest -Uri $ArchiveUrl -OutFile $archivePath

    Write-Host 'Extracting application archive...'
    Expand-Archive -Path $archivePath -DestinationPath $extractDir -Force

    $extractedRoot = Get-ChildItem -Path $extractDir -Directory | Select-Object -First 1

    if ($null -eq $extractedRoot) {
        throw 'Could not find extracted application directory.'
    }

    Move-Item -Path $extractedRoot.FullName -Destination $targetDir
    Remove-Item -Recurse -Force $tempDir
}

$installer = Join-Path $targetDir 'scripts/install-local.ps1'

if (-not (Test-Path $installer)) {
    throw 'Downloaded archive does not contain scripts/install-local.ps1.'
}

Write-Host "Starting application installer from: $targetDir"
& powershell -ExecutionPolicy Bypass -File $installer