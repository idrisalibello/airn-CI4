<?php

/**
 * publications/article_pdf.php
 *
 * FRONT-MATTER ONLY (single leading page):
 * - Blue journal header bar (left: journal name, right: Vol • Issue • Year)
 * - Meta line (Received / Accepted / Published + Article ID + DOI)
 * - Centered bold title
 * - Authors line (as provided)
 * - Abstract card + Keywords
 *
 * NOTE: The manuscript body is appended as camera-ready PDF during publishing.
 *
 * Expected variables:
 * - $journal_name (string)
 * - $volume (string|int)
 * - $issue (string|int)
 * - $year (string|int)
 * - $received_at (string, e.g. "2026-02-25")
 * - $accepted_at (string)
 * - $published_at (string)
 * - $article_id (string)
 * - $doi (string)
 * - $title (string)
 * - $authors_line (string)
 * - $abstract (string)
 * - $keywords (string)
 */

function h($v)
{
  return esc((string)$v);
}

$journal_name = $journal_name ?? 'AIRN Journal';
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
$abstract = $abstract ?? '';
$keywords = $keywords ?? '';
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <style>
    @page {
      margin: 14mm 14mm 14mm 14mm;
    }

    body {
      font-family: "Times New Roman", serif;
      font-size: 12pt;
      line-height: 1.45;
      color: #111;
    }

    /* Blue header bar */
    .topbar {
      background: #0b3b8c;
      color: #fff;
      padding: 10px 14px;
      margin: -14mm -14mm 16px -14mm;
      /* stretch full width */
    }

    .topbar .left {
      float: left;
      font-weight: 700;
      font-size: 10.5pt;
      letter-spacing: .2px;
    }

    .topbar .right {
      float: right;
      font-size: 9.5pt;
      opacity: .95;
    }

    /* Meta line under header */
    .meta {
      font-size: 9.5pt;
      margin: 0 0 10px 0;
    }

    .meta b {
      font-weight: 700;
    }

    .meta .line2 {
      margin-top: 2px;
    }

    /* Title block */
    .title {
      text-align: center;
      font-weight: 700;
      font-size: 18pt;
      margin: 10px 0 8px 0;
    }

    .authors {
      text-align: center;
      font-size: 11pt;
      margin: 0 0 10px 0;
    }

    /* Ensure the whole page uses full width (no accidental column constraint) */
    .page {
      width: 100%;
      box-sizing: border-box;
    }

    /* If you have a wrapper like .container/.wrap, force it to full width */
    .container,
    .wrap,
    .content {
      width: 100% !important;
      max-width: none !important;
      margin: 0 !important;
      padding: 0;
      box-sizing: border-box;
    }

    /* Abstract card should span full page width (inside margins) */
    .abstract-card {
      width: 100% !important;
      max-width: none !important;
      box-sizing: border-box;
      padding: 12px 14px;
      border: 1px solid #d9e2ef;
      background: #f6f9ff;
      border-radius: 8px;
      margin-top: 10px;
    }

    /* Abstract text must justify */
    .abstract-card .abstract-text {
      text-align: justify;
      text-justify: inter-word;
      line-height: 1.55;
      margin: 0;
    }

    /* Defensive: kill floats that can force narrow columns */
    .abstract-card,
    .abstract-card * {
      float: none !important;
    }

    .keywords {
      margin-top: 8px;
      font-size: 10pt;
    }

    .keywords b {
      font-weight: 700;
    }

    .no-break {
      page-break-inside: avoid;
    }

    .clearfix {
      clear: both;
      height: 0;
    }
  </style>
</head>

<body>

  <div class="topbar">
    <div class="left"><?= h($journal_name) ?></div>
    <div class="right">Vol. <?= h($volume) ?> • Issue <?= h($issue) ?> • <?= h($year) ?></div>
    <div class="clearfix"></div>
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

    <?php if (trim((string)$authors_line) !== ''): ?>
      <div class="authors"><?= esc($authors_line) ?></div>
    <?php endif; ?>

    <div class="abstract-card">
      <div class="abstract-title">Abstract</div>
      <div style="font-size:10.5pt; text-align:justify;"><?= nl2br(esc($abstract)) ?></div>

      <?php if (trim((string)$keywords) !== ''): ?>
        <div class="keywords"><b>Keywords:</b> <?= esc($keywords) ?></div>
      <?php endif; ?>
    </div>
  </div>

</body>

</html>