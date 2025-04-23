<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Admiro admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords"
        content="admin template, Admiro admin template, best javascript admin, dashboard template, bootstrap admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <title>Admiro - Premium Admin Template</title>
    <!-- Favicon icon-->
    <link rel="icon" href="../assets/images/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="/assets/images/favicon.png" type="image/x-icon">
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200;6..12,300;6..12,400;6..12,500;6..12,600;6..12,700;6..12,800;6..12,900;6..12,1000&amp;display=swap"
        rel="stylesheet">
    <!-- Flag icon css -->
    <link rel="stylesheet" href="/assets/css/vendors/flag-icon.css">
    <!-- iconly-icon-->
    <link rel="stylesheet" href="/assets/css/iconly-icon.css">
    <link rel="stylesheet" href="/assets/css/bulk-style.css">
    <!-- iconly-icon-->
    <link rel="stylesheet" href="/assets/css/themify.css">
    <!--fontawesome-->
    <link rel="stylesheet" href="/assets/css/fontawesome-min.css">
    <!-- Whether Icon css-->
    <link rel="stylesheet" type="text/css" href="../assets/css/vendors/weather-icons/weather-icons.min.css">
    <!-- App css -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link id="color" rel="stylesheet" href="/assets/css/color-1.css" media="screen">

    <link rel="stylesheet" type="text/css" href="/css/backend.css?<?= BACKEND_CSS_VERSION ?>" />

    <script src="/assets/js/vendors/jquery/jquery.min.js"></script>
</head>

<body>
    <!-- tap on top starts-->
    <div class="tap-top"><i class="iconly-Arrow-Up icli"></i></div>
    <!-- tap on tap ends-->
    <!-- loader-->
    <div class="loader-wrapper">
        <div class="loader"><span></span><span></span><span></span><span></span><span></span></div>
    </div>
    <!-- login page start-->
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card login-dark">
                    <div>
                        <div><a class="logo" href="{{ route('home') }}"><img class="img-fluid for-light m-auto"
                                    src="{{$company->logo}}" alt="looginpage"><img
                                    class="img-fluid" src="{{$company->logo}}" alt="logo"></a>
                        </div>
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
        <!-- jquery-->
        <!-- bootstrap js-->
        <script src="/assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js" defer=""></script>
        <script src="/assets/js/vendors/bootstrap/dist/js/popper.min.js" defer=""></script>
        <!--fontawesome-->
        <script src="/assets/js/vendors/font-awesome/fontawesome-min.js"></script>
        <!-- password_show-->
        <script src="/assets/js/password.js"></script>
        <!-- custom script -->
        <script src="/assets/js/script.js"></script>
    </div>
</body>

</html>