# Viznovo open-source installation instructions

This repository now includes a separate local installer stack for open-source users. 

## Bootstrap install without Git

Users do not need Git if you publish a ZIP archive URL for the repository.

macOS bootstrap command:

```bash
bash <(curl -fsSL https://raw.githubusercontent.com/Madhukarsprabhakara/viznovo/main/scripts/bootstrap-install.sh)
```

Windows PowerShell bootstrap command:

```powershell
powershell -ExecutionPolicy Bypass -Command "& ([scriptblock]::Create((Invoke-RestMethod 'https://raw.githubusercontent.com/Madhukarsprabhakara/viznovo/main/scripts/bootstrap-install.ps1')))"
```

The bootstrap script downloads the project ZIP, extracts it into a local app folder, and then runs the local installer.

## Bootstrap uninstall without Git

macOS bootstrap uninstall command:

```bash
bash <(curl -fsSL https://raw.githubusercontent.com/Madhukarsprabhakara/viznovo/main/scripts/bootstrap-uninstall.sh)
```

Windows PowerShell bootstrap uninstall command:

```powershell
powershell -ExecutionPolicy Bypass -Command "& ([scriptblock]::Create((Invoke-RestMethod 'https://raw.githubusercontent.com/Madhukarsprabhakara/viznovo/main/scripts/bootstrap-uninstall.ps1')))"
```

These commands stop the installer stack, remove the installer volumes, remove `src/.env.install`, and delete the downloaded app folder in the default bootstrap location.

## What it does

- Checks whether Docker is installed.
- Can download the project automatically from a ZIP archive so Git is not required.
- Attempts to install Docker Desktop on macOS with Homebrew or on Windows with `winget` when possible.
- Falls back to opening the Docker Desktop download page when automated install is not available.
- Builds a separate installer-focused Docker image set with the app code and assets baked in.
- Generates a dedicated `src/.env.install` file from `src/.env.install.example`.
- Installs PHP dependencies and frontend assets during the installer image build.
- Generates the Laravel app key, runs migrations, seeds AI model and CSV data type reference tables, builds frontend assets, starts queue workers and Reverb, and opens the app in your browser.

## Repository-based commands

macOS:

```bash
bash scripts/install-local.sh
```

Windows PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\install-local.ps1
```

These commands are for users who already have the project on disk.

## Repository-based uninstall commands

macOS:

```bash
bash scripts/uninstall-local.sh
```

Windows PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\uninstall-local.ps1
```

These commands remove the installer stack from an existing checkout but leave the repository folder in place.

## URLs

- App: `http://localhost:8088/register`
- API keys: `http://localhost:8088/apikeys`
- Reverb: `http://localhost:8081`
- Postgres: `localhost:5434`

## First run

1. Run the install command for your OS.
2. Sign up your first user.
3. Add your API keys on the API keys page.
4. Create a project and start uploading CSVs, PDFs, or website URLs.

<!-- ## Notes

- The installer uses `src/.env.install` and leaves your existing `src/.env` alone.
- The installer stack is packaged for runtime use and is not intended to replace the live-edit development workflow in `docker-compose-dev.yml`.
- Local installer mode bypasses email verification so first-time users can sign up immediately.
- The existing development stack remains unchanged.
- The bootstrap scripts default to the GitHub ZIP archive for `Madhukarsprabhakara/viznovo` on the `main` branch. If you change the default branch later, update the bootstrap URLs.
- The uninstall scripts only target the install stack defined in `docker-compose.install.yml`; they do not remove or alter your existing development stack. -->

# O'Saasy License Agreement

Copyright © 2026, Madhukar Shedthikere Prabhakara.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

1. The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
2. No licensee or downstream recipient may use the Software (including any modified or derivative versions) to directly compete with the original Licensor by offering it to third parties as a hosted, managed, or Software-as-a-Service (SaaS) product or cloud service where the primary value of the service is the functionality of the Software itself.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
