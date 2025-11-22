# open-mariadb.ps1
# ---------------------------------
# Open an interactive MariaDB shell
# ---------------------------------
$ErrorActionPreference = "Stop"

# Primary client path (MariaDB 11.8)
$dbClient = "F:\xampp\MariaDB 11.8\New Folder\bin\mariadb.exe"
if (-not (Test-Path $dbClient)) {
    # Fallback: XAMPP MySQL client
    $fallback = "F:\xampp\mysql\bin\mysql.exe"
    if (Test-Path $fallback) {
        $dbClient = $fallback
    }
}

$dbHost = "127.0.0.1"
$dbPort = 3307          # <--- confirmed by netstat
$dbUser = "uzoma"       # <--- your DB user

if (-not (Test-Path $dbClient)) {
    Write-Error "Could not find MariaDB/MySQL client. Checked:
    - F:\xampp\MariaDB 11.8\New Folder\bin\mariadb.exe
    - F:\xampp\mysql\bin\mysql.exe"
    exit 1
}

# Read password securely
$securePwd = Read-Host "Enter password" -AsSecureString
$ptr    = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePwd)
$dbPass = [Runtime.InteropServices.Marshal]::PtrToStringUni($ptr)

Write-Host "Connecting to $dbHost`:$dbPort as $dbUser using $dbClient ...`n"

& $dbClient `
  "-h" $dbHost `
  "-P" $dbPort `
  "-u" $dbUser `
  "-p$dbPass"
