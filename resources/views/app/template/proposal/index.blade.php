@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title','Proposal')
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
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                {{-- <li class="breadcrumb-item"><a href="{{route('quotes.index')}}">Proposal</a></li>--}}
                                <li class="breadcrumb-item active">list</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Proposal list</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                @if(!empty($data) && $data->count())
                    @foreach($data as $key => $value)
                        <div class="col-md-4 col-lg-2">
                            <!-- project card -->
                            <div class="card d-block">
                                <div class="card-body">
                                {{-- <div class="dropdown card-widgets">
                                        <a href="#" class="dropdown-toggle arrow-none" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                            <i class="dripicons-gear"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            <!-- item-->
                                            <a href="{{route('proposal.new-create', ['id'=>$value->id])}}" class="dropdown-item"><i
                                                    class="mdi mdi-pencil me-1"></i>Edit</a>
                                            <!-- item-->
                                            <!-- item-->
                                            <a href="{{route('proposal.pdf-preview')}}" class="dropdown-item" target="_blank"><i class="uil uil-web-grid-alt"></i> Preview</a>
                                            <!-- item-->
                                        </div>
                                    </div>--}}
                                    <!-- project title-->
                                    <h4 class="mt-0">
                                        @if(in_array('template-setting', $user_perm))
                                                <a href="{{route('proposal.new-create', ['id'=>$value->id])}}" class="text-title">{{ $value->template_name }}</a>
                                        @else
                                            <a href="javascript:void(0);" onclick="accessDeniedOpenModal('#access-denied-modal','You don’t have permission to access on the updated template setting.')" class="text-title">{{ $value->template_name }}</a>
                                        @endif

                                    </h4>
                                    {{-- <div class="badge bg-success">Finished</div>--}}

                                    <img class="card-img-top" src="{{(Storage::disk('s3')->exists('public/'.$value->company_id. '/documents/proposal-sample.jpeg'))?Storage::disk('s3')->url('public/'.$value->company_id. '/documents/proposal-sample.jpeg'):Storage::url('template/proposal-template.png')}}" alt="Proposal Template">
                                    <div class="text-center">
                                        @if(in_array('template-setting', $user_perm))
                                            <a href="{{route('proposal.new-create', ['id'=>$value->id])}}" class="btn btn-sm px-1 btn btn-dark mt-2"><i class="mdi mdi-pencil"></i> Edit</a>
                                        @else
                                            <a href="javascript:void(0);" onclick="accessDeniedOpenModal('#access-denied-modal','Oops! You don’t have permission to access on the updated template setting.')" class="btn btn-sm px-1 btn btn-dark mt-2"><i class="mdi mdi-pencil"></i> Edit</a>
                                        @endif

                                        <a href="{{route('proposal.pdf-preview')}}" target="_blank" class="btn btn-sm px-1 btn btn-light ms-2 mt-2"><i class="uil uil-web-grid-alt"></i> Preview</a>
                                    </div>
                                    <!-- project detail-->
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col -->
                    @endforeach
                @endif
                    {{ $data->links('app.vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js')}}"></script>
    <script src="{{ asset('js/app.min.js')}}"></script> -->
    <script src="{{ asset('js/custom.js')}}"></script>
    <!-- third party js ends -->

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
@endpush
