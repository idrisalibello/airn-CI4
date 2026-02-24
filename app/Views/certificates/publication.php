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
            margin: 0;
        }

        html,
        body {
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

        * {
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        /* Outer container constrained to A4 minus safe margins */
        .frame {
            width: 291mm;
            height: 195mm;
            border: 2px solid #0B3D91;
            padding: 2mm;
            background: #fff;
            position: relative;
        }

        .inner {
            border: 1px solid #d7e0f2;
            padding: 4mm;
            /* was 5mm */
            height: 94%;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Banner */
        .banner {
            height: 5mm;
            background: #0B3D91;
            border-radius: 3mm;
            margin-bottom: 3mm;
        }

        /* Watermark */
        .wm {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 260px;
            /* Increase size */
            font-weight: 900;
            color: #0B3D91;
            opacity: 0.06;
            /* Keep light */
            letter-spacing: 20px;
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }

        .content {
            position: relative;
            z-index: 1;
            flex-grow: 1;
        }

        /* Header */
        .hdr td {
            vertical-align: top;
        }

        .org {
            font-weight: 900;
            font-size: 18px;
            color: #0B3D91;
            line-height: 1.1;
        }

        .tagline {
            font-size: 11px;
            color: #333;
            margin-top: 1mm;
        }

        .meta {
            text-align: right;
            font-size: 10.5px;
            color: #333;
            line-height: 1.25;
        }

        .mono {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 9px;
            white-space: normal;
            word-break: break-all;
        }

        /* Titles (tightened) */
        .center {
            text-align: center;
        }

        .t1 {
            margin-top: 4mm;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 2px;
        }

        /* was 5mm / 32px */
        .t2 {
            margin-top: 1.5mm;
            font-size: 13px;
            color: #444;
        }

        /* was 2mm / 14px */

        /* Recipient (tightened) */
        .awarded {
            margin-top: 6mm;
            font-size: 13px;
            color: #333;
        }

        /* was 8mm / 14px */
        .name {
            margin-top: 2mm;
            font-size: 28px;
            /* was 32px */
            font-weight: 900;
            letter-spacing: .5px;
            border-bottom: 1px double #d7e0f2;
            display: inline-block;
            padding-bottom: 1.5mm;
            /* was 2mm */
        }

        /* Article box (tightened) */
        .paperBox {
            margin: 6mm auto 0;
            /* was 8mm */
            width: 90%;
            border: 1px solid #d7e0f2;
            background: #f5f7fc;
            padding: 5mm;
            /* was 6mm */
            text-align: center;
        }

        .paperBox .lbl {
            font-size: 11px;
            font-weight: 900;
            color: #333;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2.5mm;
            /* was 3mm */
        }

        .paperBox .paper {
            font-size: 18px;
            font-weight: 900;
            line-height: 1.25;
            margin: 0;
            word-break: break-word;
        }

        /* Details + Seal row (tightened) */
        .row2 {
            width: 90%;
            margin: 6mm auto 0;
        }

        /* was 8mm */
        .row2 td {
            vertical-align: middle;
        }

        .details {
            font-size: 12px;
        }

        .details div {
            margin-bottom: 1.6mm;
        }

        /* was 2mm */
        .details strong {
            display: inline-block;
            width: 32mm;
            font-weight: 900;
            color: #222;
        }

        /* SEAL (smaller so it doesn't push content down) */
        .sealWrap {
            text-align: right;
        }

        .seal {
            display: inline-block;
            width: 100px;
            /* was 120px */
            height: 100px;
            /* was 120px */
            border-radius: 999px;
            border: 4px solid rgba(11, 61, 145, 0.9);
            background: rgba(11, 61, 145, 0.05);
            text-align: center;
            color: #0B3D91;
            font-weight: 900;
            padding-top: 14px;
            /* was 18px */
        }

        .sealTop {
            font-size: 10px;
            letter-spacing: 1px;
        }

        .sealMid {
            margin-top: 4px;
            font-size: 15px;
            letter-spacing: .5px;
        }

        .sealStars {
            margin-top: 3px;
            font-size: 12px;
            letter-spacing: 3px;
        }

        .sealBrand {
            margin-top: 3px;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .sealCode {
            margin-top: 4px;
            font-size: 9px;
            line-height: 1.1;
            padding: 0 8px;
        }

        /* Signatures (tightened) */
        .sign {
            width: 90%;
            margin: 7mm auto 0;
        }

        /* was 10mm */
        .sig td {
            vertical-align: top;
        }

        .sigLineLeft {
            border-top: 1px solid #666;
            padding-top: 2mm;
            width: 85%;
        }

        .sigLineRightWrap {
            text-align: right;
        }

        .sigLineRight {
            border-top: 1px solid #666;
            padding-top: 2mm;
            width: 85%;
            display: inline-block;
            text-align: right;
        }

        /* TEST SIGNATURES */
        .sigMark {
            height: 22px;
            margin-bottom: 1.2mm;
        }

        .sigMark img {
            height: 22px;
        }

        .role {
            font-weight: 900;
            font-size: 12px;
            color: #222;
        }

        .who {
            font-weight: 900;
            font-size: 13px;
            margin-top: 1mm;
        }

        .org2 {
            font-size: 11px;
            color: #333;
            margin-top: .6mm;
        }

        /* Footer tight at bottom */
        .footer {
            width: 100%;
            margin-top: auto;
            border-top: 1px solid #d7e0f2;
            padding-top: 1.5mm;
            /* was 2mm */
            font-size: 10px;
            /* was 10.5px */
            color: #333;
        }

        .footer .r {
            text-align: right;
        }
    </style>

</head>

<body>
    <div class="frame">
        <div class="inner">
            <div class="banner"></div>
            <div class="wm">AIRN JOURNAL</div>

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
                    <div class="t1">CERTIFICATE OF JOURNAL PUBLICATION</div>
                    <div class="t2">Issued in recognition of an official scholarly publication</div>
                </div>

                <div class="center awarded">This certificate is hereby awarded to</div>
                <div class="center"><span class="name"><?= esc($authors ?? 'Author') ?></span></div>

                <div class="paperBox">
                    <div class="lbl">Published Journal Article Title</div>
                    <p class="paper">“<?= esc($paper_title ?? '-') ?>”</p>
                </div>

                <!-- <div class="center" style="margin-top:3mm; font-size:12px; color:#333;">
                    <strong>Authors:</strong> <?= esc($authors ?? ($recipient_name ?? '')) ?>
                </div> -->


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

                <?php
                $leftPath  = FCPATH . 'assets/signatures/signature.png';
                $rightPath = FCPATH . 'assets/signatures/signature1.png';

                $leftData  = is_file($leftPath)
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($leftPath))
                    : '';

                $rightData = is_file($rightPath)
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($rightPath))
                    : '';
                ?>

                <div class="sign">
                    <table class="sig">
                        <tr>
                            <!-- LEFT -->
                            <td style="width:50%;">

                                <?php if ($leftData): ?>
                                    <div style="height:34px; margin-bottom:2px;">
                                        <img src="<?= $leftData ?>" style="height:30px;">
                                    </div>
                                <?php endif; ?>

                                <div class="sigLineLeft">
                                    <div class="role">Editor-in-Chief</div>
                                    <div class="who"><?= esc($editor_name ?? 'Dr. Sam Ebute') ?></div>
                                    <div class="org2"><?= esc($brand_left ?? 'AIRN') ?></div>
                                </div>

                            </td>

                            <!-- RIGHT -->
                            <td style="width:50%;" class="sigLineRightWrap">

                                <?php if ($rightData): ?>
                                    <div style="height:34px; margin-bottom:2px; text-align:right;">
                                        <img src="<?= $rightData ?>" style="height:30px;">
                                    </div>
                                <?php endif; ?>

                                <div class="sigLineRight">
                                    <div class="role">Managing Editor</div>
                                    <div class="who"><?= esc($managing_editor_name ?? 'Prof. Philip Alex') ?></div>
                                    <div class="org2">Academic &amp; International Research Network</div>
                                </div>

                            </td>
                        </tr>
                    </table>
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