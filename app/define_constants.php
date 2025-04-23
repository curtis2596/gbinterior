<?php
if (!defined('IT_COMPANY_NAME')) {

    define("IT_COMPANY_NAME", "Web Chilli");
    define("IT_COMPANY_ICON", "");

    if (env('APP_ENV') == 'production')
    {
        define("BACKEND_CSS_VERSION", "05-feb-2025");
        define("BACKEND_JS_VERSION", "05-Feb-2025");
    }
    else
    {
        define("BACKEND_CSS_VERSION", time());
        define("BACKEND_JS_VERSION", time());
    }

    define("ACTION_NOT_PROCEED", 406);
    define("DEFAULT_EXPORT_CSV_LIMIT", env("DEFAULT_EXPORT_CSV_LIMIT", 20000));
    define("DEFAULT_EXPORT_CSV_JS_LIMIT", env("DEFAULT_EXPORT_CSV_JS_LIMIT", 10000));
}