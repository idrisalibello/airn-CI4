<?= $this->extend('admin/_layout') ?>
<?= $this->section('content') ?>

<?php
  /**
   * Admin Submission Show
   * Variables passed by App\Controllers\Admin\SubmissionsController::show():
   * - $sub (array)
   * - $versions (array)
   * - $publication (array|null)
   * - $decision (array|null)
   * - $certificate (array|null)
   * - $presentation_certificate (array|null)
   * - $flash (string|null)
   * - $error (string|null)
   */

  $subId = (int)($sub['id'] ?? 0);

  $typeRaw   = $sub['type'] ?? '';
  $statusRaw = $sub['status'] ?? '';
  $type   = is_string($typeRaw) ? $typeRaw : '';
  $status = is_string($statusRaw) ? $statusRaw : '';

  $headline = trim($type . ' • ' . $status, " \t\n\r\0\x0B•");

  $dec = $decision ?? null;
  $decText = (is_array($dec) && isset($dec['decision'])) ? (string)$dec['decision'] : '';

  // Gate: accepted by status OR latest decision
  $isAccepted = ($status === 'accepted') || ($decText === 'accept');

  $presCert = $presentation_certificate ?? null;
?>

<div class="card">
  <div class="row" style="justify-content:space-between; align-items:center;">
    <div>
      <h2 style="margin:0;">Submission #<?= $subId ?></h2>
      <p class="muted" style="margin:6px 0 0;"><?= esc($headline !== '' ? $headline : '-') ?></p>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
      <a class="btn" href="<?= site_url('admin/submissions') ?>">Back</a>
    </div>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="card flash" style="margin-top:14px;"><?= esc((string)$flash) ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="card err" style="margin-top:14px;"><?= esc((string)$error) ?></div>
  <?php endif; ?>

  <hr>

  <h3>Versions</h3>
  <?php if (empty($versions) || !is_array($versions)): ?>
    <p class="muted">No versions.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th style="width:120px;">Version</th>
          <th>File</th>
          <th style="width:220px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($versions as $v): ?>
          <?php
            $vId = (int)($v['id'] ?? 0);
            $vNo = (int)($v['version_no'] ?? 0);

            $mpRaw = $v['manuscript_path'] ?? '';
            $mp = is_string($mpRaw) ? $mpRaw : '';
            $ext = $mp !== '' ? strtolower((string)pathinfo($mp, PATHINFO_EXTENSION)) : '';
          ?>
          <tr>
            <td>v<?= $vNo ?></td>
            <td><code><?= esc($mp !== '' ? $mp : '-') ?></code></td>
            <td>
              <?php if ($ext === 'pdf'): ?>
                <a class="btn" target="_blank" href="<?= site_url("admin/submissions/{$subId}/view/{$vId}") ?>">View PDF</a>
              <?php endif; ?>
              <a class="btn" href="<?= site_url("admin/submissions/{$subId}/download/{$vId}") ?>">Download</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <hr>

  <h3>Decision</h3>

  <?php if (!empty($decision) && is_array($decision)): ?>
    <p>
      <strong>Latest:</strong> <?= esc((string)$decision['decision']) ?>
      <span class="muted">at <?= esc((string)($decision['created_at'] ?? '-')) ?></span>
    </p>
    <?php if (!empty($decision['letter_text'])): ?>
      <div class="card" style="margin-top:10px;">
        <div class="muted">Letter</div>
        <div><?= nl2br(esc((string)$decision['letter_text'])) ?></div>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <p class="muted">No decision recorded yet.</p>
  <?php endif; ?>

  <?php if (empty($publication)): ?>
    <form method="post" action="<?= site_url("admin/submissions/{$subId}/decide") ?>" style="margin-top:12px;">
      <?= csrf_field() ?>
      <label>Decision</label>
      <select name="decision" required>
        <option value="">Select…</option>
        <option value="accept">Accept</option>
        <option value="revise">Request Revision</option>
        <option value="reject">Reject</option>
      </select>

      <label>Decision letter (optional)</label>
      <textarea name="letter_text" rows="4" placeholder="Message to author (optional)"></textarea>

      <button class="btn" type="submit">Save Decision</button>
    </form>
  <?php else: ?>
    <p class="muted">Decision is locked after publication.</p>
  <?php endif; ?>

  <hr>

  <?php if (!empty($publication) && is_array($publication)): ?>
    <h3>Publication</h3>
    <p><strong>Published:</strong> <?= esc((string)($publication['published_at'] ?? '-')) ?></p>
    <p><strong>DOI:</strong> <?= esc((string)($publication['doi'] ?? '-')) ?></p>
    <p><strong>Vol/Issue:</strong> <?= esc((string)($publication['volume'] ?? '-')) ?> / <?= esc((string)($publication['issue'] ?? '-')) ?></p>
    <p><strong>Pages:</strong> <?= esc((string)($publication['pages'] ?? '-')) ?></p>

    <div style="display:flex; gap:8px; align-items:center; margin-top:10px; flex-wrap:wrap;">
      <a class="btn" href="<?= site_url("admin/submissions/{$subId}/certificate") ?>">Download Certificate</a>
      <?php if (!empty($certificate) && is_array($certificate)): ?>
        <a class="btn" target="_blank" href="<?= site_url('verify/certificate/'.(string)$certificate['code']) ?>">Verify Link</a>
      <?php endif; ?>
      <a class="btn" href="<?= site_url('download/'.$subId) ?>">Download Published PDF</a>
    </div>

  <?php else: ?>

    <?php if ($type === 'conference'): ?>
      <h3>Presentation Certificate</h3>

      <?php if (!empty($presCert) && is_array($presCert)): ?>
        <div style="display:flex; gap:8px; align-items:center; margin-top:10px; flex-wrap:wrap;">
          <a class="btn" href="<?= site_url("admin/submissions/{$subId}/presentation-certificate") ?>">Download Presentation Certificate</a>
          <a class="btn" target="_blank" href="<?= site_url('verify/certificate/'.(string)$presCert['code']) ?>">Verify Link</a>
        </div>
      <?php else: ?>
        <?php if (!$isAccepted): ?>
          <div class="card err" style="margin:10px 0;">
            Issuing is disabled until the submission is <strong>accepted</strong>.
          </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url("admin/submissions/{$subId}/present") ?>" style="margin-top:10px;">
          <?= csrf_field() ?>
          <button class="btn" type="submit" <?= $isAccepted ? '' : 'disabled' ?>>Mark as Presented &amp; Issue Certificate</button>
        </form>
      <?php endif; ?>

    <?php else: ?>
      <h3>Publish</h3>

      <?php if (!$isAccepted): ?>
        <div class="card err" style="margin:10px 0;">
          Publishing is disabled until the submission is <strong>accepted</strong>.
        </div>
      <?php endif; ?>

      <div class="card" style="margin:10px 0;">
        <div class="muted" style="margin-bottom:6px;">Camera-ready PDF</div>
        <div style="font-size:13px; line-height:1.45;">
          Admin uploads the <strong>final formatted PDF</strong> received from the author/editorial process.
          Publishing will apply journal stamping and save the final published copy.
        </div>
      </div>

      <form method="post" action="<?= site_url("admin/submissions/{$subId}/publish") ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <label>Volume</label>
        <input name="volume" placeholder="e.g. 1" required>

        <label>Issue</label>
        <input name="issue" placeholder="e.g. 2" required>

        <label>Pages</label>
        <input name="pages" placeholder="e.g. 12-19" required>

        <label>DOI</label>
        <input name="doi" placeholder="optional">

        <label>Camera-Ready PDF</label>
        <input type="file" name="camera_ready_pdf" accept="application/pdf" required>
        <p style="margin:6px 0 0; font-size:12px; color:#444;">
          Upload the final PDF (content/diagrams/equations). Publishing will stamp header/footer/DOI/page numbers.
        </p>

        <button class="btn" type="submit" <?= $isAccepted ? '' : 'disabled' ?>>Publish &amp; Issue Certificate</button>
      </form>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?= $this->endSection() ?>
