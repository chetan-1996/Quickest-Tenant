<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">

    <!-- Brand Logo Light -->
    <a href="index.html" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{asset('assets/images/logo.png')}}" alt="logo" height="50">
        </span>
        <span class="logo-sm">
            <img src="{{asset('assets/images/logo-sm.png')}}" alt="small logo" height="50">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="index.html" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{asset('assets/images/logo.png')}}" alt="dark logo" height="50">
        </span>
        <span class="logo-sm">
            <img src="{{asset('assets/images/logo-dark-sm.png')}}" alt="small logo" height="50">
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <!-- Full Sidebar Menu Close Button -->
    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>

    <!-- Sidebar -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!-- Leftbar User -->
        <div class="leftbar-user">
            <a href="pages-profile.html">
                <img src="assets/images/users/avatar-1.jpg" alt="user-image" height="42" class="rounded-circle shadow-sm">
                <span class="leftbar-user-name mt-2">Dominic Keller</span>
            </a>
        </div>

        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title">Navigation</li>

            <li class="side-nav-item">
                <a href="{{url('/admin/dashboard')}}" class="side-nav-link">
                    <i class="uil-home-alt"></i>
                    <span> Dashbaord </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{url('/admin/client')}}" class="side-nav-link">
                    <i class="uil-calender"></i>
                    <span> Client </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{url('/admin/business-category')}}" class="side-nav-link">
                    <i class="uil-calender"></i>
                    <span> Business Category </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{url('/admin/plans')}}" class="side-nav-link">
                    <i class="uil-calender"></i>
                    <span> Plans </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{url('/admin/plan-history')}}" class="side-nav-link">
                    <i class="uil-calender"></i>
                    <span> User Plan History </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{url('/admin/promo-code')}}" class="side-nav-link">
                    <i class="uil-calender"></i>
                    <span> Promo Code </span>
                </a>
            </li>
            
            <li class="side-nav-item">
                <a href="{{url('/admin/payment-history')}}" class="side-nav-link">
                    <i class="uil-calender"></i>
                    <span> Payment </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarEcommerce" aria-expanded="false"
                    aria-controls="sidebarEcommerce" class="side-nav-link">
                    <i class="uil-user-square"></i>
                    <span> Permissions </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarEcommerce">
                    <ul class="side-nav-second-level">
                        {{--                        <li> --}}
                        {{--                            <a href="{{route('admin.role.index')}}">Roles</a> --}}
                        {{--                        </li> --}}
                        <li>
                            <a href="{{url('/admin/permission')}}">Permission</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Help Box -->
            {{--<div class="help-box text-white text-center">
                <a href="javascript: void(0);" class="float-end close-btn text-white">
                    <i class="mdi mdi-close"></i>
                </a>
                <img src="assets/images/svg/help-icon.svg" height="90" alt="Helper Icon Image" />
                <h5 class="mt-3">Unlimited Access</h5>
                <p class="mb-3">Upgrade to plan to get access to unlimited reports</p>
                <a href="javascript: void(0);" class="btn btn-secondary btn-sm">Upgrade</a>
            </div>--}}
            <!-- end Help Box -->


        </ul>
        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->







{{--


<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">

            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @endif

                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
--}}
