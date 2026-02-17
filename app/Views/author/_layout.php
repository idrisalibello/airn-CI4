<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Author') ?></title>
  <style>
    body{font-family:Arial;margin:0;background:#f7f7f7;color:#111}
    header{background:#111;color:#fff;padding:14px 18px}
    header a{color:#fff;text-decoration:none;margin-right:14px}
    main{max-width:1100px;margin:20px auto;padding:0 16px}
    .card{background:#fff;border:1px solid #e5e5e5;border-radius:10px;padding:14px;margin:10px 0}
    .muted{color:#666}
    input,textarea,select{width:100%;padding:10px;margin:6px 0;border:1px solid #ddd;border-radius:8px}
    button{padding:10px 14px;border-radius:8px;border:1px solid #111;background:#111;color:#fff;cursor:pointer}
    .btn{display:inline-block;padding:8px 12px;border:1px solid #111;border-radius:8px;text-decoration:none;color:#111}
    .row{display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap}
    .flash{background:#e8fff1;border:1px solid #bfe9cc}
    .err{background:#fff0f1;border:1px solid #f0b4bb}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    .pill{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #ddd;font-size:12px}
    .pill.ok{border-color:#2e7d32;color:#2e7d32}
    .pill.no{border-color:#999;color:#666}
    .timeline{list-style:none;padding:0;margin:0}
    .timeline li{display:flex;gap:10px;align-items:flex-start;padding:10px 0;border-bottom:1px solid #eee}
    .dot{width:12px;height:12px;border-radius:50%;margin-top:3px;border:2px solid #999}
    .dot.ok{border-color:#2e7d32}
    .small{font-size:12px;color:#666}
  </style>
</head>
<body>
<header>
  <strong>Author</strong>
  <a href="/author">Dashboard</a>
  <a href="/author/submissions">My Submissions</a>
  <a href="/">Public site</a>
  <a href="/logout">Logout</a>
</header>

<main>
  <?= $this->renderSection('content') ?>
</main>
</body>
</html>
