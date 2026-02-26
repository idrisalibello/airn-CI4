<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - AIRN</title>
<style>
  :root{
    --bg1:#0b1220;
    --bg2:#0f1a33;
    --card:#0f1a2b;
    --card2:#0b1424;
    --text:#e7ecff;
    --muted:#a9b4da;
    --line:rgba(255,255,255,.10);
    --brand:#6aa6ff;
    --brand2:#8b5cff;
    --danger:#ff5c7a;
    --shadow: 0 18px 50px rgba(0,0,0,.45);
    --radius: 18px;
  }

  *{ box-sizing:border-box; }
  body{
    margin:0;
    min-height:100vh;
    font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue", sans-serif;
    color:var(--text);
    background:
      radial-gradient(1200px 600px at 10% 10%, rgba(106,166,255,.18), transparent 60%),
      radial-gradient(1000px 600px at 90% 20%, rgba(139,92,255,.16), transparent 55%),
      radial-gradient(900px 520px at 50% 100%, rgba(106,166,255,.10), transparent 60%),
      linear-gradient(180deg, var(--bg1), var(--bg2));
    display:flex;
    align-items:center;
    justify-content:center;
    padding:28px 16px;
  }

  .wrap{
    width:100%;
    max-width:980px;
    display:grid;
    grid-template-columns: 1.15fr .85fr;
    gap:18px;
  }

  .panel{
    border:1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow:hidden;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
  }

  .hero{
    padding:34px 34px 30px;
    background:
      radial-gradient(900px 420px at 30% 20%, rgba(106,166,255,.22), transparent 60%),
      radial-gradient(700px 360px at 60% 70%, rgba(139,92,255,.20), transparent 60%),
      linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
  }

  .brandRow{
    display:flex; align-items:center; gap:12px;
    margin-bottom:18px;
  }

  .mark{
    width:42px; height:42px; border-radius:12px;
    background: linear-gradient(135deg, var(--brand), var(--brand2));
    box-shadow: 0 10px 30px rgba(106,166,255,.18);
    position:relative;
  }
  .mark:after{
    content:"";
    position:absolute; inset:10px;
    border-radius:9px;
    border:1px solid rgba(255,255,255,.35);
  }

  .brandText b{ display:block; letter-spacing:.3px; }
  .brandText span{ color:var(--muted); font-size:13px; }

  h1{
    margin:10px 0 10px;
    font-size:32px;
    line-height:1.1;
    letter-spacing:.2px;
  }
  .lead{
    margin:0 0 18px;
    color:var(--muted);
    max-width:52ch;
    font-size:14.5px;
    line-height:1.55;
  }

  .bullets{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:10px;
    margin-top:14px;
  }
  .pill{
    border:1px solid var(--line);
    background: rgba(11,20,36,.45);
    border-radius: 14px;
    padding:10px 12px;
    display:flex;
    gap:10px;
    align-items:flex-start;
  }
  .dot{
    width:10px;height:10px;border-radius:999px;
    background: linear-gradient(135deg, var(--brand), var(--brand2));
    margin-top:4px;
    flex:0 0 auto;
  }
  .pill b{ display:block; font-size:13px; margin-bottom:2px;}
  .pill span{ display:block; color:var(--muted); font-size:12.5px; line-height:1.35;}

  .card{
    padding:26px 24px;
    background: linear-gradient(180deg, rgba(11,20,36,.62), rgba(11,20,36,.38));
  }

  .titleRow{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:12px;
    margin-bottom:14px;
  }
  .titleRow h2{
    margin:0;
    font-size:20px;
    letter-spacing:.2px;
  }
  .hint{
    color:var(--muted);
    font-size:12.5px;
  }

  .err{
    display:flex;
    gap:10px;
    align-items:flex-start;
    border:1px solid rgba(255,92,122,.35);
    background: rgba(255,92,122,.10);
    color:#ffd2db;
    padding:10px 12px;
    border-radius: 14px;
    margin:12px 0 14px;
  }
  .err .badge{
    width:22px;height:22px;border-radius:7px;
    background: rgba(255,92,122,.25);
    display:grid; place-items:center;
    font-weight:700;
    flex:0 0 auto;
  }

  form{ margin-top:6px; }
  label{
    display:block;
    margin:10px 0 6px;
    color:var(--muted);
    font-size:13px;
  }

  .field{
    position:relative;
  }
  input{
    width:100%;
    padding:12px 12px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.14);
    background: rgba(15,26,43,.70);
    color:var(--text);
    outline:none;
    transition: border .15s ease, box-shadow .15s ease, transform .05s ease;
  }
  input::placeholder{ color:rgba(169,180,218,.65); }
  input:focus{
    border-color: rgba(106,166,255,.55);
    box-shadow: 0 0 0 4px rgba(106,166,255,.16);
  }

  .row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-top:12px;
  }

  .check{
    display:flex; align-items:center; gap:8px;
    color:var(--muted);
    font-size:13px;
    user-select:none;
  }
  .check input{
    width:16px; height:16px;
    padding:0; margin:0;
    accent-color: var(--brand);
    box-shadow:none;
  }

  .link{
    color: #cfe0ff;
    text-decoration:none;
    border-bottom:1px dashed rgba(207,224,255,.45);
  }
  .link:hover{ border-bottom-color: rgba(207,224,255,.85); }

  button{
    width:100%;
    margin-top:14px;
    padding:12px 14px;
    border:0;
    border-radius: 14px;
    color: #081023;
    font-weight: 700;
    letter-spacing:.2px;
    background: linear-gradient(135deg, var(--brand), var(--brand2));
    cursor:pointer;
    transition: transform .06s ease, filter .15s ease;
  }
  button:hover{ filter: brightness(1.04); }
  button:active{ transform: translateY(1px); }

  .footer{
    margin-top:14px;
    padding-top:12px;
    border-top:1px solid var(--line);
    color:var(--muted);
    font-size:13px;
    line-height:1.45;
  }

  @media (max-width: 860px){
    .wrap{ grid-template-columns: 1fr; }
    .bullets{ grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div class="wrap">
  <section class="panel hero" aria-hidden="true">
    <div class="brandRow">
      <div class="mark"></div>
      <div class="brandText">
        <b>AIRN</b>
        <span>Academic & International Research Network</span>
      </div>
    </div>

    <h1>Welcome back.</h1>
    <p class="lead">
      Sign in to manage submissions, track reviews, and access author tools for journal and conference workflows.
    </p>

    <div class="bullets">
      <div class="pill">
        <div class="dot"></div>
        <div>
          <b>Submission dashboard</b>
          <span>View status, upload revisions, and download decisions.</span>
        </div>
      </div>
      <div class="pill">
        <div class="dot"></div>
        <div>
          <b>Secure access</b>
          <span>CSRF protection and session-based authentication.</span>
        </div>
      </div>
      <div class="pill">
        <div class="dot"></div>
        <div>
          <b>Author-first flow</b>
          <span>Clean navigation that stays out of your way.</span>
        </div>
      </div>
      <div class="pill">
        <div class="dot"></div>
        <div>
          <b>Fast support</b>
          <span>Clear errors and predictable login experience.</span>
        </div>
      </div>
    </div>
  </section>

  <section class="panel card">
    <div class="titleRow">
      <h2>Login</h2>
      <div class="hint">Use your author account</div>
    </div>

    <?php if (!empty($error)): ?>
      <div class="err" role="alert">
        <div class="badge">!</div>
        <div><?= esc($error) ?></div>
      </div>
    <?php endif; ?>

    <form method="post" action="/login" autocomplete="on">
      <?= csrf_field() ?>

      <label for="email">Email</label>
      <div class="field">
        <input id="email" type="email" name="email" required placeholder="you@example.com" autocomplete="email">
      </div>

      <label for="password">Password</label>
      <div class="field">
        <input id="password" type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
      </div>

      <div class="row">
        <label class="check">
          <input type="checkbox" name="remember" value="1">
          Remember me
        </label>
        <a class="link" href="/forgot-password">Forgot password?</a>
      </div>

      <button type="submit">Sign in</button>
    </form>

    <div class="footer">
      No account yet? <a class="link" href="/register">Create author account</a>
    </div>
  </section>
</div>

</body>
</html>