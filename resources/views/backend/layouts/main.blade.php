<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>
        {{ $page_title ?? config('app.name') }}
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $page_description ?? config('app.description') }}" />
    <meta name="description" content="{{ $page_author ?? config('app.author') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- App favicon -->
    <link rel="icon" href="/assets/images/favicon.png" type="image/x-icon" />
    <link rel="shortcut icon" href="/assets/images/favicon.png" type="image/x-icon" />
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200;6..12,300;6..12,400;6..12,500;6..12,600;6..12,700;6..12,800;6..12,900;6..12,1000&amp;display=swap" />
    <link rel="stylesheet" type="text/css" href="/assets/css/fontawesome-min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/flag-icon.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/iconly-icon.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/bulk-style.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/themify.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/weather-icons/weather-icons.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/scrollbar.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/slick.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/slick-theme.css" />
    <link rel="stylesheet" type="text/css" href="/assets/libs/select2/select2.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/libs/select2/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/libs/Croppie-2.6.4/croppie.css" />
    <link rel="stylesheet" type="text/css" href="/assets/libs/fancybox/dist/jquery.fancybox.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors/flatpickr/flatpickr.min.css">
    <!-- <link rel="stylesheet" type="text/css" href="/assets/libs/bootstrap-datepicker/css/bootstrap-datepicker3.css" /> -->
    <!-- <link rel="stylesheet" type="text/css" href="/assets/libs/bootstrap-timepicker/css/bootstrap-timepicker.min.css" /> -->
    <!-- <link rel="stylesheet" type="text/css" href="/assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.css" /> -->

    <!-- Theme Specfic -->
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css?<?= BACKEND_CSS_VERSION ?>" />
    <link id="color" rel="stylesheet" href="/assets/css/color-1.css?<?= BACKEND_CSS_VERSION ?>" media="screen" />

    <!-- Project related CSS -->
    <link rel="stylesheet" type="text/css" href="/libs/loader/loader.css?<?= BACKEND_CSS_VERSION ?>" />
    <link rel="stylesheet" type="text/css" href="/libs/i-data-table/style.css?<?= BACKEND_CSS_VERSION ?>" />
    <link rel="stylesheet" type="text/css" href="/libs/style-for-custom-lib.css?<?= BACKEND_CSS_VERSION ?>" />    
    <link rel="stylesheet" type="text/css" href="/css/backend.css?<?= BACKEND_CSS_VERSION ?>" />

    <script src="/assets/js/vendors/jquery/jquery.min.js"></script>
</head>

<body>
    <!-- page-wrapper Start-->
    <!-- tap on top starts-->
    <div class="tap-top"><i class="iconly-Arrow-Up icli"></i></div>
    <!-- tap on tap ends-->
    <!-- loader-->
    <div class="loader-wrapper">
        <div class="loader"><span></span><span></span><span></span><span></span><span></span></div>
    </div>
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        @include ($partial_path . ".header")
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page sidebar start-->
            @include($partial_path . ".sidebar")
            <!-- Page sidebar end-->
            <div class="page-body">
                @yield('content')
            </div>
            @include($partial_path . ".footer")
        </div>
    </div>

    
    <script src="/assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/vendors/bootstrap/dist/js/popper.min.js"></script>
    <script src="/assets/js/vendors/font-awesome/fontawesome-min.js"></script>
    <script src="/assets/js/vendors/feather-icon/feather.min.js"></script>
    <script src="/assets/js/vendors/feather-icon/custom-script.js"></script>
    <script src="/assets/js/chart/apex-chart/apex-chart.js"></script>
    <script src="/assets/js/chart/apex-chart/stock-prices.js"></script>
    <script src="/assets/js/slick/slick.min.js"></script>
    <script src="/assets/js/slick/slick.js"></script>
    <script src="/assets/js/js-datatables/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/js/js-datatables/datatables/datatable.custom.js"></script>
    <script src="/assets/js/js-datatables/datatables/datatable.custom1.js"></script>
    <script src="/assets/js/datatable/datatables/datatable.custom.js"></script>
    <script src="/assets/js/sweetalert/sweetalert2.min.js"></script>
    <script src="/assets/libs/bootbox/5.5.3/bootbox.min.js"></script>
    <script src="/assets/libs/select2/select2.min.js"></script>
    <script src="/assets/js/flat-pickr/flatpickr.js"></script>
    <!-- <script src="/assets/libs/moment/moment.js"></script> -->
    <!-- <script src="/assets/libs/moment/moment-with-locales.js"></script> -->
    <!-- <script src="/assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.js"></script> -->
    <!-- <script src="/assets/libs/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script> -->
    <!-- <script src="/assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js"></script> -->
    <script src="/assets/libs/Croppie-2.6.4/croppie.min.js"></script>
    <script src="/assets/libs/fancybox/dist/jquery.fancybox.min.js"></script>
    <script src="/assets/libs/ejs.min.js"></script>
    <script src="/assets/libs/jquery.form.min.js"></script>
    

    <script src="/assets/js/config.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/assets/js/script.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/assets/js/height-equal.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/assets/js/sidebar.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/assets/js/scrollbar/simplebar.js"></script>
    <script src="/assets/js/scrollbar/custom.js?<?= BACKEND_JS_VERSION ?>"></script>

    <script src="/assets/js/theme-customizer/customizer.js?<?= BACKEND_JS_VERSION ?>"></script>

    <!-- Project Lib -->
    <script src="/libs/constants.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/libs/events.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/libs/loader/loader.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/libs/date-util.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/libs/jquery-input-validate.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/libs/jquery_extend.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/libs/i-data-table/script.js?<?= BACKEND_JS_VERSION ?>"></script>

    <!-- Project JS -->
    <script src="/js/common_funtions.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/js/common_script_for_all_non_ajax_layout.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="/js/backend/backend_non_ajax.js?<?= BACKEND_JS_VERSION ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.9.2/tinymce.min.js"></script>


    @stack('scripts')

</body>

</html>