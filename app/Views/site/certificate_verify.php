<?= $this->extend('site/_layout') ?>

<?= $this->section('content') ?>

<div class="card">
  <h2 style="margin:0 0 10px">Certificate Verification</h2>

  <?php if (empty($valid)): ?>
    <p><strong>Status:</strong> Invalid / Not Found</p>
    <p class="muted">
      The certificate code does not exist in AIRN records.
    </p>
  <?php else: ?>
    <p><strong>Status:</strong> Valid</p>

    <table style="width:100%;border-collapse:collapse;margin-top:10px">
      <tbody>
        <tr>
          <td style="padding:6px 0;width:180px"><strong>Certificate ID</strong></td>
          <td style="padding:6px 0"><code><?= esc($cert['code'] ?? '-') ?></code></td>
        </tr>
        <tr>
          <td style="padding:6px 0"><strong>Type</strong></td>
          <td style="padding:6px 0"><?= esc($cert['type'] ?? '-') ?></td>
        </tr>
        <tr>
          <td style="padding:6px 0"><strong>Issued</strong></td>
          <td style="padding:6px 0"><?= esc($cert['issued_at'] ?? '-') ?></td>
        </tr>
      </tbody>
    </table>

    <hr style="border:none;border-top:1px solid #eee;margin:14px 0">

    <?php if (!empty($user)): ?>
      <p><strong>Recipient:</strong> <?= esc($user['name'] ?? '-') ?> (<?= esc($user['email'] ?? '-') ?>)</p>
    <?php endif; ?>

    <?php if (!empty($submission)): ?>
      <p><strong>Paper Title:</strong> <?= esc($submission['title'] ?? '-') ?></p>
      <p><strong>Submission Type:</strong> <?= esc($submission['type'] ?? '-') ?></p>
    <?php endif; ?>

    <?php if (!empty($publication)): ?>
      <p><strong>Published At:</strong> <?= esc($publication['published_at'] ?? '-') ?></p>
      <p><strong>DOI:</strong> <?= esc($publication['doi'] ?? '-') ?></p>
      <p><strong>Volume / Issue / Pages:</strong>
        <?= esc(($publication['volume'] ?? '-') . ' / ' . ($publication['issue'] ?? '-') . ' / ' . ($publication['pages'] ?? '-')) ?>
      </p>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?= $this->endSection() ?>
