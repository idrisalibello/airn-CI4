<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'AIRN') ?></title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            background: #fafafa;
            color: #111
        }

        header {
            background: #111;
            color: #fff;
            padding: 14px 18px
        }

        header a {
            color: #fff;
            text-decoration: none;
            margin-right: 14px
        }

        main {
            max-width: 980px;
            margin: 20px auto;
            padding: 0 16px
        }

        .card {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 14px;
            margin: 10px 0
        }

        .muted {
            color: #666
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #111;
            border-radius: 8px;
            text-decoration: none;
            color: #111
        }

        footer {
            max-width: 980px;
            margin: 30px auto;
            padding: 0 16px 30px;
            color: #666
        }
    </style>
</head>

<body>
    <header>
        <strong>AIRN</strong>
        <a href="/">Home</a>
        <a href="/journals">Journals</a>
        <a href="/conferences">Conferences</a>
        <a href="/published">Published</a>
        <a href="/about">About</a>
        <a href="/contact">Contact</a>
        <?php $auth = session('auth_user'); ?>
        <?php if ($auth): ?>
            <a href="/dashboard">Dashboard</a>
            <a href="/logout">Logout</a>
        <?php else: ?>
            <a href="/login">Login</a>
        <?php endif; ?>

    </header>

    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <footer>
        <div>Academic & International Research Network</div>
    </footer>
</body>

</html>