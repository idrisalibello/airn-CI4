<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register - AIRN</title>

<style>
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

  .narrow{
    max-width: 560px;
    margin-left:auto;
    margin-right:auto;
  }

  .muted{ color: var(--muted); }

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
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:12px;
  }
  @media (max-width: 640px){
    .row{ grid-template-columns: 1fr; }
  }

  .btn{
    display:inline-block;
    padding: 8px 12px;
    border: 1px solid #111;
    border-radius: 10px;
    text-decoration:none;
    color:#111;
    background:#fff;
    transition: transform .06s ease, background .12s ease;
    cursor:pointer;
  }
  .btn:hover{ background:#f3f3f3; }
  .btn:active{ transform: translateY(1px); }

  .primary{
    border-color:#111;
    background:#111;
    color:#fff;
  }
  .primary:hover{ background:#222; }

  .actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top: 14px;
    align-items:center;
  }

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

  .hint{
    font-size: 12.5px;
    margin-top: 6px;
    color: var(--muted);
  }
</style>
</head>

<body>
  <div class="wrap">
    <div class="card narrow">
      <div class="title">
        <h2>Create Author Account</h2>
        <p class="muted">Author registration</p>
      </div>

      <p class="muted" style="margin:0 0 10px;">
        Register as an author to submit manuscripts and track decisions.
      </p>

      <?php if (!empty($error)): ?>
        <div class="err"><?= esc($error) ?></div>
      <?php endif; ?>

      <form method="post" action="/register" autocomplete="on">
        <?= csrf_field() ?>

        <label for="name">Full name</label>
        <input id="name" type="text" name="name" value="<?= esc($old['name'] ?? '') ?>" required placeholder="Surname Firstname">

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?= esc($old['email'] ?? '') ?>" required placeholder="you@example.com" autocomplete="email">

        <div class="row">
          <div>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimum 8 characters">
          </div>

          <div>
            <label for="password2">Confirm password</label>
            <input id="password2" type="password" name="password2" required autocomplete="new-password" placeholder="Repeat password">
          </div>
        </div>

        <div class="hint">
          Use a strong password. You will use this account to submit manuscripts and view decisions.
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Create account</button>
          <a class="btn" href="/login">Login instead</a>
        </div>
      </form>

      <div class="help muted">
        Already have an account? <a class="link" href="/login">Login</a>.
      </div>
    </div>
  </div>
</body>
</html>