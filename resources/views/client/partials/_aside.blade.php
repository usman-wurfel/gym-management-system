
<!--begin::Aside-->
				<div class="aside aside-left aside-fixed d-flex flex-column flex-row-auto" id="kt_aside">

					<!--begin::Brand-->
					<div class="brand flex-column-auto" id="kt_brand">

						<!--begin::Logo-->
						<a href="{{ route('client.dashboard') }}" class="brand-logo">
							<img width="150" alt="Logo" src="{{ asset($auth_logo) }}" />
						</a>

						<!--end::Logo-->

						<!--begin::Toggle-->
						<button class="brand-toggle btn btn-sm px-0" id="kt_aside_toggle">
							<span class="svg-icon svg-icon svg-icon-xl">

								<!--begin::Svg Icon | path:assets/media/svg/icons/Navigation/Angle-double-left.svg-->
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
									<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
										<polygon points="0 0 24 0 24 24 0 24" />
										<path d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z" fill="#000000" fill-rule="nonzero" transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999)" />
										<path d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999)" />
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
						<div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">

							<!--begin::Menu Nav-->
							<ul class="menu-nav">
								<li class="menu-item <?php if (Request::segment(1) == 'client' && Request::segment(2) == 'dashboard'){echo 'menu-item-active'; }?>" aria-haspopup="true">
									<a href="{{ route('client.dashboard') }}" class="menu-link">
										<span class="svg-icon menu-icon">
											<!--begin::Svg Icon | path:assets/media/svg/icons/Design/Layers.svg-->
											<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
												<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
													<polygon points="0 0 24 0 24 24 0 24" />
													<path d="M12.9336061,16.072447 L19.36,10.9564761 L19.5181585,10.8312381 C20.1676248,10.3169571 20.2772143,9.3735535 19.7629333,8.72408713 C19.6917232,8.63415859 19.6104327,8.55269514 19.5206557,8.48129411 L12.9336854,3.24257445 C12.3871201,2.80788259 11.6128799,2.80788259 11.0663146,3.24257445 L4.47482784,8.48488609 C3.82645598,9.00054628 3.71887192,9.94418071 4.23453211,10.5925526 C4.30500305,10.6811601 4.38527899,10.7615046 4.47382636,10.8320511 L4.63,10.9564761 L11.0659024,16.0730648 C11.6126744,16.5077525 12.3871218,16.5074963 12.9336061,16.072447 Z" fill="#000000" fill-rule="nonzero" />
													<path d="M11.0563554,18.6706981 L5.33593024,14.122919 C4.94553994,13.8125559 4.37746707,13.8774308 4.06710397,14.2678211 C4.06471678,14.2708238 4.06234874,14.2738418 4.06,14.2768747 L4.06,14.2768747 C3.75257288,14.6738539 3.82516916,15.244888 4.22214834,15.5523151 C4.22358765,15.5534297 4.2250303,15.55454 4.22647627,15.555646 L11.0872776,20.8031356 C11.6250734,21.2144692 12.371757,21.2145375 12.909628,20.8033023 L19.7677785,15.559828 C20.1693192,15.2528257 20.2459576,14.6784381 19.9389553,14.2768974 C19.9376429,14.2751809 19.9363245,14.2734691 19.935,14.2717619 L19.935,14.2717619 C19.6266937,13.8743807 19.0546209,13.8021712 18.6572397,14.1104775 C18.654352,14.112718 18.6514778,14.1149757 18.6486172,14.1172508 L12.9235044,18.6705218 C12.377022,19.1051477 11.6029199,19.1052208 11.0563554,18.6706981 Z" fill="#000000" opacity="0.3" />
												</g>
											</svg>

											<!--end::Svg Icon-->
										</span>
										<span class="menu-text">Dashboard</span>
									</a>
								</li>



								<li class="menu-section">
									<h4 class="menu-text">Detail</h4>
									<i class="menu-icon ki ki-bold-more-hor icon-md"></i>
								</li>
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'clients'){echo 'menu-item-active'; }?>" aria-haspopup="true">
                  <a  href="#" class="menu-link">
										<span class="svg-icon menu-icon">

											<!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
											<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
													 <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                    <rect id="bound" x="0" y="0" width="24" height="24"/>
                                                                    <path d="M8,3 L8,3.5 C8,4.32842712 8.67157288,5 9.5,5 L14.5,5 C15.3284271,5 16,4.32842712 16,3.5 L16,3 L18,3 C19.1045695,3 20,3.8954305 20,5 L20,21 C20,22.1045695 19.1045695,23 18,23 L6,23 C4.8954305,23 4,22.1045695 4,21 L4,5 C4,3.8954305 4.8954305,3 6,3 L8,3 Z" id="Combined-Shape" fill="#000000" opacity="0.3"/>
                                                                    <path d="M11,2 C11,1.44771525 11.4477153,1 12,1 C12.5522847,1 13,1.44771525 13,2 L14.5,2 C14.7761424,2 15,2.22385763 15,2.5 L15,3.5 C15,3.77614237 14.7761424,4 14.5,4 L9.5,4 C9.22385763,4 9,3.77614237 9,3.5 L9,2.5 C9,2.22385763 9.22385763,2 9.5,2 L11,2 Z" id="Combined-Shape" fill="#000000"/>
                                                                    <rect id="Rectangle-152" fill="#000000" opacity="0.3" x="10" y="9" width="7" height="2" rx="1"/>
                                                                    <rect id="Rectangle-152-Copy-2" fill="#000000" opacity="0.3" x="7" y="9" width="2" height="2" rx="1"/>
                                                                    <rect id="Rectangle-152-Copy-3" fill="#000000" opacity="0.3" x="7" y="13" width="2" height="2" rx="1"/>
                                                                    <rect id="Rectangle-152-Copy" fill="#000000" opacity="0.3" x="10" y="13" width="7" height="2" rx="1"/>
                                                                    <rect id="Rectangle-152-Copy-5" fill="#000000" opacity="0.3" x="7" y="17" width="2" height="2" rx="1"/>
                                                                    <rect id="Rectangle-152-Copy-4" fill="#000000" opacity="0.3" x="10" y="17" width="7" height="2" rx="1"/>
                                                                </g>
											</svg>

                      <!--end::Svg Icon-->
										</span>
                    <span class="menu-text">Courses</span>
                  </a>
                </li>
                <li class="menu-item <?php if (Request::segment(1) == 'admin' && Request::segment(2) == 'clients'){echo 'menu-item-active'; }?>" aria-haspopup="true">
                  <a  href="#" class="menu-link">
										<span class="svg-icon menu-icon">

											<!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
											<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
												<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                        <polygon id="Shape" points="0 0 24 0 24 24 0 24"/>
                                                                        <path d="M9.26193932,16.6476484 C8.90425297,17.0684559 8.27315905,17.1196257 7.85235158,16.7619393 C7.43154411,16.404253 7.38037434,15.773159 7.73806068,15.3523516 L16.2380607,5.35235158 C16.6013618,4.92493855 17.2451015,4.87991302 17.6643638,5.25259068 L22.1643638,9.25259068 C22.5771466,9.6195087 22.6143273,10.2515811 22.2474093,10.6643638 C21.8804913,11.0771466 21.2484189,11.1143273 20.8356362,10.7474093 L17.0997854,7.42665306 L9.26193932,16.6476484 Z" id="Path-94-Copy" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(14.999995, 11.000002) rotate(-180.000000) translate(-14.999995, -11.000002) "/>
                                                                        <path d="M4.26193932,17.6476484 C3.90425297,18.0684559 3.27315905,18.1196257 2.85235158,17.7619393 C2.43154411,17.404253 2.38037434,16.773159 2.73806068,16.3523516 L11.2380607,6.35235158 C11.6013618,5.92493855 12.2451015,5.87991302 12.6643638,6.25259068 L17.1643638,10.2525907 C17.5771466,10.6195087 17.6143273,11.2515811 17.2474093,11.6643638 C16.8804913,12.0771466 16.2484189,12.1143273 15.8356362,11.7474093 L12.0997854,8.42665306 L4.26193932,17.6476484 Z" id="Path-94" fill="#000000" fill-rule="nonzero" transform="translate(9.999995, 12.000002) rotate(-180.000000) translate(-9.999995, -12.000002) "/>
                                                                    </g>
											</svg>

                      <!--end::Svg Icon-->
										</span>
                    <span class="menu-text">Grades</span>
                  </a>
                </li>
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

								<li class="menu-section">
									<h4 class="menu-text">Setting</h4>
									<i class="menu-icon ki ki-bold-more-hor icon-md"></i>
								</li>
								<li class="menu-item <?php if (Request::segment(1) == 'client' && Request::segment(2) == 'profile'){echo 'menu-item-active'; }?>" aria-haspopup="true">
									<a  href="{{ route('client-profile') }}" class="menu-link">
										<span class="svg-icon menu-icon">

											<!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
											<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
												 <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect id="bound" x="0" y="0" width="24" height="24"/>
                                                    <path d="M5,8.6862915 L5,5 L8.6862915,5 L11.5857864,2.10050506 L14.4852814,5 L19,5 L19,9.51471863 L21.4852814,12 L19,14.4852814 L19,19 L14.4852814,19 L11.5857864,21.8994949 L8.6862915,19 L5,19 L5,15.3137085 L1.6862915,12 L5,8.6862915 Z M12,15 C13.6568542,15 15,13.6568542 15,12 C15,10.3431458 13.6568542,9 12,9 C10.3431458,9 9,10.3431458 9,12 C9,13.6568542 10.3431458,15 12,15 Z" id="Combined-Shape" fill="#000000"/>
                                                </g>
											</svg>

											<!--end::Svg Icon-->
										</span>
										<span class="menu-text">Profile</span>
									</a>
								</li>

							</ul>

							<!--end::Menu Nav-->
						</div>

						<!--end::Menu Container-->
					</div>

					<!--end::Aside Menu-->
				</div>

				<!--end::Aside-->
