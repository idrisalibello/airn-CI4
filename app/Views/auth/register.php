<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Register - AIRN</title>
<style>
body{font-family:Arial;margin:40px}
.box{max-width:520px;padding:20px;border:1px solid #ddd;border-radius:8px}
input{width:100%;padding:10px;margin:8px 0}
button{padding:10px 14px}
.err{color:#b00020;margin:10px 0}
.muted{color:#666}
</style>
</head>
<body>
<div class="box">
<h2>Create Author Account</h2>
<p class="muted">Register as an author to submit manuscripts and track decisions.</p>

<?php if (!empty($error)): ?>
<div class="err"><?= esc($error) ?></div>
<?php endif; ?>

<form method="post" action="/register">
<?= csrf_field() ?>

<label>Full name</label>
<input type="text" name="name" value="<?= esc($old['name'] ?? '') ?>" required>

<label>Email</label>
<input type="email" name="email" value="<?= esc($old['email'] ?? '') ?>" required>

<label>Password</label>
<input type="password" name="password" required>

<label>Confirm password</label>
<input type="password" name="password2" required>

<button type="submit">Create account</button>
</form>

<p style="margin-top:12px;">Already have an account? <a href="/login">Login</a></p>
</div>
</body>
</html>
