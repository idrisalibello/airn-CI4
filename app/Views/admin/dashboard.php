<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <h2>Admin Dashboard</h2>
  <p class="muted">Populate the public site by creating journals and conferences.</p>
  <p>
    <a class="btn" href="/admin/journals">Manage Journals</a>
    <a class="btn" href="/admin/conferences">Manage Conferences</a>
  </p>
</div>

<?= $this->endSection() ?>
