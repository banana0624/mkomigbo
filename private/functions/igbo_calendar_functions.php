<?php
declare(strict_types=1);

/**
 * project-root/private/functions/igbo_calendar_functions.php
 *
 * Core helpers for the Igbo 13-month calendar (Ọnwa system).
 *
 * Model (version 2):
 * - 13 months (Ọnwa), each with 28 counted days → 364 days.
 * - 4-day market week: Eke, Orie, Afọ, Nkwọ.
 * - Each month can BEGIN on any market day column (leading blanks),
 *   so day 1 does not always sit under Eke.
 * - Month grids therefore use leading blanks and can run to 7 or 8 rows.
 *
 * Lunar handling:
 * - Backward-compatible: emoji moon symbols.
 * - Enhanced: progressive SVG moon shapes (28 distinct silhouettes),
 *   so days 11–17 don’t look alike.
 *
 * NOTE:
 * - This is still a mathematical model. We are NOT yet doing full
 *   astronomical lunar calculations. When you provide more precise
 *   traditional rules, we can refine:
 *   - month start days,
 *   - intercalary day handling,
 *   - and any variable-length months if needed.
 */

/**
 * Day names in the Igbo 4-day week.
 * Index: 0 => Eke, 1 => Orie, 2 => Afọ, 3 => Nkwọ.
 */
if (!function_exists('igbo_weekday_names')) {
  function igbo_weekday_names(): array {
    return ['Eke', 'Orie', 'Afọ', 'Nkwọ'];
  }
}

/**
 * Month definitions: 1..13.
 * Each month has:
 * - name       : primary name.
 * - alt_name   : optional dialect variant (or null).
 * - greg_hint  : rough Gregorian alignment.
 * - description: cultural notes (optional).
 */
if (!function_exists('igbo_month_definitions')) {
  function igbo_month_definitions(): array {
    return [
      1  => [
        'name'        => 'Ọnwa Mbụ',
        'alt_name'    => null,
        'greg_hint'   => 'February–March (Igbo New Year)',
        'description' => 'First month of the Igbo year, beginning with the new moon around the third week of February. In Nri tradition, the Igu Aro year-counting festival marks the new cycle; the recorded Nri calendar has passed 1000 years.',
      ],
      2  => [
        'name'        => 'Ọnwa Abụọ',
        'alt_name'    => null,
        'greg_hint'   => 'March–April',
        'description' => 'Month dedicated to cleaning, preparing homesteads, and beginning major farming work.',
      ],
      3  => [
        'name'        => 'Ọnwa Ife Eke',
        'alt_name'    => null,
        'greg_hint'   => 'April–May',
        'description' => 'Often called Ugani – a period of fasting, hunger and discipline. Communities may hold wrestling contests as a test of strength and a way of finding one’s Ikenga through struggle.',
      ],
      4  => [
        'name'        => 'Ọnwa Anọ',
        'alt_name'    => null,
        'greg_hint'   => 'May–June',
        'description' => 'Time when seed yams are planted. In many communities this is the month of the Ekeleke dance festival, emphasizing hope, endurance and trust in God through hardship.',
      ],
      5  => [
        'name'        => 'Ọnwa Agwụ',
        'alt_name'    => null,
        'greg_hint'   => 'June–July',
        'description' => 'Month of Agwụ. Adult masquerades (mmanwụ) appear and the Alusi Agwụ is specially venerated by Dibia. In many traditions this is the spiritual “start” of the ritual year.',
      ],
      6  => [
        'name'        => 'Ọnwa Ifejiọkụ',
        'alt_name'    => null,
        'greg_hint'   => 'July–August',
        'description' => 'Month of the yam deity Ifejiọkụ and Njoku Ji. Yam rituals and preparations for the New Yam festival are central.',
      ],
      7  => [
        'name'        => 'Ọnwa Alọm Chi',
        'alt_name'    => null,
        'greg_hint'   => 'August–early September',
        'description' => 'Yam harvest month and a deep period of prayer, reflection and honouring motherhood. The Alọm Chi shrine connects women to their ancestors and “first mother”; this month emphasises women, mothers and future children.',
      ],
      8  => [
        'name'        => 'Ọnwa Ilọ Mmụọ',
        'alt_name'    => 'Ọnwa Agbara',
        'greg_hint'   => 'Late September (Ọnwa Asatọ – Eighth Month)',
        'description' => 'Also known as Ọnwa Asatọ in some areas. A time of returning of spirits (mmụọ/agbara); communities may hold festivals marking spiritual presence and visitation.',
      ],
      9  => [
        'name'        => 'Ọnwa Ana',
        'alt_name'    => 'Ọnwa Ala',
        'greg_hint'   => 'October',
        'description' => 'Month of Ana/Ala, the earth goddess. Rituals and taboos concerning land, morality and community order are emphasized.',
      ],
      10 => [
        'name'        => 'Ọnwa Okike',
        'alt_name'    => null,
        'greg_hint'   => 'Early November',
        'description' => 'Month when Okike rites take place. A period that recalls creation, order and the laws that hold the world together.',
      ],
      11 => [
        'name'        => 'Ọnwa Ajana',
        'alt_name'    => 'Ọnwa Ajala',
        'greg_hint'   => 'Late November',
        'description' => 'Another phase of Okike-related rituals and community observances, preparing the ground for closing rites of the year.',
      ],
      12 => [
        'name'        => 'Ọnwa Ede Ajana',
        'alt_name'    => 'Ọnwa Ede Ajala',
        'greg_hint'   => 'Late November–December',
        'description' => 'Rituals begin to wind down. Communities move towards the end of the sacred cycle, completing obligations and offerings.',
      ],
      13 => [
        'name'        => 'Ọnwa Ụzọ Alụsị',
        'alt_name'    => 'Ọnwa Ụzọ Arushi',
        'greg_hint'   => 'January–early February',
        'description' => 'The “road of the deities” month, leading out of the old year and into the transitional period before the new moon of the next Ọnwa Mbụ appears.',
      ],
    ];
  }
}

