# generate-public-subject-pages.ps1
# --------------------------------
# Regenerates ALL staff subject hub files:
#   public/staff/subjects/<slug>/index.php
# Overwrites existing index.php with a clean, consistent template.
# Uses a hard-coded list of the 19 subjects.

param()

# Resolve project root to the folder where this script lives
$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
Write-Host "Project root: $projectRoot" -ForegroundColor Cyan

# List of your 19 subjects (name + slug)
$subjects = @(
    @{ Name = 'History';          Slug = 'history'       }
    @{ Name = 'Slavery';          Slug = 'slavery'       }
    @{ Name = 'People';           Slug = 'people'        }
    @{ Name = 'Persons';          Slug = 'persons'       }
    @{ Name = 'Culture';          Slug = 'culture'       }
    @{ Name = 'Religion';         Slug = 'religion'      }
    @{ Name = 'Spirituality';     Slug = 'spirituality'  }
    @{ Name = 'Tradition';        Slug = 'tradition'     }
    @{ Name = 'Language 1';       Slug = 'language1'     }
    @{ Name = 'Language 2';       Slug = 'language2'     }
    @{ Name = 'Struggles';        Slug = 'struggles'     }
    @{ Name = 'Biafra';           Slug = 'biafra'        }
    @{ Name = 'Nigeria';          Slug = 'nigeria'       }
    @{ Name = 'IPOB';             Slug = 'ipob'          }
    @{ Name = 'Africa';           Slug = 'africa'        }
    @{ Name = 'United Kingdom';   Slug = 'uk'            }
    @{ Name = 'Europe';           Slug = 'europe'        }
    @{ Name = 'Arabs';            Slug = 'arabs'         }
    @{ Name = 'About';            Slug = 'about'         }
)

# PHP template for the staff subject hub index.php.
# __SLUG__ will be replaced per subject.
$template = @'
<?php
// project-root/public/staff/subjects/__SLUG__/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) {
  die('Init not found at: ' . $init);
}
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = '__SLUG__';
$subject_name = function_exists('subject_human_name')
  ? subject_human_name($subject_slug)
  : ucfirst(str_replace('-', ' ', $subject_slug));

require PRIVATE_PATH . '/common/staff_subjects/hub.php';
?><!---- hub-wrapper-ok ---->
'@

# Loop through the 19 subjects and (re)write index.php
foreach ($s in $subjects) {
    $slug = $s.Slug
    $name = $s.Name

    $subjectDir = Join-Path $projectRoot "public\staff\subjects\$slug"
    $indexPath  = Join-Path $subjectDir "index.php"

    if (-not (Test-Path $subjectDir)) {
        Write-Host "Creating directory: $subjectDir" -ForegroundColor Yellow
        New-Item -ItemType Directory -Path $subjectDir | Out-Null
    } else {
        Write-Host "Directory already exists: $subjectDir" -ForegroundColor DarkGray
    }

    Write-Host "Writing index.php for [$name] at: $indexPath" -ForegroundColor Green
    $content = $template -replace '__SLUG__', $slug
    Set-Content -Path $indexPath -Value $content -Encoding UTF8
}

Write-Host "Done generating staff subject pages (all index.php overwritten)." -ForegroundColor Cyan
