@extends('client.layouts.master')
@section('title',$title)
@section('content')
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Dashboard-->
            <!--begin::Row-->
            <div class="row">
                @php
                    $users = \App\Models\User::where("is_admin",0)->get()->count();
                    $courses =  \App\Models\Course::count();;
                    $completed = 10;
                @endphp

                <div class="col-lg-10 col-xxl-10">
                    <!--begin::Mixed Widget 1-->
                    <div class="card card-custom bg-gray-100 card-stretch gutter-b">
                        <!--begin::Header-->
                        <div class="card-header border-0 bg-danger py-5">
                            <h3 class="card-title font-weight-bolder text-white">Stats</h3>
                            <div class="card-toolbar">
                            </div>
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body p-0 position-relative overflow-hidden">
                            <!--begin::Chart-->
                            <div id="kt_mixed_widget_1_chart" class="card-rounded-bottom bg-danger"
                                 style="height: 200px"></div>
                            <!--end::Chart-->
                            <!--begin::Stats-->
                            <div class="card-spacer mt-n25">
                                <!--begin::Row-->
                                <div class="row m-0">
                                    <div class="col bg-light-primary px-6 py-8 rounded-xl mr-7 mb-7">
															<span
                                                                class="svg-icon svg-icon-3x svg-icon-primary d-block my-2">
																<!--begin::Svg Icon | path:assets/media/svg/icons/Media/Equalizer.svg-->
																<svg xmlns="http://www.w3.org/2000/svg"
                                                                     xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                     width="24px" height="24px" viewBox="0 0 24 24"
                                                                     version="1.1">
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
                                        <a href="#" class="text-primary font-weight-bold font-size-h6">Total Courses ({{$courses}})</a>
                                    </div>
                                    <div class="col bg-light-success px-6 py-8 rounded-xl mb-7">
															<span
                                                                class="svg-icon svg-icon-3x svg-icon-success d-block my-2">
																<!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
																<svg xmlns="http://www.w3.org/2000/svg"
                                                                     xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                     width="24px" height="24px" viewBox="0 0 24 24"
                                                                     version="1.1">
																	<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                        <polygon id="Shape" points="0 0 24 0 24 24 0 24"/>
                                                                        <path d="M9.26193932,16.6476484 C8.90425297,17.0684559 8.27315905,17.1196257 7.85235158,16.7619393 C7.43154411,16.404253 7.38037434,15.773159 7.73806068,15.3523516 L16.2380607,5.35235158 C16.6013618,4.92493855 17.2451015,4.87991302 17.6643638,5.25259068 L22.1643638,9.25259068 C22.5771466,9.6195087 22.6143273,10.2515811 22.2474093,10.6643638 C21.8804913,11.0771466 21.2484189,11.1143273 20.8356362,10.7474093 L17.0997854,7.42665306 L9.26193932,16.6476484 Z" id="Path-94-Copy" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(14.999995, 11.000002) rotate(-180.000000) translate(-14.999995, -11.000002) "/>
                                                                        <path d="M4.26193932,17.6476484 C3.90425297,18.0684559 3.27315905,18.1196257 2.85235158,17.7619393 C2.43154411,17.404253 2.38037434,16.773159 2.73806068,16.3523516 L11.2380607,6.35235158 C11.6013618,5.92493855 12.2451015,5.87991302 12.6643638,6.25259068 L17.1643638,10.2525907 C17.5771466,10.6195087 17.6143273,11.2515811 17.2474093,11.6643638 C16.8804913,12.0771466 16.2484189,12.1143273 15.8356362,11.7474093 L12.0997854,8.42665306 L4.26193932,17.6476484 Z" id="Path-94" fill="#000000" fill-rule="nonzero" transform="translate(9.999995, 12.000002) rotate(-180.000000) translate(-9.999995, -12.000002) "/>
                                                                    </g>
																</svg>
                                                                <!--end::Svg Icon-->
															</span>
                                        <a href="#" class="text-success font-weight-bold font-size-h6 mt-2">Completed Courses
                                            ({{$completed}})</a>
                                    </div>
                                </div>
                                <!--end::Row-->
                                <!--begin::Row-->

                                <!--end::Row-->
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Mixed Widget 1-->
                </div>

            </div>
            <!--end::Row-->
            <!--begin::Row-->

            <!--end::Row-->
            <!--end::Dashboard-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Entry-->
@endsection
