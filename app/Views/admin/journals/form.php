<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<h2><?= esc($mode === 'create' ? 'New Journal' : 'Edit Journal') ?></h2>

<?php if (!empty($error)): ?>
  <div class="card err"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" action="<?= esc($mode === 'create' ? '/admin/journals' : '/admin/journals/' . (int)$item['id']) ?>">
    <label>Name *</label>
    <input name="name" value="<?= esc($item['name'] ?? '') ?>" required>

    <label>Slug *</label>
    <input name="slug" value="<?= esc($item['slug'] ?? '') ?>" required>

    <label>ISSN</label>
    <input name="issn" value="<?= esc($item['issn'] ?? '') ?>">

    <label>Description</label>
    <textarea name="description" rows="5"><?= esc($item['description'] ?? '') ?></textarea>

    <button type="submit"><?= esc($mode === 'create' ? 'Create' : 'Save') ?></button>
    <a class="btn" href="/admin/journals">Cancel</a>
  </form>
</div>

<?= $this->endSection() ?>
