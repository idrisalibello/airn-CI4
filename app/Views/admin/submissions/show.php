<?php
// admin/submissions/show.php
// Expected variables (common patterns in your codebase):
// $sub (submission row array)
// $decisions (array of decisions) optional
// $journal (journal row) optional
// $publication (publication row) optional
// If your controller names differ, keep this view and just align the variable names in controller.

function h($v)
{
  return esc((string)$v);
}

$sub = $sub ?? [];
$decisions = $decisions ?? [];
$publication = $publication ?? null;

$status = (string)($sub['status'] ?? '—');
$title  = (string)($sub['title'] ?? '—');
$type   = (string)($sub['type'] ?? 'journal');
$createdAt = (string)($sub['created_at'] ?? '');
$updatedAt = (string)($sub['updated_at'] ?? '');

$manuscriptPath = (string)($sub['manuscript_path'] ?? $sub['file_path'] ?? '');
$manuscriptUrl  = $manuscriptPath !== '' ? base_url('writable/' . ltrim($manuscriptPath, '/')) : '';

$flash = session()->getFlashdata('flash');
$error = session()->getFlashdata('error');
?>
<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<h2>Submission #<?= h($sub['id'] ?? '') ?></h2>

<?php if ($error): ?>
  <div class="card err" style="margin-bottom:12px;"><?= esc($error) ?></div>
<?php endif; ?>
<?php if ($flash): ?>
  <div class="card ok" style="margin-bottom:12px;"><?= esc($flash) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:14px;">
  <div style="display:flex; gap:18px; flex-wrap:wrap;">
    <div style="min-width:260px;">
      <div><strong>Title:</strong> <?= h($title) ?></div>
      <div><strong>Type:</strong> <?= h($type) ?></div>
      <div><strong>Status:</strong> <?= h($status) ?></div>
    </div>
    <div style="min-width:260px;">
      <div><strong>Created:</strong> <?= h($createdAt) ?></div>
      <div><strong>Updated:</strong> <?= h($updatedAt) ?></div>
      <?php if (!empty($sub['submitter_user_id'])): ?>
        <div><strong>Submitter User ID:</strong> <?= h($sub['submitter_user_id']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($sub['abstract'])): ?>
    <hr style="margin:14px 0;">
    <div><strong>Abstract</strong></div>
    <div style="white-space:pre-wrap;"><?= esc((string)$sub['abstract']) ?></div>
  <?php endif; ?>

  <hr style="margin:14px 0;">
  <div>
    <strong>Manuscript:</strong>
    <?php if ($manuscriptPath !== ''): ?>
      <a href="<?= base_url('admin/submissions/' . (int)$sub['id'] . '/download') ?>">Download</a>
      <span style="color:#777;">(stored: <?= h($manuscriptPath) ?>)</span>
    <?php else: ?>
      <span style="color:#b00;">Not uploaded</span>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($decisions)): ?>
  <div class="card" style="margin-bottom:14px;">
    <h3 style="margin-top:0;">Decisions</h3>
    <table style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Date</th>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Decision</th>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Note</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($decisions as $d): ?>
          <tr>
            <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($d['created_at'] ?? '') ?></td>
            <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($d['decision'] ?? '') ?></td>
            <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($d['note'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<div class="card" style="margin-bottom:14px;">
  <h3 style="margin-top:0;">Publish (Journal)</h3>

  <div class="card warn" style="margin:10px 0; padding:12px;">
    <strong>Camera-ready PDF is mandatory.</strong>
    <div style="margin-top:6px;">
      Upload the <b>final author-formatted PDF</b> (A4, portrait). Do not include journal header/page numbers/DOI — the system stamps them during publishing.
    </div>
    <p style="margin:6px 0 0; font-size:12px;">
      <a href="<?= site_url('camera-ready-template') ?>" target="_blank">Download Camera-Ready PDF Template</a>
    </p>
    <ul style="margin:8px 0 0 18px;">
      <li>PDF only</li>
      <li>Must open without password</li>
      <li>All figures/tables embedded</li>
    </ul>
  </div>

  <form method="post"
    action="<?= base_url('admin/submissions/' . (int)($sub['id'] ?? 0) . '/publish') ?>"
    enctype="multipart/form-data">

    <?= csrf_field() ?>

    <div style="display:flex; gap:12px; flex-wrap:wrap;">
      <div style="min-width:180px;">
        <label><strong>Volume</strong></label><br>
        <input type="text" name="volume" value="<?= h(old('volume')) ?>" required style="width:100%; padding:8px;">
      </div>

      <div style="min-width:180px;">
        <label><strong>Issue</strong></label><br>
        <input type="text" name="issue" value="<?= h(old('issue')) ?>" required style="width:100%; padding:8px;">
      </div>

      <div style="min-width:220px;">
        <label><strong>Pages</strong> <span style="color:#777;">(e.g., 12-27)</span></label><br>
        <input type="text" name="pages" value="<?= h(old('pages')) ?>" required style="width:100%; padding:8px;">
      </div>

      <div style="min-width:260px;">
        <label><strong>DOI</strong> <span style="color:#777;">(optional if you assign later)</span></label><br>
        <input type="text" name="doi" value="<?= h(old('doi')) ?>" style="width:100%; padding:8px;">
      </div>
    </div>

    <div style="margin-top:12px;">
      <label for="camera_ready_pdf"><strong>Camera-ready PDF (required)</strong></label><br>
      <input type="file"
        name="camera_ready_pdf"
        id="camera_ready_pdf"
        accept="application/pdf"
        required>
    </div>

    <div style="margin-top:14px;">
      <button type="submit" class="btn btn-primary">Publish Now</button>
      <a href="<?= base_url('admin/submissions') ?>" class="btn">Back</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>