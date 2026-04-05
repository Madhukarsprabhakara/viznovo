<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <title>{{ $report->title ?? 'Global Report' }}</title>
    <style>
      :root {
        color-scheme: light;
      }

      html,
      body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      html,
      body {
        margin: 0;
        padding: 0;
      }

      .report-shell {
        box-sizing: border-box;
        width: 100%;
      }

      .report-content,
      .report-content * {
        box-sizing: border-box;
      }

      .report-content img,
      .report-content svg,
      .report-content canvas,
      .report-content table,
      .report-content pre,
      .report-content blockquote {
        max-width: 100%;
      }

      .report-content table,
      .report-content thead,
      .report-content tbody,
      .report-content tr,
      .report-content td,
      .report-content th,
      .report-content figure,
      .report-content pre,
      .report-content blockquote,
      .report-content section,
      .report-content article,
      .report-content .avoid-page-break {
        break-inside: avoid;
        page-break-inside: avoid;
      }

      @page {
        margin: 0;
      }

      @media print {
        body {
          padding: 0 !important;
        }

        .report-shell {
          max-width: none !important;
          border: 0 !important;
          border-radius: 0 !important;
          box-shadow: none !important;
          margin: 0 !important;
          padding: 18mm 12mm 12mm !important;
        }

        .report-content {
          padding-top: 6mm;
        }

        .report-content > *:first-child {
          margin-top: 0 !important;
        }

        .report-content > .min-h-screen {
          min-height: auto !important;
        }

        .report-content [class*="backdrop-blur"] {
          -webkit-backdrop-filter: none !important;
          backdrop-filter: none !important;
        }

        .report-content h1,
        .report-content h2,
        .report-content p {
          color: #0f172a !important;
          opacity: 1 !important;
        }
      }
    </style>
  </head>
  <body class="min-h-screen bg-gradient-to-tr from-blue-50 via-white to-green-50 {{ $isPdfExport ? '' : 'px-2 pt-10 pb-6' }}">
  <div class="report-shell mx-auto w-full {{ $isPdfExport ? '' : 'max-w-full sm:max-w-3xl md:max-w-6xl lg:max-w-8xl xl:max-w-screen-2xl rounded-xl border border-gray-200 bg-white p-4 shadow-lg sm:p-6 md:p-8' }} {{ $isPdfExport ? 'px-12 py-10' : '' }}">
    <section class="report-content mb-10">
      {!! $content !!}
    </section>
  </div>
</body>
</html>