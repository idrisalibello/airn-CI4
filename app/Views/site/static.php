<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <h2><?= esc($heading ?? 'Page') ?></h2>
  <p class="muted">Content placeholder. We’ll replace this with real copy later.</p>
</div>

<?= $this->endSection() ?>
