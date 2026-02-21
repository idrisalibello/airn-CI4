<?php
// app/Views/publications/article_pdf.php
// Inputs:
// - header_left, header_right, doi, license, received_at, accepted_at, published_at, article_id
// - title, authors_html, affiliations_html, corresponding_html
// - body_html (HTML)
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 18mm 16mm 16mm 16mm; }
    body { font-family: DejaVu Serif, DejaVu Sans, Arial, sans-serif; font-size: 11.5px; line-height: 1.45; color: #111; }

    .hdr {
      position: fixed;
      top: -14mm;
      left: 0; right: 0;
      height: 14mm;
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 9.5px;
      color: #222;
      border-bottom: 1px solid #cfd8e3;
      padding-bottom: 2mm;
    }
    .hdr .row1 { display: flex; justify-content: space-between; }
    .hdr .row2 { margin-top: 1mm; display: flex; justify-content: space-between; }
    .hdr .mono { font-family: DejaVu Sans Mono, monospace; }

    .metaLine { margin-top: 2mm; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9.5px; color: #222; }
    h1 { font-size: 18px; margin: 8mm 0 3mm; line-height: 1.25; }
    .authors { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; margin: 0 0 2mm; }
    .affiliations { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; margin: 0 0 2mm; color: #333; }
    .corresponding { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; margin: 0 0 5mm; color: #333; }
    h2 { font-size: 13px; margin: 5mm 0 2mm; }
    .abstract { background: #f4f6f9; border-left: 3px solid #cfd8e3; padding: 3mm; margin: 0 0 4mm; }
    .kw { margin-top: 2mm; font-size: 10.5px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #cfd8e3; padding: 2mm; font-size: 10.5px; }
    .figureCap { font-size: 10px; color: #333; margin: 1mm 0 4mm; }
  </style>
</head>
<body>

  <div class="hdr">
    <div class="row1">
      <div><?= esc($header_left ?? '') ?></div>
      <div><?= esc($header_right ?? '') ?></div>
    </div>
    <div class="row2">
      <div>DOI: <span class="mono"><?= esc($doi ?? '-') ?></span> • Licensed under <?= esc($license ?? 'CC BY 4.0') ?></div>
      <div class="mono"><?= esc($page_label ?? '') ?></div>
    </div>
  </div>

  <div class="metaLine">
    Received: <?= esc($received_at ?? '-') ?> &nbsp;&nbsp; Accepted: <?= esc($accepted_at ?? '-') ?> &nbsp;&nbsp; Published: <?= esc($published_at ?? '-') ?>
  </div>
  <div class="metaLine">
    Article ID: <span class="mono"><?= esc($article_id ?? '-') ?></span> &nbsp;&nbsp; DOI: <span class="mono"><?= esc($doi ?? '-') ?></span>
  </div>

  <h1><?= esc($title ?? '') ?></h1>

  <div class="authors"><?= $authors_html ?? '' ?></div>
  <div class="affiliations"><?= $affiliations_html ?? '' ?></div>
  <div class="corresponding"><?= $corresponding_html ?? '' ?></div>

  <div><?= $body_html ?? '' ?></div>

</body>
</html>
