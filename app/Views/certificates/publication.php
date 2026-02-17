<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 28px 32px;
        }

        body {
            font-family: Arial, sans-serif;
            color: #111;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Outer certificate frame (standard certificate look) */
        .frame {
            border: 2px solid #0B3D91;
            padding: 18px;
            position: relative;
            min-height: 1000px;
            /* gives breathing room on A4 */
        }

        /* Inner thin line */
        .inner {
            border: 1px solid #d7e0f2;
            padding: 18px 18px 14px;
            min-height: 960px;
            position: relative;
        }

        /* Header: emblem + institution */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logoBox {
            width: 52px;
            height: 52px;
            border: 2px solid #0B3D91;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #0B3D91;
            font-size: 16px;
        }

        .org {
            line-height: 1.2;
        }

        .org .name {
            font-size: 16px;
            font-weight: 800;
            color: #0B3D91;
            letter-spacing: .2px;
        }

        .org .sub {
            font-size: 12px;
            color: #333;
        }

        .metaRight {
            text-align: right;
            font-size: 11px;
            color: #333;
        }

        .mono {
            font-family: "Courier New", monospace;
        }

        /* Big certificate title */
        .certTitle {
            margin-top: 26px;
            text-align: center;
            font-size: 34px;
            font-weight: 900;
            letter-spacing: .6px;
        }

        .certSubtitle {
            text-align: center;
            margin-top: 6px;
            font-size: 13px;
            color: #333;
        }

        /* Recipient */
        .presented {
            margin-top: 26px;
            text-align: center;
            font-size: 13px;
            color: #333;
        }

        .recipient {
            margin-top: 10px;
            text-align: center;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: .3px;
        }

        /* Statement block */
        .statement {
            margin: 18px auto 0;
            max-width: 620px;
            text-align: center;
            font-size: 14px;
            color: #111;
        }

        /* Paper title emphasis */
        .paperBox {
            margin: 18px auto 0;
            max-width: 720px;
            border: 1px solid #d7e0f2;
            background: #f3f6fb;
            padding: 12px 14px;
            text-align: center;
        }

        .paperBox .label {
            font-size: 11px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: .8px;
            font-weight: 700;
        }

        .paperBox .title {
            margin-top: 6px;
            font-size: 16px;
            font-weight: 800;
        }

        /* Details table */
        .details {
            margin: 18px auto 0;
            max-width: 720px;
            font-size: 12px;
            border-collapse: collapse;
            width: 100%;
        }

        .details td {
            padding: 6px 0;
            vertical-align: top;
        }

        .details .k {
            width: 170px;
            color: #222;
            font-weight: 800;
        }

        /* Seal + watermark */
        .watermark {
            position: absolute;
            left: 0;
            right: 0;
            top: 44%;
            text-align: center;
            font-size: 86px;
            font-weight: 900;
            color: rgba(11, 61, 145, 0.06);
            transform: rotate(-12deg);
            z-index: 0;
            pointer-events: none;
        }

        .seal {
            position: absolute;
            right: 28px;
            bottom: 205px;
            width: 100px;
            height: 100px;
            border-radius: 999px;
            border: 3px solid rgba(11, 61, 145, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #0B3D91;
            font-weight: 900;
            font-size: 12px;
            background: rgba(11, 61, 145, 0.03);
        }

        .seal span {
            display: block;
            font-weight: 700;
            font-size: 10px;
            color: #0B3D91;
            margin-top: 4px;
        }

        /* Signatures */
        .signRow {
            margin-top: 52px;
            display: flex;
            justify-content: space-between;
            gap: 18px;
            position: relative;
            z-index: 1;
        }

        .sig {
            width: 48%;
            border-top: 1px solid #666;
            padding-top: 8px;
            font-size: 12px;
            color: #222;
        }

        .sig .role {
            font-weight: 800;
        }

        .sig.right {
            text-align: right;
        }

        /* Footer verify line */
        .footer {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 14px;
            font-size: 11px;
            color: #333;
            border-top: 1px solid #d7e0f2;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            z-index: 1;
        }

        .footer .left {
            max-width: 70%;
        }

        .footer .right {
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="frame">
        <div class="inner">
            <div class="watermark">AIRN</div>

            <div class="header">
                <div class="brand">
                    <div class="logoBox">AIRN</div>
                    <div class="org">
                        <div class="name"><?= esc($brand_left ?? 'AIRN Journal of Computing Systems') ?></div>
                        <div class="sub">Academic & International Research Network</div>
                    </div>
                </div>

                <div class="metaRight">
                    <div><?= esc($brand_right ?? '') ?></div>
                    <div>Certificate ID: <span class="mono"><?= esc($code ?? '-') ?></span></div>
                </div>
            </div>

            <div class="certTitle">CERTIFICATE</div>
            <div class="certSubtitle">of Publication</div>

            <div class="presented">This certificate is hereby awarded to</div>
            <div class="recipient"><?= esc($recipient_name ?? 'Author') ?></div>

            <div class="statement">
                In recognition of the successful publication of a scholarly article under AIRN.
            </div>

            <div class="paperBox">
                <div class="label">Published Article Title</div>
                <div class="title"><?= esc($paper_title ?? '-') ?></div>
            </div>

            <table class="details">
                <tr>
                    <td class="k">Publication Date</td>
                    <td><?= esc(!empty($published_at) ? date('d M Y', strtotime($published_at)) : '-') ?></td>
                </tr>
                <tr>
                    <td class="k">DOI</td>
                    <td><?= esc($doi ?: '-') ?></td>
                </tr>
                <tr>
                    <td class="k">Volume / Issue / Pages</td>
                    <td><?= esc(($volume ?: '-') . ' / ' . ($issue ?: '-') . ' / ' . ($pages ?: '-')) ?></td>
                </tr>
                <tr>
                    <td class="k">Verification Link</td>
                    <td><?= esc($verify_url ?? '-') ?></td>
                </tr>
            </table>
            
            <div class="seal">
                VERIFIED
                <span>AIRN</span>
            </div>

            <div class="signRow">
                <div class="sig">
                    <div style="height:34px;"></div> <!-- reserved space for handwritten signature later -->
                    <div class="role">Editor-in-Chief</div>
                    <div style="font-weight:800; font-size:13px; color:#111;">
                        <?= esc($editor_name ?? 'Dr. Sam Ebute') ?>
                    </div>
                    <div style="font-size:11px; color:#333;">
                        AIRN Journal of Computing Systems
                    </div>
                </div>

                <div class="sig right">
                    <div style="height:34px;"></div> <!-- reserved space for handwritten signature later -->
                    <div class="role">Managing Editor</div>
                    <div style="font-weight:800; font-size:13px; color:#111;">
                        <?= esc($managing_editor_name ?? 'Prof. Philip Alex') ?>
                    </div>
                    <div style="font-size:11px; color:#333;">
                        Academic & International Research Network
                    </div>
                </div>
            </div>


            <div class="footer">
                <div class="left">
                    Verify this certificate using the link above or by the Certificate ID.
                </div>
                <div class="right mono">
                    <?= esc($code ?? '-') ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>