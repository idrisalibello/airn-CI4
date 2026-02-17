<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <h2>Submissions</h2>

  <?php if (!empty($flash)): ?><div class="card flash"><?= esc($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="card err"><?= esc($error) ?></div><?php endif; ?>

  <form method="get" style="margin:10px 0">
    <label class="muted">Filter by status</label>
    <input type="text" name="status" value="<?= esc($status ?? '') ?>" placeholder="submitted / under_review / decided / published">
    <button class="btn" type="submit">Filter</button>
    <a class="btn" href="/admin/submissions">Clear</a>
  </form>

  <?php if (empty($items)): ?>
    <p class="muted">No submissions.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>ID</th><th>Type</th><th>Title</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= (int)$it['id'] ?></td>
          <td><?= esc($it['type']) ?></td>
          <td><?= esc($it['title']) ?></td>
          <td><?= esc($it['status']) ?></td>
          <td><a class="btn" href="/admin/submissions/<?= (int)$it['id'] ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
