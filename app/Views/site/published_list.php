<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<h2>Published</h2>

<?php if (empty($items)): ?>
  <div class="card">No publications yet.</div>
<?php else: ?>
  <?php foreach ($items as $p): ?>
    <div class="card">
      <div class="muted">
        <?= esc($p['published_at'] ?? '') ?> | <?= esc($p['type'] ?? '') ?>
        <?php if (!empty($p['doi'])): ?> | DOI: <?= esc($p['doi']) ?><?php endif; ?>
      </div>
      <h3><a href="/published/<?= (int)$p['publication_id'] ?>"><?= esc($p['title']) ?></a></h3>
      <a class="btn" href="/download/<?= (int)$p['submission_id'] ?>">Download PDF</a>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
