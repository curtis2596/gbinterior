<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\FileUtility;
?>

<style>
    .app-search {
        z-index: 11;
    }

    .app-search .form-control {
        border: 1px solid #e9e9ef;
        height: 40px;
        padding-left: 17px;
        padding-right: 50px;
        background-color: #f3f3f9;
        -webkit-box-shadow: none;
        box-shadow: none;
        border-radius: 0px;
    }

    .search-menu-link-block {}

    #search_menu {
        width: 100%;
    }

    #search_menu:focus {
        color: #495057;
        background-color: #f3f3f9;
        border-color: #e9e9ef;
    }

    #search_menu_autocomplete {
        height: 50vh;
        padding: 0;
        margin: 0;
        position: fixed;
        z-index: 1;
        background-color: #f3f3f9;
        border: 1px solid #e9e9ef;
        border-radius: 0 0 5px 5px;
        display: none;
        z-index: 5;
    }

    @media (min-width: 900px) {

        #search_menu,
        #search_menu_autocomplete {
            width: 30vw;
        }
    }

    @media (min-width: 1200px) {

        #search_menu,
        #search_menu_autocomplete {
            width: 40vw;
        }
    }

    #search_menu_autocomplete ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    #search_menu_autocomplete ul li {
        padding: 0;
        margin: 0;
        border-bottom: 2px solid #e9e9ef;
    }

    #search_menu_autocomplete ul li a {
        padding: 8px 10px;
        display: block;
    }

    #search_menu_autocomplete ul li:hover a {
        background-color: color-mix(in srgb, var(--bs-link-color), #FFF 70%);
    }

    [data-layout-mode=dark] #search_menu_autocomplete ul li:hover a {
        background-color: color-mix(in srgb, var(--bs-topbar-search-bg), #000 20%);
    }

    #search_menu_autocomplete ul li:hover a {
        font-weight: bold;
        transition: font-weight 0.5s;
    }
