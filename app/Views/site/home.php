<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<div class="card">
  <h2>Academic & International Research Network</h2>
  <p class="muted">Journals, conferences, and published proceedings in one network.</p>
  <p>
    <a class="btn" href="/journals">Browse Journals</a>
    <a class="btn" href="/conferences">Browse Conferences</a>
    <a class="btn" href="/published">Browse Published</a>
  </p>
</div>

<div class="card">
  <h3>For Authors</h3>
  <p class="muted">Submit papers, track review status, receive decision letters.</p>
</div>

<div class="card">
  <h3>For Reviewers & Editors</h3>
  <p class="muted">Structured peer review, editorial decisions, and publication workflow.</p>
</div>

<?= $this->endSection() ?>
