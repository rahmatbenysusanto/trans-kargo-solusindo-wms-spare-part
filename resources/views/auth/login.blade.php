<!doctype html>
<html lang="en" class=" layout-wide  customizer-hide" dir="ltr" data-skin="default"
    data-assets-path="../../assets/" data-template="vertical-menu-template" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Login - Maximaz Spare</title>

    <meta name="description"
        content="Vuexy is the best bootstrap 5 dashboard for responsive web apps. Streamline your app development process with ease." />
    <meta name="keywords"
        content="Vuexy bootstrap dashboard, vuexy bootstrap 5 dashboard, themeselection, html dashboard, web dashboard, frontend dashboard, responsive bootstrap theme" />
    <meta property="og:title" content="Vuexy bootstrap Dashboard by Pixinvent" />
    <meta property="og:type" content="product" />
    <meta property="og:url"
        content="https://themeforest.net/item/vuexy-vuejs-html-laravel-admin-dashboard-template/23328599" />
    <meta property="og:image" content="https://pixinvent.com/wp-content/uploads/2023/06/vuexy-hero-image.png" />
    <meta property="og:description"
        content="Vuexy is the best bootstrap 5 dashboard for responsive web apps. Streamline your app development process with ease." />
    <meta property="og:site_name" content="Pixinvent" />
    <link rel="canonical"
        href="https://themeforest.net/item/vuexy-vuejs-html-laravel-admin-dashboard-template/23328599" />

    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5J3LMKC');
    </script>
    <link rel="icon" type="image/x-icon"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;ampdisplay=swap"
        rel="stylesheet" />

    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/fonts/iconify-icons.css" />
    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/pickr/pickr-themes.css" />
    <link rel="stylesheet" href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/css/demo.css" />
    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/@form-validation/form-validation.css" />
    <link rel="stylesheet" href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/css/pages/page-auth.css" />
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/js/helpers.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/js/template-customizer.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/js/config.js"></script>
</head>

<body>

<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5J3LMKC" height="0" width="0" style="display: none; visibility: hidden"></iframe></noscript>

<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-6">
            <div class="card">
                <div class="card-body">
                    <div class="app-brand justify-content-center mb-6">
                        <a href="#" class="app-brand-link">
                            <span class="app-brand-logo demo">
                                <img src="{{ asset('assets/image/logo.png') }}" alt="logo" height="60">
                            </span>
                        </a>
                    </div>

                    <p class="mb-6">Please sign-in to your account</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="formAuthentication"
        class="mb-4" action="{{ route('loginPost') }}" method="POST">
    @csrf
    <div class="mb-6 form-control-validation">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username"
            value="{{ old('username') }}" autofocus />
    </div>
    <div class="mb-6 form-password-toggle form-control-validation">
        <label class="form-label" for="password">Password</label>
        <div class="input-group input-group-merge">
            <input type="password" id="password" class="form-control" name="password"
                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                aria-describedby="password" />
            <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
        </div>
    </div>
    <div class="my-8">
        <div class="d-flex justify-content-between">
            <div class="form-check mb-0 ms-2">
                <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                <label class="form-check-label" for="remember-me"> Remember Me </label>
            </div>
        </div>
    </div>
    <div class="mb-6">
        <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
    </div>
    </form>
    </div>
    </div>
    <!-- /Login -->
    </div>
    </div>
    </div>

    <!-- / Content -->


    <div class="buy-now">
        <a href="https://themeforest.net/item/vuexy-vuejs-html-laravel-admin-dashboard-template/23328599"
            target="_blank" class="btn btn-danger btn-buy-now">Buy Now</a>
    </div>




    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js -->


    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/jquery/jquery.js"></script>

    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/popper/popper.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/js/bootstrap.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/node-waves/node-waves.js">
    </script>


    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/@algolia/autocomplete-js.js">
    </script>



    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/pickr/pickr.js"></script>



    <script
        src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js">
    </script>


    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/hammer/hammer.js"></script>

    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/i18n/i18n.js"></script>


    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/@form-validation/popular.js">
    </script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/@form-validation/bootstrap5.js">
    </script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/@form-validation/auto-focus.js">
    </script>

    <!-- Main JS -->

    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/js/main.js"></script>


    <!-- Page JS -->
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/js/pages-auth.js"></script>

    </body>

</html>
