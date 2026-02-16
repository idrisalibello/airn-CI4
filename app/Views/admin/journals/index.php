<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<div class="row">
  <h2>Journals</h2>
  <a class="btn" href="/admin/journals/new">New Journal</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="card flash"><?= esc($flash) ?></div>
<?php endif; ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="muted">No journals yet.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Slug</th>
          <th>ISSN</th>
          <th style="width:220px">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $j): ?>
        <tr>
          <td><?= esc($j['name']) ?></td>
          <td><?= esc($j['slug']) ?></td>
          <td><?= esc($j['issn'] ?? '') ?></td>
          <td>
            <a class="btn" href="/admin/journals/<?= (int)$j['id'] ?>/edit">Edit</a>
            <form method="post" action="/admin/journals/<?= (int)$j['id'] ?>/delete" style="display:inline">
              <button class="danger" type="submit" onclick="return confirm('Delete this journal?')">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
