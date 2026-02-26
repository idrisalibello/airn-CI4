<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'AIRN') ?></title>

  <style>
    :root{
      --bg:#fafafa;
      --text:#111;
      --muted:#666;
      --card:#fff;
      --line:#e6e6e6;

      --ink:#0f172a;         /* deep slate */
      --navbg:#0b1220;       /* near-black */
      --navline:rgba(255,255,255,.10);
      --navmuted:rgba(255,255,255,.75);
      --navhover:rgba(255,255,255,.10);

      --radius:12px;
    }

    *{ box-sizing:border-box; }
    body{
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue", sans-serif;
      margin:0;
      background:var(--bg);
      color:var(--text);
      line-height:1.45;
    }

    /* Header */
    header{
      position: sticky;
      top: 0;
      z-index: 50;
      background: linear-gradient(180deg, rgba(11,18,32,1), rgba(11,18,32,.96));
      border-bottom: 1px solid var(--navline);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }

    .navwrap{
      max-width: 980px;
      margin: 0 auto;
      padding: 12px 16px;
      display:flex;
      gap:14px;
      align-items:center;
      justify-content:space-between;
    }

    .brand{
      display:flex;
      align-items:baseline;
      gap:10px;
      min-width: 200px;
    }
    .brand a{
      color:#fff;
      text-decoration:none;
      font-weight:800;
      letter-spacing:.4px;
      font-size: 16px;
    }
    .brand small{
      color: var(--navmuted);
      font-size: 12px;
      letter-spacing:.2px;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
      max-width: 340px;
    }

    nav{
      display:flex;
      flex-wrap:wrap;
      gap:6px;
      align-items:center;
      justify-content:flex-end;
    }

    header a.navlink{
      color: var(--navmuted);
      text-decoration:none;
      padding: 7px 10px;
      border-radius: 10px;
      border: 1px solid transparent;
      transition: background .12s ease, border-color .12s ease, color .12s ease;
      font-size: 13.5px;
    }
    header a.navlink:hover{
      background: var(--navhover);
      border-color: rgba(255,255,255,.12);
      color:#fff;
    }
    header a.navlink.active{
      background: rgba(255,255,255,.14);
      border-color: rgba(255,255,255,.18);
      color:#fff;
    }

    .navsep{
      width:1px;
      height:22px;
      background: rgba(255,255,255,.14);
      margin: 0 4px;
    }

    /* Page container */
    main{
      max-width: 980px;
      margin: 20px auto;
      padding: 0 16px;
    }

    /* Cards + buttons (kept compatible with your current pages) */
    .card{
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 14px;
      margin: 10px 0;
    }
    .muted{ color: var(--muted); }

    .btn{
      display:inline-block;
      padding: 8px 12px;
      border: 1px solid #111;
      border-radius: 10px;
      text-decoration:none;
      color:#111;
      background:#fff;
      transition: transform .06s ease, background .12s ease;
    }
    .btn:hover{ background:#f3f3f3; }
    .btn:active{ transform: translateY(1px); }

    /* Footer */
    footer{
      max-width: 980px;
      margin: 26px auto 0;
      padding: 0 16px 30px;
      color: #666;
    }
    .footline{
      border-top: 1px solid var(--line);
      padding-top: 14px;
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      justify-content:space-between;
      align-items:center;
    }

    @media (max-width: 760px){
      .navwrap{ align-items:flex-start; }
      .brand{ min-width:auto; flex: 1 1 auto; }
      .brand small{ display:none; } /* keep header compact on mobile */
      nav{ justify-content:flex-start; }
    }
  </style>
</head>

<body>

<?php
  $auth = session('auth_user');
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  $isActive = function(string $href) use ($path): bool {
      if ($href === '/') return $path === '/';
      return strpos($path, $href) === 0; // prefix match
  };
?>

<header>
  <div class="navwrap">
    <div class="brand">
      <a href="/">AIRN</a>
      <small>Academic &amp; International Research Network</small>
    </div>

    <nav aria-label="Primary">
      <a class="navlink <?= $isActive('/') ? 'active' : '' ?>" href="/">Home</a>
      <a class="navlink <?= $isActive('/journals') ? 'active' : '' ?>" href="/journals">Journals</a>
      <a class="navlink <?= $isActive('/conferences') ? 'active' : '' ?>" href="/conferences">Conferences</a>
      <a class="navlink <?= $isActive('/published') ? 'active' : '' ?>" href="/published">Published</a>
      <a class="navlink <?= $isActive('/about') ? 'active' : '' ?>" href="/about">About</a>
      <a class="navlink <?= $isActive('/contact') ? 'active' : '' ?>" href="/contact">Contact</a>

      <span class="navsep" aria-hidden="true"></span>

      <?php if ($auth): ?>
        <a class="navlink <?= $isActive('/dashboard') ? 'active' : '' ?>" href="/dashboard">Dashboard</a>
        <a class="navlink" href="/logout">Logout</a>
      <?php else: ?>
        <a class="navlink <?= $isActive('/register') ? 'active' : '' ?>" href="/register">Register</a>
        <a class="navlink <?= $isActive('/login') ? 'active' : '' ?>" href="/login">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main>
  <?= $this->renderSection('content') ?>
</main>

<footer>
  <div class="footline">
    <div>Academic &amp; International Research Network</div>
    <div class="muted">© <?= date('Y') ?> AIRN</div>
  </div>
</footer>

</body>
</html>