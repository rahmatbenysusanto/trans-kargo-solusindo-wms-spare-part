<!doctype html>
<html lang="en" dir="ltr" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title') - WMS Spare</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS (minimal for table/card styling) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <style>
        body {
            background: #f5f5f9;
            font-family: 'Inter', 'Public Sans', -apple-system, sans-serif;
        }

        .pdf-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        @media print {
            body {
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            .pdf-container {
                max-width: 100%;
                padding: 0;
                margin: 0;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>

    @yield('css')
</head>

<body>
    <div class="pdf-container">
        @yield('content')
    </div>

    <!-- Minimal JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>

    @yield('js')
</body>

</html>
