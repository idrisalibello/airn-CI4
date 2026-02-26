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
  <h3 style="margin-top:0;">Payment</h3>

  <?php $ps = (string)($payment_status ?? 'UNPAID'); ?>

  <?php if ($ps === 'PAID'): ?>
    <div class="pill ok">PAID</div>
    <div style="margin-top:8px;font-size:13px;color:#666;">
      Reference: <?= esc((string)($payment['reference'] ?? '')) ?><br>
      Paid at: <?= esc((string)($payment['paid_at'] ?? '')) ?>
    </div>

  <?php elseif ($ps === 'PENDING'): ?>
    <div class="pill">PENDING</div>
    <p>Payment started but not yet confirmed.</p>
    <a class="btn" href="/author/submissions/<?= (int)$sub['id'] ?>/pay">
      Continue Payment
    </a>

  <?php else: ?>
    <div class="pill no">UNPAID</div>
    <p><strong>Warning:</strong> Only PAID submissions will be considered for peer review. Conference submissions without payment will not be processed.</p>
    <a class="btn" href="/author/submissions/<?= (int)$sub['id'] ?>/pay">
      Pay Now
    </a>
  <?php endif; ?>
</div>

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
      <tr>
        <th style="width:220px">Type</th>
        <td><?= esc($sub['type'] ?? '-') ?></td>
      </tr>
      <tr>
        <th>Title</th>
        <td><?= esc($sub['title'] ?? '-') ?></td>
      </tr>
      <?php if (array_key_exists('track', $sub) && !empty($sub['track'])): ?>
        <tr>
          <th>Track</th>
          <td><?= esc($sub['track']) ?></td>
        </tr>
      <?php endif; ?>
      <?php if (array_key_exists('abstract', $sub) && !empty($sub['abstract'])): ?>
        <tr>
          <th>Abstract</th>
        <tr>
          <th>Abstract</th>
          <td><?= nl2br(esc((string)$sub['abstract'])) ?></td>
        </tr>
        </tr>

      <?php endif; ?>
      <?php if (array_key_exists('keywords', $sub) && !empty($sub['keywords'])): ?>
        <tr>
          <th>Keywords</th>
          <td><?= esc($sub['keywords']) ?></td>
        </tr>
      <?php endif; ?>
      <?php if (array_key_exists('current_version_id', $sub)): ?>
        <tr>
          <th>Current version</th>
          <td><?= !empty($sub['current_version_id']) ? ('#' . (int)$sub['current_version_id']) : '-' ?></td>
        </tr>
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
            <?php
            $mpRaw = $v['manuscript_path'] ?? '-';
            $mp = is_string($mpRaw) ? $mpRaw : '-';
            ?>
            <td><?= esc($mp) ?></td>

            <td>
              <?php if (!empty($v['id'])): ?>
                <a class="btn" href="/author/submissions/<?= (int)$sub['id'] ?>/download/<?= (int)$v['id'] ?>">Download</a>
              <?php endif; ?>
              <?php
              $p = (string)($v['manuscript_path'] ?? '');
              $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
              ?>
              <?php if ($ext === 'pdf'): ?>
                <a class="btn" href="/author/submissions/<?= (int)$sub['id'] ?>/view/<?= (int)$v['id'] ?>" target="_blank">View PDF</a>
              <?php endif; ?>
              <a class="btn" href="/author/submissions/<?= (int)$sub['id'] ?>/download/<?= (int)$v['id'] ?>">Download</a>

            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <hr style="border:none;border-top:1px solid #eee;margin:14px 0">
  <div class="card">
    <h3 style="margin-top:0;">Camera-Ready Guide (Required)</h3>
    <p class="muted" style="margin-top:6px;">
      Use this checklist before uploading. Submissions that ignore these rules may be returned for correction.
    </p>

   <div class="card">
  <h3 style="margin-top:0;">Camera-Ready Manuscript Guide</h3>
  <p class="muted" style="color:red">
    Please ensure your manuscript strictly follows the formatting requirements below before upload.
    Non-compliant files may delay review or publication.
  </p>

  <details style="margin-top:10px;">
    <summary style="cursor:pointer;font-weight:600;">
      View formatting requirements
    </summary>

    <div style="margin-top:12px; line-height:1.6;">
      <ul style="padding-left:18px;">
        <li><strong>File Format:</strong> DOCX (preferred) or PDF.</li>
        <li><strong>Paper Size:</strong> A4 (210mm × 297mm).</li>
        <li><strong>Margins:</strong> 1 inch (2.54 cm) on all sides.</li>
        <li><strong>Font:</strong> Times New Roman.</li>
        <li><strong>Font Size:</strong> 12pt (body), 14pt bold (section headings).</li>
        <li><strong>Line Spacing:</strong> 1.5 spacing.</li>
        <li><strong>Alignment:</strong> Justified text.</li>
        <li><strong>Figures & Tables:</strong> Must be clear, numbered, and referenced in text.</li>
        <li><strong>References:</strong> Follow required style (APA / IEEE / Chicago as applicable).</li>
        <li><strong>No headers/footers:</strong> Publication metadata will be added by the editor.</li>
      </ul>

      <div style="margin-top:12px;">
        <a class="btn" href="/templates/AIRN-Camera-Ready-Template.docx">
          Download Official Template (DOCX)
        </a>
      </div>
    </div>
  </details>
</div>

  <h4 style="margin:0 0 10px">Upload new version</h4>
  <form method="post" action="/author/submissions/<?= (int)$sub['id'] ?>/upload" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="file" name="manuscript" accept=".pdf,.doc,.docx" required>
    <button type="submit">Upload</button>
  </form>
</div>

<?= $this->endSection() ?>