/**
 * Display month name with dialect variant WITHOUT repeating "Ọnwa".
 * Example:
 *   name="Ọnwa Ajana", alt="Ọnwa Ajala" -> "Ọnwa Ajana/Ajala"
 *   name="Ọnwa Ana",   alt="Ọnwa Ala"   -> "Ọnwa Ana/Ala"
 */
if (!function_exists('igbo_month_display_name')) {
  function igbo_month_display_name(array $def): string {
    $name = trim((string)($def['name'] ?? ''));
    $alt  = trim((string)($def['alt_name'] ?? ''));

    if ($name === '') { return ''; }
    if ($alt === '')  { return $name; }

    // Strip leading "Ọnwa " from alt (dialect variant)
    $altStripped = preg_replace('/^\s*Ọnwa\s+/u', '', $alt);
    if (!is_string($altStripped) || $altStripped === '') {
      $altStripped = $alt;
    }
    $altStripped = trim($altStripped);

    // Join without spaces: Ajana/Ajala
    return $name . '/' . $altStripped;
  }
}

/**
 * Simple lunar stage label for a 28-day cycle.
 */
if (!function_exists('igbo_lunar_stage_for_day')) {
  function igbo_lunar_stage_for_day(int $dayInMonth): string {
    if ($dayInMonth <= 1) {
      return 'New moon';
    } elseif ($dayInMonth <= 6) {
      return 'Waxing crescent';
    } elseif ($dayInMonth === 7) {
      return 'First quarter';
    } elseif ($dayInMonth <= 13) {
      return 'Waxing gibbous';
    } elseif ($dayInMonth === 14) {
      return 'Full moon';
    } elseif ($dayInMonth <= 21) {
      return 'Waning gibbous';
    } elseif ($dayInMonth === 22) {
      return 'Last quarter';
    } elseif ($dayInMonth <= 27) {
      return 'Waning crescent';
    }
    return 'Dark / Old moon';
  }
}

/**
 * Moon icon (emoji) for visual display, based on day in month.
 * Backward-compatible, but not very granular (some days look alike).
 */
if (!function_exists('igbo_lunar_symbol_for_day')) {
  function igbo_lunar_symbol_for_day(int $dayInMonth): string {
    if ($dayInMonth <= 1) {
      return '🌑'; // New
    } elseif ($dayInMonth <= 3) {
      return '🌒';
    } elseif ($dayInMonth <= 7) {
      return '🌓';
    } elseif ($dayInMonth <= 10) {
      return '🌔';
    } elseif ($dayInMonth <= 17) {
      return '🌕';
    } elseif ($dayInMonth <= 20) {
      return '🌖';
    } elseif ($dayInMonth <= 23) {
      return '🌗';
    } elseif ($dayInMonth <= 27) {
      return '🌘';
    }
    return '🌑';
  }
}

