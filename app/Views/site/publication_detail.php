<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <div class="muted">
    Published: <?= esc($item['published_at'] ?? '') ?>
    <?php if (!empty($item['doi'])): ?> | DOI: <?= esc($item['doi']) ?><?php endif; ?>
  </div>
  <h2><?= esc($item['title']) ?></h2>

  <?php if (!empty($item['keywords'])): ?>
    <p class="muted"><strong>Keywords:</strong> <?= esc($item['keywords']) ?></p>
  <?php endif; ?>

  <h3>Abstract</h3>
  <p><?= esc($item['abstract']) ?></p>

  <p>
    <a class="btn" href="/download/<?= (int)$item['submission_id'] ?>">Download PDF</a>
  </p>
</div>

<?= $this->endSection() ?>