</style>
<header class="page-header row">
    <div class="logo-wrapper d-flex align-items-center col-auto">
        <a href="{{ route('home') }}"><img class="light-logo img-fluid" src="/{{$company->logo}}" alt="logo" /><img class="dark-logo img-fluid" src="/{{$company->logo}}" alt="logo" /></a>
        <a class="close-btn toggle-sidebar" href="javascript:void(0)">
            <svg class="svg-color">
                <use href="/assets/svg/iconly-sprite.svg#Category"></use>
            </svg>
        </a>
    </div>
    <div class="page-main-header col">
        <div class="header-left">
            <span class="fw-bold">{{$company->name}}</span>
            <form class="form-inline search-full col" action="#" method="get">
                <div class="form-group w-100">
                    <div class="Typeahead Typeahead--twitterUsers">
                        <div class="u-posRelative">
                            <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Search Admiro .." name="q" title="" autofocus="autofocus" />
                            <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading...</span></div>
                            <i class="close-search" data-feather="x"></i>
                        </div>
                        <div class="Typeahead-menu"></div>
                    </div>
                </div>
            </form>
            <div class="d-lg-block d-none">
                <div class="position-relative">
                    <input id="search_menu" type="text" class="form-control" placeholder="Search Menu Link">
                    <div id="search_menu_autocomplete" class="simplebar-content-wrapper">
                        <div class="simplebar-content">
                            <ul>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- <div class="Typeahead Typeahead--twitterUsers">
                    <div class="u-posRelative d-flex align-items-center">
                        <input class="demo-input py-0 Typeahead-input form-control-plaintext w-100" type="text" placeholder="Type to Search..." name="q" title="" /><i class="search-bg iconly-Search icli"></i>
                    </div>
                </div> -->
            </div>
        </div>
        <div class="nav-right">
            <ul class="header-right">
                <li class="search d-lg-none d-flex">
                    <a href="javascript:void(0)">
                        <svg>
                            <use href="/assets/svg/iconly-sprite.svg#Search"></use>
                        </svg>
                    </a>
                </li>
                <li>
                    <a class="dark-mode" href="javascript:void(0)">
                        <svg>
                            <use href="/assets/svg/iconly-sprite.svg#moondark"></use>
                        </svg>
                    </a>
                </li>
                <li class="custom-dropdown">
                    <a href="javascript:void(0)">
                        <svg>
                            <use href="/assets/svg/iconly-sprite.svg#notification"></use>
                        </svg>
                    </a>
                    <span class="badge rounded-pill badge-primary">4</span>
                    <div class="custom-menu notification-dropdown py-0 overflow-hidden">
                        <h3 class="title bg-primary-light dropdown-title">Notification <span class="font-primary">View all</span></h3>
                        <ul class="activity-timeline">
                            <li class="d-flex align-items-start">
                                <div class="activity-line"></div>
                                <div class="activity-dot-primary"></div>
                                <div class="flex-grow-1">
                                    <h6 class="f-w-600 font-primary">
                                        30-04-2024<span>Today</span>
                                        <span class="circle-dot-primary float-end">
                                            <svg class="circle-color">
                                                <use href="/assets/svg/iconly-sprite.svg#circle"></use>
                                            </svg>
                                        </span>
                                    </h6>
                                    <h5>Alice Goodwin</h5>
                                    <p class="mb-0">Fashion should be fun. It shouldn't be labelled intellectual.</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <div class="activity-dot-secondary"></div>
                                <div class="flex-grow-1">
                                    <h6 class="f-w-600 font-secondary">
                                        28-06-2024<span>1 hour ago</span>
                                        <span class="float-end circle-dot-secondary">
                                            <svg class="circle-color">
                                                <use href="/assets/svg/iconly-sprite.svg#circle"></use>
                                            </svg>
                                        </span>
                                    </h6>
                                    <h5>Herry Venter</h5>
                                    <p>I am convinced that there can be luxury in simplicity.</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <div class="activity-dot-primary"></div>
                                <div class="flex-grow-1">
                                    <h6 class="f-w-600 font-primary">
                                        04-08-2024<span>Today</span>
                                        <span class="float-end circle-dot-primary">
                                            <svg class="circle-color">
                                                <use href="/assets/svg/iconly-sprite.svg#circle"></use>
                                            </svg>
                                        </span>
                                    </h6>
                                    <h5>Loain Deo</h5>
                                    <p>I feel that things happen for open new opportunities.</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <div class="activity-dot-secondary"></div>
                                <div class="flex-grow-1">
                                    <h6 class="f-w-600 font-secondary">
                                        12-11-2024<span>Yesterday</span>
                                        <span class="float-end circle-dot-secondary">
                                            <svg class="circle-color">
                                                <use href="/assets/svg/iconly-sprite.svg#circle"></use>
                                            </svg>
                                        </span>
                                    </h6>
                                    <h5>Fenter Jessy</h5>
                                    <p>Sometimes the simplest things are the most profound.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a class="full-screen" href="javascript:void(0)">
                        <svg>
                            <use href="/assets/svg/iconly-sprite.svg#scanfull"></use>
                        </svg>
                    </a>
                </li>
                @auth
                @php
                $user = Auth::user();
                @endphp
                <li class="profile-nav custom-dropdown">
                    <div class="user-wrap">
                        @if(isset($user->profile_image) && $user->profile_image)
                        <div class="user-img"><img src="{{ FileUtility::get($user->profile_image) }}" alt="user" /></div>
                        @endif
                        <div class="user-content">
                            <!-- <h6>Ava Davis</h6> -->
                            <p class="mb-0">{{ $user->name }}<i class="fa-solid fa-chevron-down"></i></p>
                        </div>
                    </div>
                    <div class="custom-menu overflow-hidden">
                        <ul class="profile-body">
                            <li class="d-flex">
                                <svg class="svg-color">
                                    <use href="/assets/svg/iconly-sprite.svg#Profile"></use>
                                </svg>
                                @if (Route::has('user.change.password'))
                                <a class="ms-2" href="{{ route('user.change.password') }}">Change Password</a>
                                @else
                                Route not exist : user.change.password
                                @endif
                            </li>
                            <!-- <li class="d-flex">
                                <svg class="svg-color">
                                    <use href="../assets/svg/iconly-sprite.svg#Message"></use>
                                </svg>
                                <a class="ms-2" href="letter-box.html">Inbox</a>
                            </li>
                            <li class="d-flex">
                                <svg class="svg-color">
                                    <use href="../assets/svg/iconly-sprite.svg#Document"></use>
                                </svg>
                                <a class="ms-2" href="to-do.html">Task</a>
                            </li> -->

                            <li class="d-flex">
                                <svg class="svg-color">
                                    <use href="/assets/svg/iconly-sprite.svg#Login"></use>
                                </svg>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>

                            </li>
                        </ul>
                    </div>
                </li>
                @endauth
            </ul>
        </div>
    </div>
