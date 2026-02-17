<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Admin') ?></title>
  <style>
    body{font-family:Arial;margin:0;background:#f7f7f7;color:#111}
    header{background:#111;color:#fff;padding:14px 18px}
    header a{color:#fff;text-decoration:none;margin-right:14px}
    main{max-width:1100px;margin:20px auto;padding:0 16px}
    .card{background:#fff;border:1px solid #e5e5e5;border-radius:10px;padding:14px;margin:10px 0}
    .muted{color:#666}
    input,textarea{width:100%;padding:10px;margin:6px 0;border:1px solid #ddd;border-radius:8px}
    button{padding:10px 14px;border-radius:8px;border:1px solid #111;background:#111;color:#fff;cursor:pointer}
    .btn{display:inline-block;padding:8px 12px;border:1px solid #111;border-radius:8px;text-decoration:none;color:#111}
    .row{display:flex;gap:10px;align-items:center;justify-content:space-between}
    .danger{border-color:#b00020;color:#b00020}
    .flash{background:#e8fff1;border:1px solid #bfe9cc}
    .err{background:#fff0f1;border:1px solid #f0b4bb}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
  </style>
</head>
<body>
<header>
  <strong>Admin</strong>
  <a href="/admin">Dashboard</a>
  <a href="/admin/journals">Journals</a>
  <a href="/admin/conferences">Conferences</a>
  <a class="btn" href="/admin/submissions">Submissions</a>
  <a href="/">Public site</a>
  <a href="/logout">Logout</a>
</header>

<main>
  <?= $this->renderSection('content') ?>
</main>
</body>
</html>
