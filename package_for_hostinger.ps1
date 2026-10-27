# ========================================================
# Script Otomatis Pembuat Paket ZIP Hostinger Cloud
# SMP Negeri 4 Cepu
# ========================================================
$ErrorActionPreference = "Stop"

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "[*] Memulai Pembuatan Paket ZIP Hostinger Cloud Startup" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$projectDir = $PSScriptRoot
$zipOutput = Join-Path $projectDir "hostinger_deploy.zip"
$tempDir = Join-Path $env:TEMP ("smpn4cepu_hostinger_" + (Get-Random))

# 1. Kompilasi Aset Frontend (Vite & Tailwind CSS)
Write-Host "==> [1/4] Mengompilasi aset frontend produksi (npm run build)..." -ForegroundColor Yellow
npm run build

# 2. Siapkan direktori sementara
Write-Host "==> [2/4] Menyiapkan struktur berkas produksi..." -ForegroundColor Yellow
if (Test-Path $tempDir) { Remove-Item -Path $tempDir -Recurse -Force }
New-Item -ItemType Directory -Path $tempDir | Out-Null

$includeItems = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "lang",
    "public",
    "resources",
    "routes",
    "storage",
    "vendor",
    "artisan",
    "composer.json",
    "composer.lock",
    ".env.example",
    ".htaccess",
    "deploy.sh"
)

foreach ($item in $includeItems) {
    $src = Join-Path $projectDir $item
    if (Test-Path $src) {
        $dest = Join-Path $tempDir $item
        Copy-Item -Path $src -Destination $dest -Recurse -Force
    }
}

# 3. Bersihkan log dan cache lokal dari direktori storage sementara
Write-Host "==> [3/4] Membersihkan berkas cache dan log sementara..." -ForegroundColor Yellow
$cleanPaths = @(
    (Join-Path $tempDir "storage/framework/cache/data/*"),
    (Join-Path $tempDir "storage/framework/views/*"),
    (Join-Path $tempDir "storage/logs/*")
)
foreach ($pattern in $cleanPaths) {
    if (Test-Path $pattern) {
        Remove-Item -Path $pattern -Recurse -Force -ErrorAction SilentlyContinue
    }
}

# Pastikan folder penyimpanan media bawaan terisi
$storagePublic = Join-Path $tempDir "storage/app/public"
New-Item -ItemType Directory -Path (Join-Path $storagePublic "staff") -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $storagePublic "facilities") -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $storagePublic "principal") -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $storagePublic "assets") -Force | Out-Null

if (Test-Path (Join-Path $projectDir "public/images/staff")) {
    Copy-Item -Path (Join-Path $projectDir "public/images/staff/*") -Destination (Join-Path $storagePublic "staff") -Recurse -Force -ErrorAction SilentlyContinue
}
if (Test-Path (Join-Path $projectDir "public/images/facilities")) {
    Copy-Item -Path (Join-Path $projectDir "public/images/facilities/*") -Destination (Join-Path $storagePublic "facilities") -Recurse -Force -ErrorAction SilentlyContinue
}
if (Test-Path (Join-Path $projectDir "public/images/principal")) {
    Copy-Item -Path (Join-Path $projectDir "public/images/principal/*") -Destination (Join-Path $storagePublic "principal") -Recurse -Force -ErrorAction SilentlyContinue
}
if (Test-Path (Join-Path $projectDir "public/images/assets")) {
    Copy-Item -Path (Join-Path $projectDir "public/images/assets/*") -Destination (Join-Path $storagePublic "assets") -Recurse -Force -ErrorAction SilentlyContinue
}

# 4. Buat berkas ZIP final
Write-Host "==> [4/4] Mengompresi berkas ke hostinger_deploy.zip..." -ForegroundColor Yellow
if (Test-Path $zipOutput) {
    Remove-Item -Path $zipOutput -Force
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($tempDir, $zipOutput, [System.IO.Compression.CompressionLevel]::Optimal, $false)

# Bersihkan folder sementara
Remove-Item -Path $tempDir -Recurse -Force -ErrorAction SilentlyContinue

$zipSizeMb = [math]::Round((Get-Item $zipOutput).Length / 1MB, 2)

Write-Host "==========================================================" -ForegroundColor Green
Write-Host "[OK] BERHASIL! Berkas ZIP siap diunggah ke Hostinger:" -ForegroundColor Green
Write-Host "Lokasi: $zipOutput" -ForegroundColor White
Write-Host "Ukuran: $zipSizeMb MB (Bebas node_modules dan .git)" -ForegroundColor White
Write-Host "==========================================================" -ForegroundColor Green
