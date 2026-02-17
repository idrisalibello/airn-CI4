<?= $this->extend('author/_layout') ?>

<?= $this->section('content') ?>

<div class="row">
  <div>
    <h2 style="margin:0">Submission #<?= (int)$sub['id'] ?></h2>
    <div class="muted"><?= esc(($sub['type'] ?? '-') . ' • ' . ($sub['title'] ?? '-')) ?></div>
  </div>
  <a class="btn" href="/author/submissions">Back</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="card flash"><?= esc($flash) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="card err"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
  <h3 style="margin-top:0">Timeline</h3>
  <ul class="timeline">
    <?php foreach (($timeline ?? []) as $t): ?>
      <li>
        <div class="dot <?= !empty($t['done']) ? 'ok' : '' ?>"></div>
        <div>
          <div>
            <strong><?= esc($t['label'] ?? '-') ?></strong>
            <span class="pill <?= !empty($t['done']) ? 'ok' : 'no' ?>"><?= !empty($t['done']) ? 'done' : 'pending' ?></span>
          </div>
          <?php if (!empty($t['at'])): ?>
            <div class="small"><?= esc($t['at']) ?></div>
          <?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<div class="card">
  <h3 style="margin-top:0">Details</h3>
  <table>
    <tbody>
      <tr><th style="width:220px">Type</th><td><?= esc($sub['type'] ?? '-') ?></td></tr>
      <tr><th>Title</th><td><?= esc($sub['title'] ?? '-') ?></td></tr>
      <?php if (array_key_exists('track', $sub) && !empty($sub['track'])): ?>
        <tr><th>Track</th><td><?= esc($sub['track']) ?></td></tr>
      <?php endif; ?>
      <?php if (array_key_exists('abstract', $sub) && !empty($sub['abstract'])): ?>
        <tr><th>Abstract</th><td><?php echo 'nl2br(esc($sub[abstract]))'; ?></td></tr>
      <?php endif; ?>
      <?php if (array_key_exists('keywords', $sub) && !empty($sub['keywords'])): ?>
        <tr><th>Keywords</th><td><?= esc($sub['keywords']) ?></td></tr>
      <?php endif; ?>
      <?php if (array_key_exists('current_version_id', $sub)): ?>
        <tr><th>Current version</th><td><?= !empty($sub['current_version_id']) ? ('#' . (int)$sub['current_version_id']) : '-' ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <div class="row">
    <h3 style="margin:0">Manuscript versions</h3>
  </div>

  <?php if (empty($versions)): ?>
    <p class="muted">No versions found.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Version</th>
          <th>File</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($versions as $v): ?>
          <tr>
            <td><?= (int)$v['id'] ?></td>
            <td><?= isset($v['version_no']) ? ('v' . (int)$v['version_no']) : '-' ?></td>
            <td><?= esc($v['manuscript_path'] ?? '-') ?></td>
            <td>
              <?php if (!empty($v['id'])): ?>
                <a class="btn" href="/author/submissions/<?= (int)$sub['id'] ?>/download/<?= (int)$v['id'] ?>">Download</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <hr style="border:none;border-top:1px solid #eee;margin:14px 0">

  <h4 style="margin:0 0 10px">Upload new version</h4>
  <form method="post" action="/author/submissions/<?= (int)$sub['id'] ?>/upload" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="file" name="manuscript" accept=".pdf,.doc,.docx" required>
    <button type="submit">Upload</button>
  </form>
</div>

<?= $this->endSection() ?>
