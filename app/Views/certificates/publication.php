<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    /* Match the journal PDF "clean, modern, blue header bar" feel */
    @page { margin: 36px 42px; }

    body {
      font-family: Arial, sans-serif;
      color: #111;
      font-size: 13px;
      line-height: 1.5;
    }

    .topbar {
      background: #0B3D91; /* extracted from your journal header */
      color: #fff;
      padding: 14px 18px;
    }

    .topbar .row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .topbar .left {
      font-size: 16px;
      font-weight: 700;
    }

    .topbar .right {
      font-size: 13px;
      font-weight: 400;
      opacity: 0.95;
    }

    .meta {
      margin-top: 18px;
      font-size: 11px;
      color: #333;
    }

    .title {
      margin-top: 18px;
      text-align: center;
      font-size: 28px;
      font-weight: 800;
      letter-spacing: 0.2px;
    }

    .recipient {
      margin-top: 14px;
      text-align: center;
      font-size: 22px;
      font-weight: 800;
    }

    .subtitle {
      margin-top: 10px;
      text-align: center;
      font-size: 13px;
      color: #333;
    }

    .paper {
      margin-top: 14px;
      text-align: center;
      font-size: 15px;
      font-weight: 700;
    }

    .infoBox {
      margin-top: 20px;
      border: 1px solid #d7e0f2;
      background: #f3f6fb;
      padding: 14px;
    }

    .grid {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }

    .grid td {
      padding: 6px 0;
      vertical-align: top;
    }

    .grid .k {
      width: 160px;
      color: #222;
      font-weight: 700;
    }

    .signRow {
      margin-top: 34px;
      display: flex;
      justify-content: space-between;
      gap: 18px;
    }

    .sig {
      width: 48%;
      border-top: 1px solid #999;
      padding-top: 6px;
      font-size: 12px;
      color: #333;
    }

    .sig.right {
      text-align: right;
    }

    .footer {
      position: fixed;
      left: 42px;
      right: 42px;
      bottom: 26px;
      font-size: 11px;
      color: #333;
    }

    .footer .line {
      border-top: 1px solid #ddd;
      margin-bottom: 8px;
    }

    .mono { font-family: "Courier New", monospace; }

    /* very light watermark like professional documents */
    .wm {
      position: fixed;
      left: 0;
      right: 0;
      top: 42%;
      text-align: center;
      font-size: 78px;
      font-weight: 800;
      color: rgba(11, 61, 145, 0.06);
      transform: rotate(-12deg);
      z-index: -1;
    }
  </style>
</head>
<body>

<div class="wm">AIRN</div>

<div class="topbar">
  <div class="row">
    <div class="left"><?= esc($brand_left ?? 'AIRN Journal of Computing Systems') ?></div>
    <div class="right"><?= esc($brand_right ?? '') ?></div>
  </div>
</div>

<div class="meta">
  Certificate ID: <span class="mono"><?= esc($code ?? '-') ?></span>
</div>

<div class="title">Publication Certificate</div>

<div class="recipient"><?= esc($recipient_name ?? 'Author') ?></div>

<div class="subtitle">
  This certifies that the above named author has a paper published under AIRN.
</div>

<div class="paper"><?= esc($paper_title ?? '-') ?></div>

<div class="infoBox">
  <table class="grid">
    <tr>
      <td class="k">Published</td>
      <td><?= esc(!empty($published_at) ? date('d M Y', strtotime($published_at)) : '-') ?></td>
    </tr>
    <tr>
      <td class="k">DOI</td>
      <td><?= esc($doi ?: '-') ?></td>
    </tr>
    <tr>
      <td class="k">Volume</td>
      <td><?= esc($volume ?: '-') ?></td>
    </tr>
    <tr>
      <td class="k">Issue</td>
      <td><?= esc($issue ?: '-') ?></td>
    </tr>
    <tr>
      <td class="k">Pages</td>
      <td><?= esc($pages ?: '-') ?></td>
    </tr>
    <tr>
      <td class="k">Verification</td>
      <td><?= esc($verify_url ?? '-') ?></td>
    </tr>
  </table>
</div>

<div class="signRow">
  <div class="sig">
    Editor-in-Chief<br>
    <span class="muted">(Name)</span>
  </div>
  <div class="sig right">
    Managing Editor<br>
    <span class="muted">(Name)</span>
  </div>
</div>

<div class="footer">
  <div class="line"></div>
  <div>
    DOI: <?= esc($doi ?: '-') ?> &nbsp;•&nbsp;
    Verification: <?= esc($verify_url ?? '-') ?> &nbsp;•&nbsp;
    Certificate: <span class="mono"><?= esc($code ?? '-') ?></span>
  </div>
</div>

</body>
</html>
