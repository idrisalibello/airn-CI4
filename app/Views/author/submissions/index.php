<?= $this->extend('author/_layout') ?>

<?= $this->section('content') ?>

<div class="row">
  <h2 style="margin:0">My Submissions</h2>
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

<?php if (!empty($scopeNote)): ?>
  <div class="card" style="border-left:4px solid #c00;">
    <strong>Notice:</strong>
    <?= esc($scopeNote) ?>
  </div>
<?php endif; ?>

<div class="card">
  <?php if (empty($items)): ?>
    <p class="muted">No submissions yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Title</th>
          <th>Current Version</th>
          <th>Payment</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= (int)$it['id'] ?></td>
            <td><?= esc($it['type'] ?? '-') ?></td>
            <td><?= esc($it['title'] ?? '-') ?></td>
            <td><?= !empty($it['current_version_id']) ? ('#' . (int)$it['current_version_id']) : '-' ?></td>
            <td>
              <?php $ps = (string)($it['payment_status'] ?? 'UNPAID'); ?>

              <?php if ($ps === 'PAID'): ?>
                <span class="pill ok">PAID</span>

              <?php elseif ($ps === 'PENDING'): ?>
                <span class="pill">PENDING</span>
                <a class="btn" href="/author/submissions/<?= (int)$it['id'] ?>/pay">Retry Pay</a>

              <?php else: ?>
                <span class="pill no">UNPAID</span>
                <a class="btn" href="/author/submissions/<?= (int)$it['id'] ?>/pay">Pay Now</a>
              <?php endif; ?>
            </td>
            <td><a class="btn" href="/author/submissions/<?= (int)$it['id'] ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>