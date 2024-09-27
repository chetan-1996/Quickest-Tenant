@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title','Plan Expired')
@push('styles')
    <style>
        .bg-primary {
            --ct-bg-opacity: 1;
            background-color: rgba( 114, 124, 245, 1 ) !important;
        }
        .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
            margin: 10px 0;
            font-weight: 700;
        }
        .h5, h5 {
            font-size: 0.875rem;
            line-height: 1.1;
        }
        .text-muted {
            --ct-text-opacity: 1;
            color: var(--ct-text-muted) !important;
        }
        .fw-bold {
            font-weight: 500 !important;
        }
    </style>
@endpush
@section('content')
    <div class="content-page">
        <div class="content">

            <!-- Start Content-->
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="page-title-right">
                                <div class="dropdown btn-group mb-2">

                                </div>
                            </div>


                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row justify-content-center">
                    <div class="col-xl-10">

                        <div class="text-center">
                            <img src="{{asset('images/maintenance.svg')}}" height="140" alt="File not found Image">
                            @if(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $companyExp->plan_end_date) > \Carbon\Carbon::now())
                                <h3 class=" text-dark mt-4">Your plan is expiring on {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$companyExp->plan_end_date)
                                ->format('d-m-y H:i:s')}}</h3>
                            @else
                                <h3 class=" text-dark mt-4">Your plan is expired on {{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $companyExp->plan_end_date)
                                ->format('d-m-y H:i:s')}}</h3>
                            @endif



                            <p class="text-muted">We're making the system more awesome. We'll be back shortly.</p>

                            <div class="row mt-5">

                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body pb-0">
                                            <div class="text-center mt-3 ps-1 pe-1">
                                                <i class="dripicons-question bg-primary maintenance-icon text-white mb-2"></i>
                                                <h5 class="text-uppercase fw-bold">Do you need Support?</h5>
                                                <p class="text-muted"><a href="mailto:support@quickestimate.co"
                                                                        class="text-muted fw-bold">support@quickestimate.co</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- end col-->

                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body pb-0">
                                            <div class="text-center mt-3 ps-1 pe-1">
                                                <i class="dripicons-phone bg-primary maintenance-icon text-white mb-2"></i>
                                                <h5 class="text-uppercase fw-bold">Mobile</h5>
                                                <p class="text-muted">+91 97242 94153</p>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- end col-->
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body pb-0">
                                            <div class="text-center mt-3 ps-1 pe-1">
                                                <i class=" dripicons-web bg-primary maintenance-icon text-white mb-2"></i>
                                                <h5 class="text-uppercase fw-bold">Website</h5>
                                                <p class="text-muted"><a href="https://quickestimate.co/" target="_blank">www.quickestimate.co</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- end col-->
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body pb-0">
                                            <div class="text-center mt-3 ps-1 pe-1">
                                                <i class="dripicons-clock bg-primary maintenance-icon text-white mb-2"></i>
                                                <h5 class="text-uppercase fw-bold">Time</h5>
                                                <p class="text-muted">9:00AM To 6:00PM</p>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- end col-->
                            </div> <!-- end row-->
                        </div> <!-- end /.text-center-->

                    </div> <!-- end col -->
                </div>

                    {{--<div class="row justify-content-center">
                        <div class="col-lg-4">
                            <div class="text-center">
                                <img src="{{asset('images/maintenance.svg')}}" height="90" alt="File not found Image">

                --}}{{--                <h1 class="text-error mt-4">404</h1>--}}{{--
                                <h4 class="text-uppercase text-danger mt-3">Your plan is expired</h4>
                                <p class="text-muted mt-3">It's looking like you may have taken a wrong turn. Don't worry... it
                                    happens to the best of us. Here's a
                                    little tip that might help you get back on track.</p>

                                <a class="btn btn-info mt-3" href="index.html"><i class="mdi mdi-reply"></i> Return Home</a>
                            </div> <!-- end /.text-center-->
                        </div> <!-- end col-->
                    </div>--}}
                    <!-- end row-->
                </div>
            </div>
        </div>

@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js')}}"></script>
    <script src="{{ asset('js/app.min.js')}}"></script> -->
    <script src="{{ asset('js/custom.js')}}"></script>
@endpush
