<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="{{ asset('dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{ config('app.name', 'Laravel') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @foreach ($adminNavigation as $item)
                    @php
                        $currentRoute = request()->route()?->getName();
                        $childRoutes = isset($item['children'])
                            ? array_values(array_filter(array_column($item['children'], 'route')))
                            : [];
                        $hasChildren = !empty($childRoutes);
                        $itemRoute = $item['route'] ?? null;
                        $isOpen = $hasChildren && $currentRoute && in_array($currentRoute, $childRoutes, true);
                        $isActive = $isOpen || ($itemRoute && $currentRoute === $itemRoute);
                    @endphp
                    <li class="nav-item {{ $isOpen ? 'menu-open' : '' }}">
                        <a href="{{ $hasChildren || !$itemRoute ? '#' : route($itemRoute) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                            <i class="nav-icon {{ $item['icon'] }}"></i>
                            <p>
                                {{ $item['title'] }}
                                @if ($hasChildren)
                                    <i class="right fas fa-angle-left"></i>
                                @endif
                            </p>
                        </a>
                        @if ($hasChildren)
                            <ul class="nav nav-treeview">
                                @foreach ($item['children'] as $child)
                                    @continue(empty($child['route']))
                                    <li class="nav-item">
                                        <a href="{{ route($child['route']) }}" class="nav-link {{ $currentRoute === $child['route'] ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>{{ $child['title'] }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