/**
 * Progressive moon SVG (28 distinct illumination steps).
 * Uses TWO highly contrasting edge colors:
 * - Waxing: cyan outline
 * - Waning: magenta outline
 *
 * IMPORTANT: unique clipPath ids are generated to avoid SVG id collisions.
 */
if (!function_exists('igbo_moon_svg_for_day')) {
  function igbo_moon_svg_for_day(int $dayInMonth): string {
    static $seq = 0;
    $seq++;

    $d = max(1, min(28, $dayInMonth));

    $waxColor  = '#00E5FF'; // bright cyan
    $waneColor = '#FF2BD6'; // bright magenta

    $dark  = '#0B0F14';
    $light = '#F7FAFF';

    $isWaxing = ($d <= 14);

    // illumination fraction 0..1
    if ($d <= 14) {
      $f = ($d - 1) / 13;      // 1->0, 14->1
    } else {
      $f = (28 - $d) / 14;     // 15->~0.93, 28->0
    }

    // shift in [-9..9] gives distinct shapes
    $shift = (1.0 - $f) * 9.0;
    $shift = $isWaxing ? $shift : -$shift;

    $stroke = $isWaxing ? $waxColor : $waneColor;
    $title  = htmlspecialchars(igbo_lunar_stage_for_day($d), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $clipId = 'igboMoonClip_' . $seq;

    return '
<svg class="moon-svg" width="24" height="24" viewBox="0 0 24 24" role="img" aria-label="'.$title.'" xmlns="http://www.w3.org/2000/svg">
  <title>'.$title.'</title>
  <defs>
    <clipPath id="'.$clipId.'">
      <circle cx="12" cy="12" r="9.2"></circle>
    </clipPath>
  </defs>
  <circle cx="12" cy="12" r="9.2" fill="'.$dark.'" stroke="'.$stroke.'" stroke-width="1.4"></circle>
  <g clip-path="url(#'.$clipId.')">
    <circle cx="'.(12 + $shift).'" cy="12" r="9.2" fill="'.$light.'"></circle>
  </g>
</svg>';
  }
}

/**
 * Format Gregorian span for a month.
 * e.g. "Feb Thu 20 – Mar Wed 19"
 */
if (!function_exists('igbo_month_gregorian_span_label')) {
  function igbo_month_gregorian_span_label(DateTimeImmutable $start): string {
    $end = $start->modify('+27 days');
    $startLabel = $start->format('M D j');
    $endLabel   = $end->format('M D j');
    return "{$startLabel} – {$endLabel}";
  }
}

/**
 * Decide the weekday index of DAY 1 for a given month.
 * Rotating pattern for now: month 1=Eke, 2=Orie, 3=Afọ, 4=Nkwọ, repeat.
 */
if (!function_exists('igbo_month_start_weekday_index')) {
  function igbo_month_start_weekday_index(int $monthNum): int {
    return ($monthNum - 1) % 4;
  }
}

/**
 * Convert a Gregorian date into an Igbo date within a given Igbo year.
 */
if (!function_exists('igbo_from_gregorian')) {
  function igbo_from_gregorian(
    DateTimeInterface $gregDate,
    DateTimeInterface $igboYearStart,
    ?string $yearLabel = null
  ): ?array {
    $start = (new DateTimeImmutable($igboYearStart->format('Y-m-d')))->setTime(0, 0, 0);
    $date  = (new DateTimeImmutable($gregDate->format('Y-m-d')))->setTime(0, 0, 0);

    if ($date < $start) {
      return null;
    }

    $diff   = $start->diff($date);
    $offset = (int)$diff->days;

    if ($offset >= 364) {
      $weekdayIndex = $offset % 4;
      $weekdayName  = igbo_weekday_names()[$weekdayIndex];

      return [
        'year_label'    => $yearLabel,
        'day_of_year'   => 364 + ($offset - 364) + 1,
        'month'         => null,
        'day_in_month'  => null,
        'weekday_index' => $weekdayIndex,
        'weekday_name'  => $weekdayName,
        'lunar_stage'   => null,
        'lunar_symbol'  => null,
        'is_festival'   => true,
      ];
    }

    $dayOfYear   = $offset + 1;
    $monthIndex  = intdiv($offset, 28);
    $dayInMonth  = ($offset % 28) + 1;
    $monthNumber = $monthIndex + 1;

    $monthStartWeekday = igbo_month_start_weekday_index($monthNumber);
    $weekdayIndex      = ($monthStartWeekday + $dayInMonth - 1) % 4;
    $weekdayName       = igbo_weekday_names()[$weekdayIndex];

    return [
      'year_label'    => $yearLabel,
      'day_of_year'   => $dayOfYear,
      'month'         => $monthNumber,
      'day_in_month'  => $dayInMonth,
      'weekday_index' => $weekdayIndex,
      'weekday_name'  => $weekdayName,
      'lunar_stage'   => igbo_lunar_stage_for_day($dayInMonth),
      'lunar_symbol'  => igbo_lunar_symbol_for_day($dayInMonth),
      'is_festival'   => false,
    ];
  }
}

/**
 * Generate the full Igbo year structure.
 *
 * Returns months with meta + rows (4 columns).
 * For each day cell we include:
 * - lunar_symbol (emoji) for compatibility
 * - lunar_svg (distinct, nicer) for improved UI
 */
if (!function_exists('igbo_year_grid')) {
  function igbo_year_grid(DateTimeInterface $igboYearStart, ?string $yearLabel = null): array {
    $start        = (new DateTimeImmutable($igboYearStart->format('Y-m-d')))->setTime(0, 0, 0);
    $monthDefs    = igbo_month_definitions();
    $weekdayNames = igbo_weekday_names();

    $months = [];

    for ($m = 1; $m <= 13; $m++) {
      $monthStartOffset = 28 * ($m - 1);
      $monthStart       = $start->modify("+{$monthStartOffset} days");
      $monthEnd         = $monthStart->modify('+27 days');
      $startWeekdayIdx  = igbo_month_start_weekday_index($m);

      $def = $monthDefs[$m] ?? [
        'name'        => "Month {$m}",
        'alt_name'    => null,
        'greg_hint'   => '',
        'description' => '',
      ];

      $months[$m] = [
        'meta' => [
          'index'               => $m,
          'name'                => $def['name'],
          'alt_name'            => $def['alt_name'],
          'display_name'        => igbo_month_display_name($def),
          'greg_hint'           => $def['greg_hint'],
          'description'         => $def['description'],
          'start_date'          => $monthStart,
          'end_date'            => $monthEnd,
          'greg_span'           => igbo_month_gregorian_span_label($monthStart),
          'start_weekday_index' => $startWeekdayIdx,
          'start_weekday_name'  => $weekdayNames[$startWeekdayIdx],
        ],
        'rows' => [],
      ];

      $daysInMonth = 28;

      $offset   = $startWeekdayIdx;
      $numCells = $offset + $daysInMonth;
      $numRows  = (int)ceil($numCells / 4);

      $dayCounter = 0;

      for ($row = 0; $row < $numRows; $row++) {
        $rowCells = [];

        for ($col = 0; $col < 4; $col++) {
          $cellIndex = $row * 4 + $col;

          if ($cellIndex < $offset || $dayCounter >= $daysInMonth) {
            $rowCells[] = null;
            continue;
          }

          $dayCounter++;
          $dayInMonth   = $dayCounter;
          $globalOffset = $monthStartOffset + ($dayInMonth - 1);
          $dayOfYear    = $globalOffset + 1;

          $weekdayIndex = ($startWeekdayIdx + $dayInMonth - 1) % 4;
          $weekdayName  = $weekdayNames[$weekdayIndex];
          $currentDate  = $monthStart->modify('+' . ($dayInMonth - 1) . ' days');

          $rowCells[] = [
            'gregorian_date' => $currentDate,
            'year_label'     => $yearLabel,
            'day_of_year'    => $dayOfYear,
            'month'          => $m,
            'day_in_month'   => $dayInMonth,
            'weekday_index'  => $weekdayIndex,
            'weekday_name'   => $weekdayName,
            'lunar_stage'    => igbo_lunar_stage_for_day($dayInMonth),
            'lunar_symbol'   => igbo_lunar_symbol_for_day($dayInMonth),
            'lunar_svg'      => igbo_moon_svg_for_day($dayInMonth),
            'is_festival'    => false,
          ];
        }

        $months[$m]['rows'][] = $rowCells;
      }
    }

    return [
      'year_label' => $yearLabel,
      'start_date' => $start,
      'months'     => $months,
    ];
  }
}
