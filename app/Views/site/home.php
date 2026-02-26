<?= $this->extend('site/_layout') ?>
<?= $this->section('content') ?>

<style>
  /* Home-only polish. Safe, restrained. */
  .hero{
    padding: 18px 18px;
    margin-bottom: 14px;
  }
  .hero h2{ margin: 0 0 6px; }
  .hero .muted{ margin: 0 0 12px; }

  .actions{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-top: 10px;
  }

  .grid{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap:12px;
    margin-top: 12px;
  }
  .mini{
    border:1px solid rgba(0,0,0,.08);
    border-radius:10px;
    padding:12px 12px;
    background: rgba(0,0,0,.02);
  }
  .mini b{ display:block; margin-bottom:4px; }
  .mini .muted{ margin:0; font-size: .95em; line-height:1.35; }

  .split{
    display:grid;
    grid-template-columns: 1.2fr .8fr;
    gap:12px;
    align-items:start;
  }

  .note{
    border-left: 4px solid rgba(0,0,0,.12);
    padding: 8px 10px;
    background: rgba(0,0,0,.02);
    border-radius: 10px;
  }
  .note .muted{ margin:0; }

  /* If your layout is narrow on mobile */
  @media (max-width: 860px){
    .grid{ grid-template-columns: 1fr; }
    .split{ grid-template-columns: 1fr; }
  }
</style>

<div class="card hero">
  <h2>Academic &amp; International Research Network</h2>
  <p class="muted">Journals, conferences, and published proceedings in one network.</p>

  <div class="actions">
    <a class="btn" href="/journals">Browse Journals</a>
    <a class="btn" href="/conferences">Browse Conferences</a>
    <a class="btn" href="/published">Browse Published</a>
  </div>

  <div class="grid">
    <div class="mini">
      <b>Journals</b>
      <p class="muted">Submit manuscripts, follow peer review, and publish accepted articles.</p>
    </div>
    <div class="mini">
      <b>Conferences</b>
      <p class="muted">Conference calls, submissions, decisions, and proceedings publication.</p>
    </div>
    <div class="mini">
      <b>Published</b>
      <p class="muted">Browse finalized publications and proceedings in one catalogue.</p>
    </div>
  </div>
</div>

<div class="card">
  <h3>Announcements</h3>
  <p class="muted">Latest journal calls and conference notices.</p>

  <div class="split">
    <!-- Journal announcements -->
    <div>
      <h4 style="margin:0 0 10px;">Journal Announcements</h4>

      <?php if (!empty($journalAnnouncements) && is_array($journalAnnouncements)): ?>
        <div class="grid" style="grid-template-columns: 1fr; gap:10px;">
          <?php foreach ($journalAnnouncements as $j): ?>
            <div class="mini">
              <b style="margin-bottom:6px;">
                <a href="<?= esc($j['url'] ?? '#') ?>">
                  <?= esc($j['title'] ?? 'Untitled') ?>
                </a>
              </b>

              <?php if (!empty($j['summary'])): ?>
                <p class="muted"><?= esc($j['summary']) ?></p>
              <?php endif; ?>

              <p class="muted" style="margin-top:8px;">
                <?php if (!empty($j['deadline'])): ?>
                  <span><b>Deadline:</b> <?= esc($j['deadline']) ?></span>
                <?php endif; ?>
                <?php if (!empty($j['meta'])): ?>
                  <?php if (!empty($j['deadline'])): ?> · <?php endif; ?>
                  <span><?= esc($j['meta']) ?></span>
                <?php endif; ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="note"><p class="muted">No journal announcements yet.</p></div>
      <?php endif; ?>

      <div class="actions" style="margin-top:10px;">
        <a class="btn" href="/journals">View all journals</a>
      </div>
    </div>

    <!-- Conference announcements -->
    <div>
      <h4 style="margin:0 0 10px;">Conference Announcements</h4>

      <?php if (!empty($conferenceAnnouncements) && is_array($conferenceAnnouncements)): ?>
        <div class="grid" style="grid-template-columns: 1fr; gap:10px;">
          <?php foreach ($conferenceAnnouncements as $c): ?>
            <div class="mini">
              <b style="margin-bottom:6px;">
                <a href="<?= esc($c['url'] ?? '#') ?>">
                  <?= esc($c['title'] ?? 'Untitled') ?>
                </a>
              </b>

              <?php if (!empty($c['summary'])): ?>
                <p class="muted"><?= esc($c['summary']) ?></p>
              <?php endif; ?>

              <p class="muted" style="margin-top:8px;">
                <?php if (!empty($c['deadline'])): ?>
                  <span><b>Deadline:</b> <?= esc($c['deadline']) ?></span>
                <?php endif; ?>
                <?php if (!empty($c['meta'])): ?>
                  <?php if (!empty($c['deadline'])): ?> · <?php endif; ?>
                  <span><?= esc($c['meta']) ?></span>
                <?php endif; ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="note"><p class="muted">No conference announcements yet.</p></div>
      <?php endif; ?>

      <div class="actions" style="margin-top:10px;">
        <a class="btn" href="/conferences">View all conferences</a>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="split">
    <div>
      <h3>For Authors</h3>
      <p class="muted">Submit papers, track review status, and download decision letters.</p>

      <?php $auth = session('auth_user'); ?>
      <div class="actions">
        <?php if (!$auth): ?>
          <a class="btn" href="/register">Create Author Account</a>
          <a class="btn" href="/login">Login</a>
          <a class="btn" href="/author/submissions/new">Submit Manuscript</a>
        <?php else: ?>
          <a class="btn" href="/author/submissions/new">Submit Manuscript</a>
          <a class="btn" href="/author">Go to Dashboard</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="note">
      <?php if (!$auth): ?>
        <p class="muted">
          Submitting requires login. If you start a submission while signed out, you will be redirected to login.
        </p>
      <?php else: ?>
        <p class="muted">
          You are signed in. Use the dashboard to monitor submissions and respond to decisions.
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <h3>For Reviewers &amp; Editors</h3>
  <p class="muted">Structured peer review, editorial decisions, and publication workflow.</p>

  <div class="grid">
    <div class="mini">
      <b>Review workflow</b>
      <p class="muted">Assigned reviews, deadlines, and clear decision recommendations.</p>
    </div>
    <div class="mini">
      <b>Editorial decisions</b>
      <p class="muted">Track revisions, send letters, and move items to publication.</p>
    </div>
    <div class="mini">
      <b>Proceedings &amp; issues</b>
      <p class="muted">Publication-ready outputs for conferences and journal issues.</p>
    </div>
  </div>
</div>

<?= $this->endSection() ?>