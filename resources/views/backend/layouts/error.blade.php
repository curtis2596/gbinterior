<!doctype html>
<html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>{{ SITE_NAME }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Test Laravel" name="description" />
        <meta content="Hardeep" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="/favicon.ico">

        <link rel="stylesheet"  type="text/css" href="/assets/css/app.css"  />
        <link rel="stylesheet"  type="text/css" href="/assets/css/bootstrap.min.css"  />
        <link rel="stylesheet"  type="text/css" href="/assets/css/preloader.min.css"/>
        <link rel="stylesheet"  type="text/css" href="/assets/css/icons.min.css" />
        
        <link rel="stylesheet"  type="text/css" href="/assets/libs/sweetalert2/sweetalert2.min.css" />
        
        <!-- Project related CSS -->        
        <link rel="stylesheet"  type="text/css" href="/css/backend/default.css?<?= BACKEND_CSS_VERSION ?>" />
                
        <style>
            .help-links *
            {
                margin-left: 1em;
            }
        </style>
    </head>

    <body>
        @yield('content')
        
        <!-- Theme Required File -->
        <script type="text/javascript" src="/assets/libs/bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="/assets/libs/bootbox/5.5.3/bootbox.min.js"></script>
        <script type="text/javascript" src="/assets/libs/metismenu/metisMenu.min.js"></script>
        <script type="text/javascript" src="/assets/libs/simplebar/simplebar.min.js"></script>
        <script type="text/javascript" src="/assets/libs/node-waves/waves.min.js"></script>
        <script type="text/javascript" src="/assets/libs/feather-icons/feather.min.js"></script>
        <script type="text/javascript" src="/assets/js/app.js"></script>

        <!-- Component File -->        
        <script type="text/javascript" src="/assets/libs/sweetalert2/sweetalert2.min.js"></script>
    </body>

</html>
