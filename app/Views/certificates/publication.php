<?php
// app/Views/certificates/publication.php
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    /* A4 Landscape Sizing: 297mm x 210mm */
    @page { 
      size: A4 landscape; 
      margin: 0; /* Margin handled by body padding for better engine compatibility */
    }

    html, body { 
      margin: 0; 
      padding: 0; 
      width: 297mm; 
      height: 210mm;
      overflow: hidden;
    }

    body { 
      font-family: DejaVu Sans, Arial, sans-serif; 
      font-size: 12px; 
      color: #111; 
      line-height: 1.2; 
      display: flex;
      justify-content: center;
      align-items: center;
    }

    * { box-sizing: border-box; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }

    /* The outer container constrained to A4 minus safe margins (approx 3mm each side) */
    .frame { 
      width: 291mm; 
      height: 204mm; 
      border: 2px solid #0B3D91; 
      padding: 2mm; 
      background: #fff; 
      position: relative;
    }

    .inner { 
      border: 1px solid #d7e0f2; 
      padding: 5mm; 
      height: 100%;
      position: relative; 
      display: flex;
      flex-direction: column;
    }

    /* Banner */
    .banner { height: 5mm; background: #0B3D91; border-radius: 3mm; margin-bottom: 4mm; }

    /* Watermark */
    .wm {
      position: absolute; left: 0; right: 0;
      top: 45%;
      transform: translateY(-50%);
      text-align: center;
      font-size: 110px;
      font-weight: 900;
      color: #0B3D91;
      opacity: 0.045;
      letter-spacing: 15px;
      z-index: 0;
    }
    .content { position: relative; z-index: 1; flex-grow: 1; }

    /* Header */
    .hdr td { vertical-align: top; }
    .org { font-weight: 900; font-size: 18px; color: #0B3D91; line-height: 1.1; }
    .tagline { font-size: 11px; color: #333; margin-top: 1mm; }
    .meta { text-align: right; font-size: 10.5px; color: #333; line-height: 1.25; }

    .mono {
      font-family: DejaVu Sans Mono, monospace;
      font-size: 9px;
      white-space: normal;
      word-break: break-all;
    }

    /* Titles */
    .center { text-align: center; }
    .t1 { margin-top: 5mm; font-size: 32px; font-weight: 900; letter-spacing: 2px; }
    .t2 { margin-top: 2mm; font-size: 14px; color: #444; }

    /* Recipient */
    .awarded { margin-top: 8mm; font-size: 14px; color: #333; }
    .name { margin-top: 3mm; font-size: 32px; font-weight: 900; letter-spacing: .5px; border-bottom: 1px double #d7e0f2; display: inline-block; padding-bottom: 2mm; }

    /* Article box */
    .paperBox {
      margin: 8mm auto 0;
      width: 90%;
      border: 1px solid #d7e0f2;
      background: #f5f7fc;
      padding: 6mm;
      text-align: center;
    }
    .paperBox .lbl {
      font-size: 11px; font-weight: 900; color: #333;
      letter-spacing: 1px; text-transform: uppercase;
      margin-bottom: 3mm;
    }
    .paperBox .paper {
      font-size: 18px; font-weight: 900; line-height: 1.3; margin: 0;
      word-break: break-word;
    }

    /* Details + Seal row */
    .row2 { width: 90%; margin: 8mm auto 0; }
    .row2 td { vertical-align: middle; }

    .details { font-size: 12px; }
    .details div { margin-bottom: 2mm; }
    .details strong {
      display: inline-block;
      width: 32mm;
      font-weight: 900;
      color: #222;
    }

    /* SEAL */
    .sealWrap { text-align: right; }
    .seal {
      display: inline-block;
      width: 120px;
      height: 120px;
      border-radius: 999px;
      border: 4px solid rgba(11,61,145,0.9);
      background: rgba(11,61,145,0.05);
      text-align: center;
      color: #0B3D91;
      font-weight: 900;
      padding-top: 18px;
    }
    .sealTop { font-size: 10px; letter-spacing: 1px; }
    .sealMid { margin-top: 5px; font-size: 16px; letter-spacing: .5px; }
    .sealStars { margin-top: 4px; font-size: 12px; letter-spacing: 3px; }
    .sealBrand { margin-top: 4px; font-size: 11px; letter-spacing: 1px; }
    .sealCode { margin-top: 5px; font-size: 9px; line-height: 1.1; padding: 0 8px; }

    /* Signatures */
    .sign { width: 90%; margin: 10mm auto 0; }
    .sig td { vertical-align: top; }

    .sigLineLeft { border-top: 1px solid #666; padding-top: 2mm; width: 85%; }
    .sigLineRightWrap { text-align: right; }
    .sigLineRight { border-top: 1px solid #666; padding-top: 2mm; width: 85%; display: inline-block; text-align: right; }

    .role { font-weight: 900; font-size: 12px; color: #222; }
    .who { font-weight: 900; font-size: 13px; margin-top: 1mm; }
    .org2 { font-size: 11px; color: #333; margin-top: .6mm; }

    /* Footer tight at bottom */
    .footer {
      width: 100%;
      margin-top: auto;
      border-top: 1px solid #d7e0f2;
      padding-top: 2mm;
      font-size: 10.5px;
      color: #333;
    }
    .footer .r { text-align: right; }
  </style>
</head>
<body>
  <div class="frame">
    <div class="inner">
      <div class="banner"></div>
      <div class="wm">AIRN</div>

      <div class="content">
        <table class="hdr">
          <tr>
            <td>
              <div class="org"><?= esc($brand_left ?? 'AIRN') ?></div>
              <div class="tagline">Academic &amp; International Research Network</div>
            </td>
            <td class="meta">
              <div><?= esc($brand_right ?? '') ?></div>
              <div>Certificate ID: <span class="mono"><?= esc($code ?? '-') ?></span></div>
            </td>
          </tr>
        </table>

        <div class="center">
          <div class="t1">CERTIFICATE OF PUBLICATION</div>
          <div class="t2">Issued in recognition of an official scholarly publication</div>
        </div>

        <div class="center awarded">This certificate is hereby awarded to</div>
        <div class="center"><span class="name"><?= esc($recipient_name ?? 'Author') ?></span></div>

        <div class="paperBox">
          <div class="lbl">Published Article Title</div>
          <p class="paper">“<?= esc($paper_title ?? '-') ?>”</p>
        </div>

        <?php
          $pubDate = !empty($published_at) ? date('d M Y', strtotime($published_at)) : '-';
          $token = $verify_short ?? ($verify_url ?? '-');
        ?>

        <table class="row2">
          <tr>
            <td style="width:70%;">
              <div class="details">
                <div><strong>Pub Date</strong> <?= esc($pubDate) ?></div>
                <div><strong>Vol/Iss/Pg</strong> <?= esc(($volume ?: '-') . ' / ' . ($issue ?: '-') . ' / ' . ($pages ?: '-')) ?></div>
                <div><strong>DOI</strong> <span class="mono"><?= esc($doi ?: '-') ?></span></div>
                <div><strong>Verify</strong> <span class="mono"><?= esc($token) ?></span></div>
              </div>
            </td>

            <td style="width:30%;" class="sealWrap">
              <div class="seal">
                <div class="sealTop">OFFICIAL SEAL</div>
                <div class="sealMid">VERIFIED</div>
                <div class="sealStars">★ ★ ★</div>
                <div class="sealBrand">AIRN</div>
                <div class="sealCode"><?= esc($code ?? '-') ?></div>
              </div>
            </td>
          </tr>
        </table>

        <div class="sign">
          <table class="sig">
            <tr>
              <td style="width:50%;">
                <div class="sigLineLeft">
                  <div class="role">Editor-in-Chief</div>
                  <div class="who"><?= esc($editor_name ?? 'Dr. Sam Ebute') ?></div>
                  <div class="org2"><?= esc($brand_left ?? 'AIRN') ?></div>
                </div>
              </td>

              <td style="width:50%;" class="sigLineRightWrap">
                <div class="sigLineRight">
                  <div class="role">Managing Editor</div>
                  <div class="who"><?= esc($managing_editor_name ?? 'Prof. Philip Alex') ?></div>
                  <div class="org2">Academic &amp; International Research Network</div>
                </div>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <div class="footer">
        <table>
          <tr>
            <td>Verify using the token or Certificate ID on the AIRN verification page.</td>
            <td class="r mono"><?= esc($code ?? '-') ?></td>
          </tr>
        </table>
      </div>

    </div>
  </div>
</body>
</html>