# generate-subject-overviews.ps1
# Creates/overwrites overview.html for all 19 subjects
# using the canonical subject list from:
# project-root/private/registry/subjects_register.php

# 1) Project root (ADJUST if your path is different)
$projectRoot = "F:\xampp\htdocs\mkomigbo\project-root"

# 2) Base folder where subject overview files live
#    This matches: project-root/private/content/subjects/<slug>/overview.html
#    If your structure is different, change this ONE line:
$outputRoot = Join-Path $projectRoot "private\content\subjects"

# 3) Subjects list (mirrors subjects_register.php: id, name, slug)
$subjects = @(
    @{ Id = 1;  Name = "History";      Slug = "history"      },
    @{ Id = 2;  Name = "Slavery";      Slug = "slavery"      },
    @{ Id = 3;  Name = "People";       Slug = "people"       },
    @{ Id = 4;  Name = "Persons";      Slug = "persons"      },
    @{ Id = 5;  Name = "Culture";      Slug = "culture"      },
    @{ Id = 6;  Name = "Religion";     Slug = "religion"     },
    @{ Id = 7;  Name = "Spirituality"; Slug = "spirituality" },
    @{ Id = 8;  Name = "Tradition";    Slug = "tradition"    },
    @{ Id = 9;  Name = "Language1";    Slug = "language1"    },
    @{ Id = 10; Name = "Language2";    Slug = "language2"    },
    @{ Id = 11; Name = "Struggles";    Slug = "struggles"    },
    @{ Id = 12; Name = "Biafra";       Slug = "biafra"       },
    @{ Id = 13; Name = "Nigeria";      Slug = "nigeria"      },
    @{ Id = 14; Name = "IPOB";         Slug = "ipob"         },
    @{ Id = 15; Name = "Africa";       Slug = "africa"       },
    @{ Id = 16; Name = "UK";           Slug = "uk"           },
    @{ Id = 17; Name = "Europe";       Slug = "europe"       },
    @{ Id = 18; Name = "Arabs";        Slug = "arabs"        },
    @{ Id = 19; Name = "About";        Slug = "about"        }
)

# 4) Generic HTML template for the overview BODY
#    All [SUBJECT_NAME] placeholders will be replaced per subject.
$template = @'
<!-- Overview body template for [SUBJECT_NAME] -->
<section class="subject-section subject-section-intro">
  <h2>What this section is about</h2>
  <p>
    Write 3–5 lines here to introduce <strong>[SUBJECT_NAME]</strong> in the
    context of Mkomigbo: why it matters, the time period, and what kind of
    stories or evidence the reader will find.
  </p>
</section>

<section class="subject-section subject-section-themes">
  <h2>Key themes</h2>
  <ul>
    <li>Theme 1 for [SUBJECT_NAME] &mdash; short explanation.</li>
    <li>Theme 2 for [SUBJECT_NAME] &mdash; short explanation.</li>
    <li>Theme 3 for [SUBJECT_NAME] &mdash; short explanation.</li>
  </ul>
</section>

<section class="subject-section subject-section-structure">
  <h2>How this subject is organised</h2>
  <p>
    Summarise how articles under <strong>[SUBJECT_NAME]</strong> are grouped
    (periods, places, people, events, etc.). Mention how to move between pages.
  </p>
  <ol>
    <li>Group or chapter 1 &mdash; what it focuses on.</li>
    <li>Group or chapter 2 &mdash; what it focuses on.</li>
    <li>Group or chapter 3 &mdash; what it focuses on.</li>
  </ol>
</section>

<section class="subject-section subject-section-examples">
  <h2>Examples you might see here</h2>
  <p>Replace this with concrete examples that belong to [SUBJECT_NAME].</p>
  <ul>
    <li>Example 1 (event, person, place, source).</li>
    <li>Example 2.</li>
    <li>Example 3.</li>
  </ul>
</section>

<section class="subject-section subject-section-how-to-read">
  <h2>How to read and use this section</h2>
  <p>
    Explain how you want the visitor to use this part of the site:
    start with the overview, then move to specific topics, compare pages,
    follow links to related subjects, etc.
  </p>
</section>
'@

Write-Host "=== Generating subject overview templates ==="
Write-Host "Project root : $projectRoot"
Write-Host "Output root  : $outputRoot"
Write-Host ""

# 5) Loop through all subjects and generate overview.html
foreach ($subject in $subjects) {
    $name = $subject.Name
    $slug = $subject.Slug

    # Folder: private/content/subjects/<slug>/
    $subjectDir = Join-Path $outputRoot $slug

    if (-not (Test-Path -LiteralPath $subjectDir)) {
        New-Item -ItemType Directory -Path $subjectDir | Out-Null
        Write-Host "Created folder: $subjectDir"
    }

    # Replace [SUBJECT_NAME] with the human-friendly name
    $bodyHtml = $template -replace "\[SUBJECT_NAME\]", $name

    # File: overview.html (same name for every subject)
    $filePath = Join-Path $subjectDir "overview.html"

    # Overwrite existing file to maintain uniformity
    Set-Content -Path $filePath -Value $bodyHtml -Encoding UTF8

    Write-Host ("  -> {0}\overview.html (for {1})" -f $slug, $name)
}

Write-Host ""
Write-Host "Done. All 19 subject overview.html files have been created/overwritten."
