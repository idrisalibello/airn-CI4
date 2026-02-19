<?= $this->extend('author/_layout') ?>

<?= $this->section('content') ?>

<h2>New Submission</h2>

<?php if (!empty($error)): ?>
  <div class="card err"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" action="/author/submissions" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <label>Submission type</label>
    <?php $type = $old['type'] ?? 'journal'; ?>
    <select name="type" id="typeSel" onchange="toggleType()">
      <option value="journal" <?= $type === 'journal' ? 'selected' : '' ?>>Journal</option>
      <option value="conference" <?= $type === 'conference' ? 'selected' : '' ?>>Conference</option>
    </select>

    <div id="journalBox">
      <label>Journal</label>
      <select name="journal_id">
        <option value="">-- Select journal --</option>
        <?php foreach (($journals ?? []) as $j): ?>
          <option value="<?= (int)$j['id'] ?>" <?= ((string)($old['journal_id'] ?? '') === (string)$j['id']) ? 'selected' : '' ?>>
            <?= esc($j['name'] ?? ('Journal #' . (int)$j['id'])) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="confBox" style="display:none">
      <label>Conference</label>
      <select name="conference_id">
        <option value="">-- Select conference --</option>
        <?php foreach (($confs ?? []) as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ((string)($old['conference_id'] ?? '') === (string)$c['id']) ? 'selected' : '' ?>>
            <?= esc($c['name'] ?? ('Conference #' . (int)$c['id'])) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Track (optional)</label>
      <input type="text" name="track" value="<?= esc($old['track'] ?? '') ?>" placeholder="e.g. AI, Networks, Security">
    </div>

    <label>Title</label>
    <input type="text" name="title" value="<?= esc($old['title'] ?? '') ?>" required>

    <l<label>Abstract</label>
      <textarea name="abstract" rows="6" placeholder="Abstract..." required><?= esc($old['abstract'] ?? '') ?></textarea>


      <label>Keywords (optional)</label>
      <input type="text" name="keywords" value="<?= esc($old['keywords'] ?? '') ?>" placeholder="comma-separated">

      <label>Note to editor (optional)</label>
      <textarea name="author_note" rows="3" placeholder="Any message for the editorial team..."><?= esc($old['author_note'] ?? '') ?></textarea>

      <label>Manuscript file (PDF, DOC, DOCX)</label>
      <input type="file" name="manuscript" accept=".pdf,.doc,.docx" required>

      <div class="row" style="margin-top:10px">
        <button type="submit">Submit</button>
        <a class="btn" href="/author/submissions">Cancel</a>
      </div>
  </form>
</div>

<script>
  function toggleType() {
    var t = document.getElementById('typeSel').value;
    document.getElementById('journalBox').style.display = (t === 'journal') ? 'block' : 'none';
    document.getElementById('confBox').style.display = (t === 'conference') ? 'block' : 'none';
  }
  toggleType();
</script>

<?= $this->endSection() ?>