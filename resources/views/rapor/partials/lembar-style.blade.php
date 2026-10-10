{{-- Gaya lembar rapor; dipakai cetak per siswa (rapor.print) dan cetak satu kelas (rapor.print-kelas) --}}
<style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Times New Roman', serif;
        color: #111;
        margin: 0;
        padding: 0;
    }

    @page {
        size: 210mm 330mm portrait;
        margin: 10mm;
    }

    html,
    body {
        width: 210mm;
        margin: 0;
        padding: 0;
    }

    .page {
        width: 210mm;
        min-height: calc(330mm - 20mm);
        padding: 10mm;
        margin: 0 auto;
        position: relative;
        background: #fff;
        page-break-inside: avoid;
    }

    .page-break:not(:last-child) {
        page-break-after: always;
    }

    .page:last-child {
        page-break-after: auto;
    }

    .watermark {
        position: absolute;
        inset: 0;
        opacity: 0.12;
        pointer-events: none;
        background-size: 37mm 26mm;
        background-repeat: repeat;
        background-position: 0 0;
        background-attachment: scroll;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    @media print {
        .watermark {
            background-size: 37mm 26mm !important;
            background-position: 0 0 !important;
            background-repeat: repeat !important;
            background-attachment: scroll !important;
            transform: none !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid #111;
        padding-bottom: 8px;
        margin-bottom: 12px;
    }

    .title-block {
        flex: 1;
        text-align: center;
    }

    .title-block h1 {
        margin: 0;
        font-size: 18px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .title-block h2 {
        margin: 2px 0;
        font-size: 16px;
        text-transform: uppercase;
    }

    .title-block p {
        margin: 0;
        font-size: 12px;
        line-height: 1.3;
    }

    .logo {
        width: 70px;
        height: 70px;
        object-fit: contain;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .info-table td {
        padding: 3px 6px;
        vertical-align: top;
    }

    .info-table td.label {
        width: 120px;
    }

    .section-title {
        text-align: center;
        font-weight: bold;
        font-size: 14px;
        margin: 10px 0 6px;
        text-transform: uppercase;
    }

    .nilai-table th,
    .nilai-table td {
        border: 1px solid #000;
        padding: 6px;
    }

    .nilai-table th {
        text-align: center;
        font-weight: 700;
    }

    .nilai-table td.mapel {
        width: 50%;
    }

    .nilai-table td.nilai {
        width: 12%;
        text-align: center;
    }

    .nilai-table td.deskripsi {
        width: 38%;
    }

    .group-row td {
        font-weight: bold;
        background: #f6f6f6;
    }

    .sub {
        padding-left: 14px;
    }

    .two-col {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-top: 10px;
    }

    .block {
        border: 1px solid #000;
        padding: 8px;
    }

    .block h3 {
        margin: 0 0 6px;
        font-size: 13px;
        text-transform: uppercase;
        text-align: center;
    }

    .small-table th,
    .small-table td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 11px;
    }

    .ttd {
        margin-top: 20px;
        font-size: 12px;
        line-height: 1.5;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .ttd-baris {
        display: grid;
        /* Blok kanan selebar baris terpanjangnya dan menempel ke tepi kanan */
        grid-template-columns: 1fr auto;
        column-gap: 24px;
    }

    .ttd-tengah {
        margin-top: 18px;
        text-align: center;
    }

    .ttd-nama {
        margin-top: 64px;
        font-weight: 700;
    }

    .ttd-garis {
        width: 160px;
        margin-top: 82px;
        border-bottom: 1px solid #000;
    }

    .qr-box {
        border: 1px solid #000;
        width: 90px;
        height: 90px;
        margin: 6px auto 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }

    .note {
        font-size: 11px;
        line-height: 1.4;
        border: 1px solid #000;
        padding: 8px;
        min-height: 60px;
    }

    .mt-8 {
        margin-top: 8px;
    }

    .mt-12 {
        margin-top: 12px;
    }

    .mb-4 {
        margin-bottom: 4px;
    }
</style>
