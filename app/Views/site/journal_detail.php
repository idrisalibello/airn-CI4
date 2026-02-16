<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <h2><?= esc($journal['name']) ?></h2>
  <?php if (!empty($journal['issn'])): ?><div class="muted">ISSN: <?= esc($journal['issn']) ?></div><?php endif; ?>
  <?php if (!empty($journal['description'])): ?><p class="muted"><?= esc($journal['description']) ?></p><?php endif; ?>
</div>

<h3>Latest Published</h3>
<?php if (empty($published)): ?>
  <div class="card">No published papers yet.</div>
<?php else: ?>
  <?php foreach ($published as $p): ?>
    <div class="card">
      <div class="muted"><?= esc($p['published_at'] ?? '') ?></div>
      <h4><a href="/published/<?= (int)$p['publication_id'] ?>"><?= esc($p['title']) ?></a></h4>
      <a class="btn" href="/download/<?= (int)$p['submission_id'] ?>">Download PDF</a>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
