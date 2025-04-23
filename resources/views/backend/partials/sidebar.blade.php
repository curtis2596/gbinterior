<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow">
        <i data-feather="arrow-left"></i>
    </div>
    <div class="main-sidebar" id="main-sidebar">
        <ul class="sidebar-menu" id="simple-bar">
            <li class="pin-title sidebar-main-title">
                <div>
                    <h5 class="sidebar-title f-w-700">Pinned</h5>
                </div>
            </li>
            <li class="sidebar-main-title">
                <div>
                    <h5 class="lan-1 f-w-700 sidebar-title">General</h5>
                </div>
            </li>
            @foreach($menus as $menu)
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                @isset($menu['route_name'])
                <a class="sidebar-link menu-a {{ $menu['is_active'] ? 'active' : '' }}" href="{{ route($menu['route_name']) }}">
                    <i class="{{ $menu['icon'] }}"></i>
                    <h6>{{ $menu['title'] }}</h6>
                    @if(isset($menu['badge']))
                    <span class="badge">{{ $menu['badge'] }}</span>
                    @endif
                </a>
                @else
                <a class="sidebar-link" href="javascript:void(0);">
                    <i class="{{ $menu['icon'] }}"></i>
                    <h6>{{ $menu['title'] }}</h6>
                    @if(isset($menu['badge']))
                    <span class="badge">{{ $menu['badge'] }}</span>
                    @endif
                    <i class="iconly-Arrow-Right-2 icli"></i>
                </a>
                @isset($menu['links'])
                <ul class="sidebar-submenu" style="display: none;">
                    @foreach($menu['links'] as $submenu)
                    <li>
                        @isset($submenu['route_name'])
                        <a href="{{ route($submenu['route_name']) }}" class="menu-a {{ $submenu['is_active'] ? 'active' : '' }}">
                            <i class="{{ $submenu['icon'] }}"></i>
                            <span>{{ $submenu['title'] }}</span>
                        </a>
                        @else
                        <a href="javascript:void(0);" class="submenu-title">
                            <i class="{{ $submenu['icon'] }}"></i>
                            <span>{{ $submenu['title'] }}</span>
                            <i class="iconly-Arrow-Right-2 icli"></i>
                        </a>
                        @isset($submenu['links'])
                        <ul class="according-submenu" style="display: none;">
                            @foreach($submenu['links'] as $nestedSubmenu)
                            <li>
                                <a href="{{ route($nestedSubmenu['route_name']) }}" class="menu-a {{ $nestedSubmenu['is_active'] ? 'active' : '' }}">
                                    <i class="{{ $nestedSubmenu['icon'] }}"></i>
                                    <span>{{ $nestedSubmenu['title'] }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @endisset
                        @endisset
                    </li>
                    @endforeach
                </ul>
                @endisset
                @endisset
            </li>
            @endforeach
        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</aside>

<script type="text/javascript">
    $(function(){
        console.log("Active menu a : " + $(".menu-a.active").length);
        $(".menu-a.active").parents("li").addClass("active");
        $(".menu-a.active").parents("ul").show();
        // $(".menu-a.active").closest("li.sidebar-list").find("> a.sidebar-link").addClass("active");
    });
</script>