</header>
<script type="text/javascript">
    //search menu link
    var menu_autocomplete_list = JSON.parse('<?= json_encode($header_menu_list) ?>');
    console.log(menu_autocomplete_list);
    $(document).ready(function() {
        new SimpleBar(document.getElementById('search_menu_autocomplete'));

        function search_simple(search_text) {
            var list = []
            for (var i in menu_autocomplete_list) {
                var link = menu_autocomplete_list[i];

                if (link['title'].toLowerCase().indexOf(search_text) >= 0) {
                    list.push(link);
                }
            }

            return list;
        }

        function search_list_in_string(search_list) {
            var list = [];
            for (var i in menu_autocomplete_list) {
                var link = menu_autocomplete_list[i];

                var is_all_part_found = true;
                for (var a in search_list) {
                    var part = search_list[a].trim();

                    if (part.length >= 2) {
                        if (link['title'].toLowerCase().indexOf(part) == -1) {
                            is_all_part_found = false;
                        }
                    }
                }

                if (is_all_part_found) {
                    list.push(link);
                }
            }

            return list;
        }

        function show_autocomplete_list(search_text) {
            var list = search_simple(search_text);

            var sub_parts = search_text.split(" ");
            if (sub_parts.length > 1) {
                var list2 = search_list_in_string(sub_parts);

                for (var a in list2) {
                    var link2 = list2[a];
                    var is_found = false;
                    for (var i in list) {
                        var link = list[i];

                        if (link['title'] == link2['title']) {
                            is_found = true;
                        }
                    }

                    if (!is_found) {
                        list.push(link2);
                    }
                }
            }

            var html = "";
            for (var i in list) {
                var link = list[i];
                html += "<li>"
                html += '<a href="' + link["url"] + '">' + link["title"] + '</a>';
                html += "</li>";
            }

            $("#search_menu_autocomplete ul").html(html);
            $("#search_menu_autocomplete").show();
        }

        function hide_autocomplete_list() {
            $("#search_menu_autocomplete ul").html("");
            $("#search_menu_autocomplete").hide();
        }

        function show_or_hide_search_menu_autocomplete() {
            var search = $("input#search_menu").val();
            if (search.length >= 1) {
                search = search.trim().toLowerCase();
                show_autocomplete_list(search);
                $.blackdrop.show();
            } else {
                hide_autocomplete_list();
                $.blackdrop.hide();
            }
        }

        $("input#search_menu").keyup(function(e) {
            if (e.key == "Escape") {
                $(this).val("");
            }

            show_or_hide_search_menu_autocomplete();
        });

        $("input#search_menu").focus(function() {
            show_or_hide_search_menu_autocomplete();
        });

        $.blackdrop.init();
        $.blackdrop.onClick(function() {
            $("#search_menu_autocomplete").hide();
        });
    });
</script>