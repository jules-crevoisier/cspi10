# Cree deploy-restore.tar.gz - archive unique pour Dockploy (/backup).
# Usage:
#   .\scripts\pack-deploy-backup.ps1

param(
    [string]$DatabaseFile = "database\data\cspi.db",
    [string]$UploadsTar = "uploads-backup.tar.gz",
    [string]$Output = "deploy-restore.tar.gz"
)

$ErrorActionPreference = "Stop"
$root = Split-Path $PSScriptRoot -Parent

function Resolve-ProjectPath([string]$RelativePath) {
    return Join-Path $root ($RelativePath -replace '/', '\')
}

$dbPath = Resolve-ProjectPath $DatabaseFile
$uploadsPath = Resolve-ProjectPath $UploadsTar
$outputPath = Resolve-ProjectPath $Output

if (-not (Test-Path $dbPath)) {
    Write-Error "Base introuvable: $dbPath"
}

$staging = Join-Path ([System.IO.Path]::GetTempPath()) ("cspi-deploy-pack-" + [guid]::NewGuid().ToString("n"))
New-Item -ItemType Directory -Path $staging | Out-Null

try {
    Copy-Item $dbPath (Join-Path $staging "cspi.db")

    $uploadsDir = Join-Path $staging "uploads"
    New-Item -ItemType Directory -Path $uploadsDir | Out-Null

    if (Test-Path $uploadsPath) {
        $extractTemp = Join-Path $staging "_extract"
        New-Item -ItemType Directory -Path $extractTemp | Out-Null
        tar -xzf $uploadsPath -C $extractTemp

        $nestedPublic = Join-Path $extractTemp "public\uploads"
        $nestedUploads = Join-Path $extractTemp "uploads"

        if (Test-Path $nestedPublic) {
            Copy-Item -Path (Join-Path $nestedPublic "*") -Destination $uploadsDir -Recurse -Force
        }
        elseif (Test-Path $nestedUploads) {
            Copy-Item -Path (Join-Path $nestedUploads "*") -Destination $uploadsDir -Recurse -Force
        }
        else {
            Copy-Item -Path (Join-Path $extractTemp "*") -Destination $uploadsDir -Recurse -Force
        }
    }
    else {
        Write-Warning "Archive uploads absente - pack base seule."
    }

    if (Test-Path $outputPath) {
        Remove-Item $outputPath -Force
    }

    Push-Location $staging
    tar -czf $outputPath cspi.db uploads
    Pop-Location

    $sizeMb = [math]::Round((Get-Item $outputPath).Length / 1MB, 2)
    Write-Host ""
    Write-Host "Archive prete: $outputPath ($sizeMb MB)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Etapes Dockploy:" -ForegroundColor Cyan
    Write-Host "  1. scp deploy-restore.tar.gz root@SERVEUR:/var/dockploy/cspi10/backup/"
    Write-Host "  2. Volume: /var/dockploy/cspi10/backup -> /backup"
    Write-Host "  3. Redeployer (restauration auto)"
    Write-Host "  4. Retirer volume /backup apres succes"
}
finally {
    if (Test-Path $staging) {
        Remove-Item $staging -Recurse -Force
    }
}
