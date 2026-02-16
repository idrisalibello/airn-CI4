<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <h2><?= esc($conf['name']) ?></h2>
  <div class="muted">
    <?= esc($conf['start_date'] ?? '') ?> - <?= esc($conf['end_date'] ?? '') ?>
    <?php if (!empty($conf['venue'])): ?> | <?= esc($conf['venue']) ?><?php endif; ?>
  </div>
</div>

<h3>Proceedings (Published)</h3>
<?php if (empty($published)): ?>
  <div class="card">No proceedings published yet.</div>
<?php else: ?>
  <?php foreach ($published as $p): ?>
    <div class="card">
      <div class="muted"><?= esc($p['published_at'] ?? '') ?><?= !empty($p['track']) ? ' | Track: ' . esc($p['track']) : '' ?></div>
      <h4><a href="/published/<?= (int)$p['publication_id'] ?>"><?= esc($p['title']) ?></a></h4>
      <a class="btn" href="/download/<?= (int)$p['submission_id'] ?>">Download PDF</a>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
