<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <title>Global Report</title>
  </head>
  <body class="min-h-screen bg-gradient-to-tr from-blue-50 via-white to-green-50 px-2 pt-10 pb-6">
  <div class="mx-auto w-full max-w-full sm:max-w-3xl md:max-w-6xl lg:max-w-8xl xl:max-w-screen-2xl rounded-xl border border-gray-200 bg-white p-4 sm:p-6 md:p-8 shadow-lg">
    <section class="mb-10">
      {!! $content !!}
    </section>
  </div>
</body>
</html>