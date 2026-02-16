<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<h2>Conferences</h2>

<?php if (empty($items)): ?>
  <div class="card">No conferences yet.</div>
<?php else: ?>
  <?php foreach ($items as $c): ?>
    <div class="card">
      <h3><a href="/conferences/<?= esc($c['slug']) ?>"><?= esc($c['name']) ?></a></h3>
      <div class="muted">
        <?= esc($c['start_date'] ?? '') ?> - <?= esc($c['end_date'] ?? '') ?>
        <?php if (!empty($c['venue'])): ?> | <?= esc($c['venue']) ?><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
