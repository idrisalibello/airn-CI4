<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<div class="row">
  <h2>Conferences</h2>
  <a class="btn" href="/admin/conferences/new">New Conference</a>
</div>

<?php if (!empty($flash)): ?>
  <div class="card flash"><?= esc($flash) ?></div>
<?php endif; ?>

<div class="card">
  <?php if (empty($items)): ?>
    <div class="muted">No conferences yet.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Slug</th>
          <th>Dates</th>
          <th>Venue</th>
          <th style="width:220px">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $c): ?>
        <tr>
          <td><?= esc($c['name']) ?></td>
          <td><?= esc($c['slug']) ?></td>
          <td><?= esc(($c['start_date'] ?? '') . ' - ' . ($c['end_date'] ?? '')) ?></td>
          <td><?= esc($c['venue'] ?? '') ?></td>
          <td>
            <a class="btn" href="/admin/conferences/<?= (int)$c['id'] ?>/edit">Edit</a>
            <form method="post" action="/admin/conferences/<?= (int)$c['id'] ?>/delete" style="display:inline">
              <button class="danger" type="submit" onclick="return confirm('Delete this conference?')">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
