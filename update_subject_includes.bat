@echo off
SETLOCAL EnableDelayedExpansion

:: === Configuration – adjust these paths if needed ===
set "ROOT_DIR=F:\xampp\htdocs\mkomigbo\project-root"
set "OLD_PATH=__DIR__ . '/../../shared/subjects_header.php'"
set "NEW_PATH=require_once PRIVATE_PATH . '/shared/subjects/subjects_header_%%slug%%.php'"
:: NOTE: %%slug%% placeholder should be dynamically replaced, but we will prompt.

:: or easier, we look for generic require_once of old header/footer and replace with new pattern
set "SEARCH_HEADER=require_once __DIR__ . '/../../shared/subjects_header.php';"
set "REPLACE_HEADER=require_once PRIVATE_PATH . '/shared/subjects/subjects_header_' . h($subject_slug) . '.php';"

set "SEARCH_FOOTER=require_once __DIR__ . '/../../shared/subjects_footer.php';"
set "REPLACE_FOOTER=require_once PRIVATE_PATH . '/shared/subjects/subjects_footer_' . h($subject_slug) . '.php';"

:: Directory to scan
set "SCAN_DIR=%ROOT_DIR%\public\staff\subjects\pgs"

echo Scanning files in %SCAN_DIR% …
for /R "%SCAN_DIR%" %%F in (*.php) do (
    echo Processing %%F …
    set "file=%%F"
    set "content="
    set "modified=0"

    :: Read file into variable, simple approach
    for /f "usebackq delims=" %%L in ("%%F") do (
        set "line=%%L"
        if "!line!"=="%SEARCH_HEADER%" (
            set "line=%REPLACE_HEADER%"
            set "modified=1"
        )
        if "!line!"=="%SEARCH_FOOTER%" (
            set "line=%REPLACE_FOOTER%"
            set "modified=1"
        )
        echo(!line!>> "%%F.tmp"
    )

    if "!modified!"=="1" (
        echo Updated includes in %%F
        del "%%F"
        rename "%%F.tmp" %%~nxF
    ) else (
        del "%%F.tmp"
    )
)

echo Done. Please review changed files, test thoroughly before committing.
PAUSE
