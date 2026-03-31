param (
    [string]$file,
    [switch]$silent
)

Write-Host "======================================="
Write-Host " Beulah Verification Suite DB Installer"
Write-Host "======================================="

if (-not (Test-Path "artisan")) {
    Write-Error "Error: 'artisan' not found. Please run this script from the project root."
    exit 1
}

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Error "Error: PHP is not installed or not in your system PATH."
    exit 1
}

$command = "php artisan db:import-schema"
if ($file) {
    $command += " --file='$file'"
}
if ($silent) {
    $command += " --silent"
}

Invoke-Expression $command

if ($LASTEXITCODE -eq 0) {
    Write-Host "`nDatabase schema imported successfully!" -ForegroundColor Green
} else {
    Write-Error "`nFailed to import database schema."
    exit $LASTEXITCODE
}