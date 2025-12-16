<?php
declare(strict_types=1);

/**
 * project-root/public/platforms/igbo-calendar.php
 * Public Igbo Calendar viewer (13 months, 4 market days).
 */

/* 1) Bootstrap */
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>Init not found at: {$init}</h1>";
  exit;
}
require_once $init;

/* 2) Calendar helpers */
$calFns = dirname(__DIR__, 2) . '/private/functions/igbo_calendar_functions.php';
if (!is_file($calFns)) {
  echo "<h1>Calendar functions missing at: {$calFns}</h1>";
  exit;
}
require_once $calFns;

// Timezone: prefer env APP_TZ, else PHP default, else UTC
$tzName = (string)($_ENV['APP_TZ'] ?? ini_get('date.timezone') ?? 'UTC');
if ($tzName === '') { $tzName = 'UTC'; }
try {
  $tz = new DateTimeZone($tzName);
} catch (Throwable $e) {
  $tz = new DateTimeZone('UTC');
}

// TODAY (fix: always live, not hardcoded)
$today = (new DateTimeImmutable('now', $tz))->setTime(0, 0, 0);
$todayYmd = $today->format('Y-m-d');

// Year start: default = Feb 18 (3rd week) for current Igbo year
$year = (int)$today->format('Y');
$defaultStart = new DateTimeImmutable(sprintf('%04d-02-18', $year), $tz);
if ($today < $defaultStart) {
  $defaultStart = new DateTimeImmutable(sprintf('%04d-02-18', $year - 1), $tz);
}

// Allow override via ?start=YYYY-MM-DD
$startParam = isset($_GET['start']) ? trim((string)$_GET['start']) : '';
if ($startParam !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startParam)) {
  try {
    $defaultStart = (new DateTimeImmutable($startParam, $tz))->setTime(0, 0, 0);
  } catch (Throwable $e) {
    // keep default
  }
}

$yearLabel = 'Igbo Year';

// Build grid
$grid = igbo_year_grid($defaultStart, $yearLabel);
$weekdays = igbo_weekday_names();

