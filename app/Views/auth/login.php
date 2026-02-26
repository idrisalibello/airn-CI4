<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - AIRN</title>

  <style>
    /* Keep this page consistent with site/_layout palette */
    :root{
      --bg:#fafafa;
      --text:#111;
      --muted:#666;
      --card:#fff;
      --line:#e6e6e6;
      --radius:12px;
      --focus: rgba(106,166,255,.22);
      --danger:#b00020;
    }

    *{ box-sizing:border-box; }
    body{
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue", sans-serif;
      margin:0;
      background:var(--bg);
      color:var(--text);
      line-height:1.45;
    }

    /* A simple page container like your layout */
    .wrap{
      max-width: 980px;
      margin: 22px auto;
      padding: 0 16px;
    }

    .card{
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 16px;
      margin: 12px 0;
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

    .title{
      display:flex;
      align-items:baseline;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
      margin-bottom: 10px;
    }
    .title h2{ margin:0; font-size: 20px; }
    .title .muted{ margin:0; font-size: 13px; }

    .err{
      border: 1px solid rgba(176,0,32,.18);
      background: rgba(176,0,32,.06);
      color: var(--danger);
      padding: 10px 12px;
      border-radius: 10px;
      margin: 10px 0 12px;
    }

    label{
      display:block;
      margin: 10px 0 6px;
      font-size: 13px;
      color: var(--muted);
    }

    input{
      width:100%;
      padding: 11px 12px;
      border-radius: 10px;
      border: 1px solid rgba(0,0,0,.16);
      background:#fff;
      outline:none;
      transition: box-shadow .12s ease, border-color .12s ease;
      font-size: 14px;
    }
    input:focus{
      border-color: rgba(106,166,255,.55);
      box-shadow: 0 0 0 4px var(--focus);
    }

    .row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
      margin-top: 10px;
    }

    .check{
      display:flex;
      align-items:center;
      gap:8px;
      color: var(--muted);
      font-size: 13px;
      user-select:none;
    }
    .check input{
      width:16px; height:16px;
      padding:0; margin:0;
      accent-color: #111;
      box-shadow:none;
    }

    .actions{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-top: 12px;
      align-items:center;
    }

    .primary{
      border-color:#111;
      background:#111;
      color:#fff;
    }
    .primary:hover{ background:#222; }

    .help{
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid var(--line);
      font-size: 13px;
    }

    a.link{
      color:#111;
      text-decoration:none;
      border-bottom: 1px dashed rgba(0,0,0,.35);
    }
    a.link:hover{ border-bottom-color: rgba(0,0,0,.75); }

    /* center the form card without feeling like a "marketing page" */
    .narrow{
      max-width: 520px;
      margin-left:auto;
      margin-right:auto;
    }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="card narrow">
      <div class="title">
        <h2>Login</h2>
        <p class="muted">Author portal access</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="err"><?= esc($error) ?></div>
      <?php endif; ?>

      <form method="post" action="/login" autocomplete="on">
        <?= csrf_field() ?>

        <label for="email">Email</label>
        <input id="email" type="email" name="email" required autocomplete="email" placeholder="you@example.com">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">

        <div class="row">
          <label class="check">
            <input type="checkbox" name="remember" value="1">
            Remember me
          </label>

          <!-- Remove this link if you don’t have the route yet -->
          <a class="link" href="/forgot-password">Forgot password?</a>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Login</button>
          <a class="btn" href="/register">Create account</a>
        </div>
      </form>

      <div class="help muted">
        No account yet? <a class="link" href="/register">Create an author account</a>.
        <span style="display:block;margin-top:6px;">
          Submissions require login. If you start a submission while signed out, you will be redirected.
        </span>
      </div>
    </div>
  </div>
</body>
</html>