<?php
/**
 * publications/article_pdf.php
 *
 * This template renders the FIRST-PAGE JOURNAL FRAME (header, meta, title, authors, abstract card),
 * and then prints the "main content" (journal content) below it.
 *
 * Expected variables:
 * - $journal_name (string)
 * - $volume (string|int)
 * - $issue (string|int)
 * - $year (string|int)
 * - $received_at (string, e.g. "12 Jan 2026")
 * - $accepted_at (string)
 * - $published_at (string)
 * - $article_id (string)
 * - $doi (string)
 * - $title (string)
 * - $authors_line (string)  e.g. "Idris Bello1*, Amina Yusuf2, ..."
 * - $affiliations_html (string) HTML safe lines for affiliations
 * - $corresponding_html (string) HTML safe corresponding author + ORCID line
 * - $abstract (string)
 * - $keywords (string) e.g. "MANET, hybrid optimization, ..."
 * - $content_html (string) the complex body content (already formatted HTML)
 */

function h($v){ return esc((string)$v); }

$journal_name = $journal_name ?? 'AIRN Journal of Computing Systems';
$volume = $volume ?? '—';
$issue = $issue ?? '—';
$year = $year ?? date('Y');

$received_at = $received_at ?? '—';
$accepted_at = $accepted_at ?? '—';
$published_at = $published_at ?? '—';
$article_id = $article_id ?? '—';
$doi = $doi ?? '—';

$title = $title ?? '—';
$authors_line = $authors_line ?? '';
$affiliations_html = $affiliations_html ?? '';
$corresponding_html = $corresponding_html ?? '';
$abstract = $abstract ?? '';
$keywords = $keywords ?? '';

$content_html = $content_html ?? ''; // complex body content
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 14mm 14mm 14mm 14mm; }

    body{
      font-family: "Times New Roman", serif;
      font-size: 12pt;
      line-height: 1.45;
      color: #111;
    }

    /* Blue header bar */
    .topbar{
      background:#0b3b8c;
      color:#fff;
      padding:10px 14px;
      margin:-14mm -14mm 18px -14mm; /* stretch full width */
    }
    .topbar .row{
      width:100%;
      display:block;
      clear:both;
    }
    .topbar .left{
      float:left;
      font-weight:700;
      font-size:10.5pt;
      letter-spacing:.2px;
    }
    .topbar .right{
      float:right;
      font-size:9.5pt;
      opacity:.95;
    }

    /* Meta line under header */
    .meta{
      font-size:9.5pt;
      margin: 0 0 10px 0;
    }
    .meta b{ font-weight:700; }
    .meta .line2{ margin-top:2px; }

    /* Title block */
    .title{
      text-align:center;
      font-weight:700;
      font-size:18pt;
      margin: 10px 0 8px 0;
    }
    .authors{
      text-align:center;
      font-size:11pt;
      margin: 0 0 6px 0;
    }
    .affiliations{
      text-align:center;
      font-size:9.8pt;
      margin: 0 0 10px 0;
    }
    .affiliations .small{
      font-size:9.2pt;
      margin-top:4px;
    }

    /* Abstract “card” like screenshot */
    .abstract-card{
      border:1px solid #cdd8f0;
      background:#f7f9ff;
      padding:10px 12px;
      margin: 8px 0 14px 0;
    }
    .abstract-title{
      font-weight:700;
      font-size:10.5pt;
      margin:0 0 6px 0;
    }
    .keywords{
      margin-top:8px;
      font-size:10pt;
    }
    .keywords b{ font-weight:700; }

    /* Body content */
    .content{
      margin-top: 4px;
    }
    .content h2{
      font-size:12pt;
      font-weight:700;
      margin:14px 0 6px 0;
    }
    .content p{
      margin:0 0 8px 0;
      text-align:justify;
    }

    /* Keep tables sane */
    table{ width:100%; border-collapse:collapse; margin:10px 0; }
    th, td{ border:1px solid #111; padding:6px; vertical-align:top; }

    /* Prevent weird breaks in title/meta/abstract */
    .no-break{ page-break-inside: avoid; }
  </style>
</head>
<body>

  <div class="topbar">
    <div class="row">
      <div class="left"><?= h($journal_name) ?></div>
      <div class="right">Vol. <?= h($volume) ?> • Issue <?= h($issue) ?> • <?= h($year) ?></div>
    </div>
    <div style="clear:both;"></div>
  </div>

  <div class="meta no-break">
    <div>
      <b>Received:</b> <?= h($received_at) ?>
      &nbsp;&nbsp; <b>Accepted:</b> <?= h($accepted_at) ?>
      &nbsp;&nbsp; <b>Published:</b> <?= h($published_at) ?>
    </div>
    <div class="line2">
      <b>Article ID:</b> <?= h($article_id) ?>
      &nbsp;&nbsp; <b>DOI:</b> <?= h($doi) ?>
    </div>
  </div>

  <div class="no-break">
    <div class="title"><?= h($title) ?></div>

    <?php if ($authors_line !== ''): ?>
      <div class="authors"><?= esc($authors_line) ?></div>
    <?php endif; ?>

    <?php if ($affiliations_html !== '' || $corresponding_html !== ''): ?>
      <div class="affiliations">
        <?php if ($affiliations_html !== ''): ?>
          <div><?= $affiliations_html ?></div>
        <?php endif; ?>
        <?php if ($corresponding_html !== ''): ?>
          <div class="small"><?= $corresponding_html ?></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="abstract-card">
      <div class="abstract-title">Abstract</div>
      <div style="font-size:10.5pt; text-align:justify;"><?= nl2br(esc($abstract)) ?></div>
      <div class="keywords"><b>Keywords:</b> <?= esc($keywords) ?></div>
    </div>
  </div>

  <div class="content">
    <?= $content_html ?>
  </div>

</body>
</html>