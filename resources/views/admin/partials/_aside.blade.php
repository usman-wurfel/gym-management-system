<style>
    /*.menu-item i{*/
    /*    color:#494b74;*/
    /*}*/
    .menu-icon svg {
        fill: #b5b5c3;
        width: 16px !important;
    }
    .menu-item-active i {
        color: #ba9f57 !important;
    }
    .menu-item-active svg {
        fill: #ba9f57;
    }

    .menu-item:hover i {
        color: #ba9f57 !important;
    }
    .menu-item:hover svg {
        fill: #ba9f57;
    }
</style>
<!--begin::Aside-->
<div class="aside aside-left aside-fixed d-flex flex-column flex-row-auto" id="kt_aside">

    <!--begin::Brand-->
    <div class="brand flex-column-auto" id="kt_brand">
        @php
            if(auth()->user()->image){
                $logo = asset('images/'.auth()->user()->image);
            }elseif(auth()->user()->is_admin == 1 and auth()->user()->user_id != null){
                if(auth()->user()->parent->image){
                    $logo = asset('images/'.auth()->user()->parent->image);
                }
            }
        @endphp
        <!--begin::Logo-->
        <a href="{{ route('admin.dashboard') }}" class="brand-logo">
            <img alt="Logo" src="{{ asset($logo) }}" style="max-height:48px;width:auto;height:auto;background:transparent;"/>
        </a>

        <!--end::Logo-->

        <!--begin::Toggle-->
        <button class="brand-toggle btn btn-sm px-0" id="kt_aside_toggle">
							<span class="svg-icon svg-icon svg-icon-xl">

								<!--begin::Svg Icon | path:assets/media/svg/icons/Navigation/Angle-double-left.svg-->
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                     width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
									<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
										<polygon points="0 0 24 0 24 24 0 24"/>
										<path
                                            d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z"
                                            fill="#000000" fill-rule="nonzero"
                                            transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999)"/>
										<path
                                            d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z"
                                            fill="#000000" fill-rule="nonzero" opacity="0.3"
                                            transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999)"/>
									</g>
								</svg>

                                <!--end::Svg Icon-->
							</span>
        </button>

        <!--end::Toolbar-->
    </div>

    <!--end::Brand-->

    <!--begin::Aside Menu-->
    <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">

        <!--begin::Menu Container-->
        <div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1"
             data-menu-dropdown-timeout="500">

            <!--begin::Menu Nav-->
            <ul class="menu-nav">
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'dashboard') {
                    echo 'menu-item-active';
                }?>" aria-haspopup="true">
                    <a href="{{ route('admin.dashboard') }}" class="menu-link">
										<span class="svg-icon menu-icon">
											<i class="fas fa-tachometer-alt"></i>
										</span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>


                <li class="menu-section">
                    <h4 class="menu-text">Manage</h4>
                    <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
                </li>
                @if(auth()->user()->super_admin == 1)
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'team') {
                    echo 'menu-item-active';
                }?>" aria-haspopup="true">
                    <a href="{{ route('team.index') }}" class="menu-link">
                                    <span class="svg-icon menu-icon">

                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
                                        <i class="fas fa-user-tie"></i>

                                        <!--end::Svg Icon-->
                                    </span>
                        <span class="menu-text">Business</span>
                    </a>
                </li>
                @endif
                @can("role-list")
                    <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'roles') {
                        echo 'menu-item-active';
                    }?>" aria-haspopup="true">
                        <a href="{{ route('roles.index') }}" class="menu-link">
										<span class="svg-icon menu-icon">
											<i class="fas fa-shield-alt"></i>
										</span>
                            <span class="menu-text">Roles & Permissions</span>
                        </a>
                    </li>
                @endcan
                @can("user-list")
                    <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'clients') {
                        echo 'menu-item-active';
                    }?>" aria-haspopup="true">
                        <a href="{{ route('clients.index') }}" class="menu-link">
										<span class="svg-icon menu-icon">
											<i class="fas fa-user"></i>
										</span>
                            <span class="menu-text">Users</span>
                        </a>
                    </li>
                @endcan
                @can("package-list")
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'packages') {echo 'menu-item-active';}?>" aria-haspopup="true">
                    <a href="{{ route('packages.index') }}" class="menu-link" >
                      <span class="svg-icon  menu-icon">
                        <i class="fa fa-briefcase"></i>
                      </span>
                      <span class="menu-text">Packages</span>
                    </a>
                </li>
                @endcan
                @can("member-list")
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'members') {echo 'menu-item-active';}?>" aria-haspopup="true">
                    <a href="{{ route('members.index') }}" class="menu-link" >
                      <span class="svg-icon  menu-icon">
                        <i class="fa fa-users"></i>
                      </span>
                      <span class="menu-text">Members</span>
                    </a>
                </li>
                @endcan
                @can("workout-list")
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'workouts') {echo 'menu-item-active';}?>" aria-haspopup="true">
                    <a href="{{ route('workouts.index') }}" class="menu-link" >
                      <span class="svg-icon  menu-icon">
                        <i class="fa fa-dumbbell"></i>
                      </span>
                      <span class="menu-text">Workouts</span>
                    </a>
                </li>
                @endcan
                @can("workout-type-list")
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'workout-types') {echo 'menu-item-active';}?>" aria-haspopup="true">
                    <a href="{{ route('workout-types.index') }}" class="menu-link" >
                      <span class="svg-icon  menu-icon">
                        <i class="fa fa-tags"></i>
                      </span>
                      <span class="menu-text">Workout Types</span>
                    </a>
                </li>
                @endcan
                @can("payment-list")
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'payments') {echo 'menu-item-active';}?>" aria-haspopup="true">
                    <a href="{{ route('payments.index') }}" class="menu-link" >
                      <span class="svg-icon  menu-icon">
                        <i class="fa fa-dollar-sign"></i>
                      </span>
                      <span class="menu-text">Payments</span>
                    </a>
                </li>
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'reports') {echo 'menu-item-active';}?>" aria-haspopup="true">
                    <a href="{{ route('reports.index') }}" class="menu-link" >
                      <span class="svg-icon  menu-icon">
                        <i class="fa fa-chart-bar"></i>
                      </span>
                      <span class="menu-text">Reports</span>
                    </a>
                </li>
                @endcan
                
                @if(auth()->user()->super_admin == 1)
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'subscription-packages') {echo 'menu-item-active';}?>" aria-haspopup="true">
                    <a href="{{ route('subscription-packages.index') }}" class="menu-link" >
                      <span class="svg-icon  menu-icon">
                        <i class="fa fa-box"></i>
                      </span>
                      <span class="menu-text">Subscription Packages</span>
                    </a>
                </li>
                @endif
                
                {{--<li class="menu-item menu-item-submenu" aria-haspopup="true" data-menu-toggle="hover">
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">

                            <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Bucket.svg-->
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"/>
                            <circle fill="#000000" opacity="0.3" cx="12" cy="12" r="10"/>
                            <path d="M12,11 C10.8954305,11 10,10.1045695 10,9 C10,7.8954305 10.8954305,7 12,7 C13.1045695,7 14,7.8954305 14,9 C14,10.1045695 13.1045695,11 12,11 Z M7.00036205,16.4995035 C7.21569918,13.5165724 9.36772908,12 11.9907452,12 C14.6506758,12 16.8360465,13.4332455 16.9988413,16.5 C17.0053266,16.6221713 16.9988413,17 16.5815,17 L7.4041679,17 C7.26484009,17 6.98863236,16.6619875 7.00036205,16.4995035 Z" fill="#000000" opacity="0.3"/>
                        </g>
                            </svg>

                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-text">Manage Cilents</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item menu-item-parent" aria-haspopup="true">
                                <span class="menu-link">
                                    <span class="menu-text">Themes</span>
                                </span>
                            </li>
                            <li class="menu-item" aria-haspopup="true">
                                <a href="layout/themes/aside-light.html" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Light Aside</span>
                                </a>
                            </li>
                            <li class="menu-item" aria-haspopup="true">
                                <a href="layout/themes/header-dark.html" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Dark Header</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>--}}
                @if(auth()->user()->super_admin == 1)
                <li class="menu-section">
                    <h4 class="menu-text">CMS</h4>
                    <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
                </li>
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'setting') {
                    echo 'menu-item-active';
                }?>" aria-haspopup="true">
                    <a href="{{ route('setting.index') }}" class="menu-link">
										<span class="svg-icon menu-icon">
											<i class="fas fa-cog"></i>
										</span>
                        <span class="menu-text">Settings</span>
                    </a>
                </li>
                @endif
            </ul>

            <!--end::Menu Nav-->
        </div>

        <!--end::Menu Container-->
    </div>

    <!--end::Aside Menu-->
</div>

<!--end::Aside-->
