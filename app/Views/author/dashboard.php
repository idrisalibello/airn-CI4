<?= $this->extend('author/_layout') ?>

<?= $this->section('content') ?>

<div class="row">
  <h2 style="margin:0">Author Dashboard</h2>
  <a class="btn" href="/author/submissions/new">New submission</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="card flash"><?= esc($flash) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="card err"><?= esc($error) ?></div>
<?php endif; ?>

<?php if (!empty($scopeNote)): ?>
  <div class="card err"><strong>Note:</strong> <?= esc($scopeNote) ?></div>
<?php endif; ?>

<div class="card">
  <h3 style="margin-top:0">Recent submissions</h3>

  <?php if (empty($items)): ?>
    <p class="muted">No submissions yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Title</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= (int)$it['id'] ?></td>
          <td><?= esc($it['type'] ?? '-') ?></td>
          <td><?= esc($it['title'] ?? '-') ?></td>
          <td><span class="pill no">timeline</span></td>
          <td><a class="btn" href="/author/submissions/<?= (int)$it['id'] ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
