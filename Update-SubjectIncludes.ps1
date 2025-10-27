<#  
    Script: Update-SubjectIncludes.ps1  
    Purpose: Bulk update PHP subject-page files to use new include paths for subject header/footer.  
#>

param (
    [string]$ProjectRoot = "F:\xampp\htdocs\mkomigbo\project-root",
    [string]$PagesDir   = "public\staff\subjects\pgs",
    [string]$OldHeaderPattern = "__DIR__\s*\.\s*'/\.\./\.\./shared/subjects_header.php'",
    [string]$NewHeader     = "PRIVATE_PATH . '/shared/subjects/subjects_header_' . h(`$subject_slug) . '.php';",
    [string]$OldFooterPattern = "__DIR__\s*\.\s*'/\.\./\.\./shared/subjects_footer.php'",
    [string]$NewFooter     = "PRIVATE_PATH . '/shared/subjects/subjects_footer_' . h(`$subject_slug) . '.php';"
)

# Combine full path
$scanPath = Join-Path $ProjectRoot $PagesDir
Write-Host "Scanning directory: $scanPath"

# Get all PHP files in directory and subdirectories
Get-ChildItem -Path $scanPath -Filter *.php -Recurse | ForEach-Object {
    $filePath = $_.FullName
    Write-Host "Processing file: $filePath"

    # Read the file content as single string
    $content = Get-Content -Path $filePath -Raw

    # Perform replacements
    $newContent = $content -replace $OldHeaderPattern, $NewHeader
    $newContent = $newContent -replace $OldFooterPattern, $NewFooter

    if ($newContent -ne $content) {
        Write-Host "  → Updating include paths in file."
        # Write back modified content
        $newContent | Set-Content -Path $filePath -Encoding UTF8
    } else {
        Write-Host "  → No changes needed."
    }
}

Write-Host "Done scanning & updating includes."
