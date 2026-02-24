<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <h2><?= esc($conf['name']) ?></h2>
  <div class="muted">
    <?= esc($conf['start_date'] ?? '') ?> - <?= esc($conf['end_date'] ?? '') ?>
    <?php if (!empty($conf['venue'])): ?> | <?= esc($conf['venue']) ?><?php endif; ?>
  </div>
</div>

<div class="card">
  <?php if (!empty($conf['theme'])): ?>
    <p style="margin:0 0 8px"><strong>Theme:</strong> <?= esc($conf['theme']) ?></p>
  <?php endif; ?>

  <?php if (!empty($conf['announcement'])): ?>
    <p style="margin:0" class="muted"><?= nl2br(esc((string)$conf['announcement'])) ?></p>
  <?php else: ?>
    <p class="muted" style="margin:0">No conference announcement yet.</p>
  <?php endif; ?>
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