// Compute Igbo date for TODAY (for header display)
$todayIgbo = igbo_from_gregorian($today, $defaultStart, $yearLabel);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Igbo Lunar Calendar — Mkomigbo</title>

  <style>
    :root{
      --bg: #0b0f14;
      --card: #111826;
      --card2:#0f1722;
      --text:#eaf0ff;
      --muted:#a9b6d3;
      --line: rgba(255,255,255,.10);

      /* HIGH contrast lunar accents (your request) */
      --wax: #00E5FF;   /* bright cyan */
      --wane:#FF2BD6;   /* bright magenta */
      --today:#FFD400;  /* bright yellow highlight */
    }

    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: radial-gradient(1000px 600px at 20% 0%, #142035 0%, var(--bg) 55%);
      color: var(--text);
    }
    .wrap{
      max-width: 1200px;
      margin: 0 auto;
      padding: 18px 14px 40px;
    }

    .topbar{
      display:flex;
      gap:12px;
      align-items:flex-end;
      justify-content:space-between;
      flex-wrap:wrap;
      padding: 12px 0 16px;
      border-bottom: 1px solid var(--line);
      margin-bottom: 16px;
    }
    .title{
      display:flex;
      flex-direction:column;
      gap:6px;
    }
    h1{
      font-size: 1.25rem;
      margin:0;
      letter-spacing:.2px;
    }
    .subtitle{
      color: var(--muted);
      font-size: .95rem;
      line-height:1.35;
    }

    .pill{
      display:inline-flex;
      gap:10px;
      align-items:center;
      padding: 10px 12px;
      border:1px solid var(--line);
      border-radius: 999px;
      background: rgba(255,255,255,.04);
      color: var(--text);
      font-size: .92rem;
      white-space:nowrap;
    }
    .pill b{ color: var(--today); }

    .legend{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin: 14px 0 20px;
      color: var(--muted);
      font-size: .92rem;
    }
    .dot{
      width:10px;height:10px;border-radius:99px;display:inline-block;margin-right:6px;
    }
    .dot.wax{ background: var(--wax); }
    .dot.wane{ background: var(--wane); }
    .dot.today{ background: var(--today); }

    .months{
      display:grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
    }
    @media (max-width: 980px){
      .months{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 620px){
      .months{ grid-template-columns: 1fr; }
    }

    .month{
      background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
      border:1px solid var(--line);
      border-radius: 16px;
      overflow:hidden;
    }
    .month-h{
      padding: 14px 14px 10px;
      background: rgba(0,0,0,.18);
      border-bottom: 1px solid var(--line);
    }
    .month-name{
      margin:0;
      font-size: 1.1rem;
      letter-spacing:.2px;
    }
    .month-sub{
      margin-top:6px;
      color: var(--muted);
      font-size: .92rem;
      display:flex;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }

    table{
      width:100%;
      border-collapse:collapse;
      table-layout:fixed;
    }
    th{
      text-align:center;
      font-weight:600;
      font-size:.9rem;
      padding:10px 6px;
      border-bottom: 1px solid var(--line);
      color: var(--text);
      background: rgba(255,255,255,.03);
    }

    /* Market day subtle identity */
    th:nth-child(1){ box-shadow: inset 0 -2px 0 rgba(255,165,0,.45); } /* Eke */
    th:nth-child(2){ box-shadow: inset 0 -2px 0 rgba(0,140,255,.45); } /* Orie */
    th:nth-child(3){ box-shadow: inset 0 -2px 0 rgba(0,200,140,.45); } /* Afọ */
    th:nth-child(4){ box-shadow: inset 0 -2px 0 rgba(180,120,255,.45); } /* Nkwọ */

    td{
      border-bottom: 1px solid rgba(255,255,255,.06);
      border-right: 1px solid rgba(255,255,255,.06);
      height: 64px;
      vertical-align:top;
      padding: 8px 8px 6px;
      background: rgba(0,0,0,.10);
    }
    tr td:last-child{ border-right:none; }

    .blank{
      background: rgba(0,0,0,.06);
    }

    .day{
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:4px;
      text-align:center;
    }
    .ig-day{
      font-weight:800;
      font-size: 1.05rem;
      line-height:1;
    }
    .greg{
      color: var(--muted);
      font-size: .78rem;
      line-height:1.1;
    }

    .moon-svg{
      width: 24px;
      height: 24px;
      display:block;
    }

    /* today highlight (stronger than before) */
    td.today{
      outline: 2px solid rgba(255, 212, 0, .85);
      outline-offset: -2px;
      box-shadow: 0 0 0 2px rgba(255, 212, 0, .18) inset, 0 10px 30px rgba(255, 212, 0, .10);
      background: rgba(255, 212, 0, .06);
    }

    .phase-chip{
      font-size:.74rem;
      padding: 2px 7px;
      border-radius: 999px;
      border:1px solid rgba(255,255,255,.10);
      color: var(--muted);
      background: rgba(255,255,255,.04);
    }
  </style>
</head>

<body>
<div class="wrap">
  <div class="topbar">
    <div class="title">
      <h1>Igbo Lunar Calendar (Ọnwa) — 13 Months</h1>
      <div class="subtitle">
        Year start (model): <b><?= htmlspecialchars($grid['start_date']->format('Y-m-d'), ENT_QUOTES) ?></b>
        <?php if ($todayIgbo && empty($todayIgbo['is_festival'])): ?>
          · Today: <b><?= htmlspecialchars($todayIgbo['weekday_name'], ENT_QUOTES) ?></b>
          <b><?= (int)$todayIgbo['day_in_month'] ?></b>
          (<?= htmlspecialchars($todayYmd, ENT_QUOTES) ?>)
        <?php else: ?>
          · Today: <?= htmlspecialchars($todayYmd, ENT_QUOTES) ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="pill">
      <span><b>Waxing</b> cyan</span>
      <span>·</span>
      <span><b>Waning</b> magenta</span>
      <span>·</span>
      <span>Timezone: <?= htmlspecialchars($tzName, ENT_QUOTES) ?></span>
    </div>
  </div>

  <div class="legend">
    <span><span class="dot wax"></span>Waxing side</span>
    <span><span class="dot wane"></span>Waning side</span>
    <span><span class="dot today"></span>Today</span>
    <span>· Hint: hover/tap moon for phase label</span>
  </div>

  <section class="months">
    <?php foreach ($grid['months'] as $m => $month): ?>
      <?php $meta = $month['meta']; ?>
      <div class="month">
        <div class="month-h">
          <h3 class="month-name"><?= htmlspecialchars($meta['display_name'] ?: $meta['name'], ENT_QUOTES) ?></h3>
          <div class="month-sub">
            <span><?= htmlspecialchars($meta['greg_hint'], ENT_QUOTES) ?></span>
            <span><?= htmlspecialchars($meta['greg_span'], ENT_QUOTES) ?></span>
          </div>
        </div>

        <table>
          <thead>
            <tr>
              <?php foreach ($weekdays as $w): ?>
                <th><?= htmlspecialchars($w, ENT_QUOTES) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($month['rows'] as $row): ?>
              <tr>
                <?php foreach ($row as $cell): ?>
                  <?php if ($cell === null): ?>
                    <td class="blank"></td>
                  <?php else: ?>
                    <?php
                      /** @var DateTimeImmutable $g */
                      $g = $cell['gregorian_date'];
                      $ymd = $g->format('Y-m-d');
                      $isToday = ($ymd === $todayYmd);
                    ?>
                    <td class="<?= $isToday ? 'today' : '' ?>">
                      <div class="day">
                        <div class="ig-day"><?= (int)$cell['day_in_month'] ?></div>

                        <!-- Distinct moon SVG per day -->
                        <?= $cell['lunar_svg'] ?>

                        <div class="phase-chip"><?= htmlspecialchars((string)$cell['lunar_stage'], ENT_QUOTES) ?></div>
                        <div class="greg"><?= htmlspecialchars($g->format('Y-m-d'), ENT_QUOTES) ?></div>
                      </div>
                    </td>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </section>

</div>
</body>
</html>