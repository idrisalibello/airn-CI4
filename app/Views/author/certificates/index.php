<?= $this->extend('author/_layout') ?>

<?= $this->section('content') ?>

<div class="row">
  <h2 style="margin:0">My Certificates</h2>
</div>

<?php if (!empty($flash)): ?>
  <div class="card flash"><?= esc($flash) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="card err"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
  <?php if (empty($items)): ?>
    <p class="muted">No certificates issued yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Type</th>
          <th>Issued</th>
          <th>Code</th>
          <th>PDF</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $c): ?>
        <tr>
          <td><?= esc($c['type'] ?? '-') ?></td>
          <td><?= esc($c['issued_at'] ?? '-') ?></td>
          <td><code><?= esc($c['code'] ?? '-') ?></code></td>
          <td>
            <?php if (!empty($c['code'])): ?>
              <a class="btn" href="/author/certificates/<?= esc($c['code']) ?>/download">Download</a>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <p class="muted" style="margin-top:10px">
      Tip: Each certificate has a verification code. Anyone can verify via the public verification page.
    </p>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
