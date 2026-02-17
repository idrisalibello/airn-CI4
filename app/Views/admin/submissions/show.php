<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <h2>Submission #<?= (int)$sub['id'] ?></h2>
  <p class="muted"><?= esc($sub['type'].' • '.$sub['status']) ?></p>

  <?php if (!empty($flash)): ?><div class="card flash"><?= esc($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="card err"><?= esc($error) ?></div><?php endif; ?>

  <h3>Versions</h3>
  <?php if (empty($versions)): ?>
    <p class="muted">No versions.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($versions as $v): ?>
        <li>
          v<?= (int)$v['version_no'] ?> —
          <code><?= esc($v['manuscript_path']) ?></code>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <hr>

  <?php if (!empty($publication)): ?>
    <h3>Publication</h3>
    <p><strong>Published:</strong> <?= esc($publication['published_at'] ?? '-') ?></p>
    <p><strong>DOI:</strong> <?= esc($publication['doi'] ?? '-') ?></p>
  <?php else: ?>
    <h3>Publish</h3>
    <form method="post" action="/admin/submissions/<?= (int)$sub['id'] ?>/publish">
      <?= csrf_field() ?>
      <label>Volume</label><input name="volume" placeholder="e.g. 1">
      <label>Issue</label><input name="issue" placeholder="e.g. 2">
      <label>Pages</label><input name="pages" placeholder="e.g. 12-19">
      <label>DOI</label><input name="doi" placeholder="optional">
      <button class="btn" type="submit">Publish & Issue Certificate</button>
    </form>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
