<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - AIRN</title>
<style>
body{font-family:Arial;margin:40px}
.box{max-width:420px;padding:20px;border:1px solid #ddd;border-radius:8px}
input{width:100%;padding:10px;margin:8px 0}
button{padding:10px 14px}
.err{color:#b00020;margin:10px 0}
</style>
</head>
<body>
<div class="box">
<h2>Login</h2>

<?php if (!empty($error)): ?>
<div class="err"><?= esc($error) ?></div>
<?php endif; ?>

<form method="post" action="/login">
<?= csrf_field() ?>
<label>Email</label>
<input type="email" name="email" required>

<label>Password</label>
<input type="password" name="password" required>

<button type="submit">Login</button>
</form>

<p style="margin-top:12px;">No account yet? <a href="/register">Create author account</a></p>
</div>
</body>
</html>
