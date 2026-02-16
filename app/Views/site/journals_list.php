<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<h2>Journals</h2>

<?php if (empty($items)): ?>
  <div class="card">No journals yet.</div>
<?php else: ?>
  <?php foreach ($items as $j): ?>
    <div class="card">
      <h3><a href="/journals/<?= esc($j['slug']) ?>"><?= esc($j['name']) ?></a></h3>
      <?php if (!empty($j['issn'])): ?><div class="muted">ISSN: <?= esc($j['issn']) ?></div><?php endif; ?>
      <?php if (!empty($j['description'])): ?><p class="muted"><?= esc($j['description']) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
