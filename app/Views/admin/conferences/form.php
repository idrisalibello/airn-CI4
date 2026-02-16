<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<h2><?= esc($mode === 'create' ? 'New Conference' : 'Edit Conference') ?></h2>

<?php if (!empty($error)): ?>
  <div class="card err"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" action="<?= esc($mode === 'create' ? '/admin/conferences' : '/admin/conferences/' . (int)$item['id']) ?>">
    <label>Name *</label>
    <input name="name" value="<?= esc($item['name'] ?? '') ?>" required>

    <label>Slug *</label>
    <input name="slug" value="<?= esc($item['slug'] ?? '') ?>" required>

    <label>Start date</label>
    <input type="date" name="start_date" value="<?= esc($item['start_date'] ?? '') ?>">

    <label>End date</label>
    <input type="date" name="end_date" value="<?= esc($item['end_date'] ?? '') ?>">

    <label>Venue</label>
    <input name="venue" value="<?= esc($item['venue'] ?? '') ?>">

    <button type="submit"><?= esc($mode === 'create' ? 'Create' : 'Save') ?></button>
    <a class="btn" href="/admin/conferences">Cancel</a>
  </form>
</div>

<?= $this->endSection() ?>
