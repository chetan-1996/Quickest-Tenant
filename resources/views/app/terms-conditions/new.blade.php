@extends('app.layouts.app')
@section('title','Term & Condition')
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
                                <li class="breadcrumb-item"><a href="{{route('tenant.dashboard', ['tenant' => $segment])}}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{route('tenant.term-condition.index', ['tenant' => $segment])}}">Term & Condition</a>
                                </li>
                                <li class="breadcrumb-item active">New</li>
                            </ol>

                        </div>
                        <h4 class="page-title">New Term & Condition</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form class="ps-3 pe-3 term-condition-form" id="term-condition-form" action="#">

                                <div class="mb-1">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="name" name="name" required=""
                                        placeholder="Enter name" autofocus>
                                    <input class="form-control" type="hidden" id="id" name="id" value="0">
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description"
                                            placeholder="Enter description"></textarea>
                                </div>

                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-primary" id="term-condition_button" type="submit"><i
                                            class="uil-arrow-circle-right"></i> Save
                                    </button>
                                </div>

                            </form>
                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
        </div>
    </div>
</div>




@endsection
@push('scripts')
    <!-- <script src="{{ asset('js/vendor.min.js')}}"></script>
    <script src="{{ asset('js/app.min.js')}}"></script> -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
    <script src="{{ asset('js/custom.js')}}"></script>
    <script src="{{ asset('ckeditor/ckeditor.js')}}"></script>
    <!-- third party js ends -->

    <!-- demo app -->
    {{--    <script src="{{ asset('js/pages/demo.datatable-init.js')}}"></script>--}}
    <!-- end demo js-->
    <script>
        CKEDITOR.on('instanceReady', function () {
            $('#description').attr('required', '');

            $.each(CKEDITOR.instances, function (instance) {
                CKEDITOR.instances[instance].on("change", function (e) {
                    for (instance in CKEDITOR.instances) {
                        CKEDITOR.instances[instance].updateElement();
                        $('form').parsley();
                    }
                });
            });
        });
        CKEDITOR.replace('description', {
            extraPlugins: 'editorplaceholder',
        });
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            formValition('#term-condition-form');
            $('.term-condition-form').on('submit', function (e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    var formData = $(this).serializeArray();
                    formData.push({name: 'description', value: CKEDITOR.instances.description.getData()});
                    $.ajax({
                        // async: false,
                        type: 'POST',
                        url: '{{route('tenant.term-condition.store', ['tenant' => $segment])}}',
                        data: formData,
                        dataType: "json",
                        beforeSend: function () {
                            $("#term-condition_button").prop('disabled', true);
                            $("#term-condition_button").html('<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                            //goBack();
                        },
                        success: function (data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            $("#term-condition_button").prop('disabled', false);
                            $("#term-condition_button").html('<i class="uil-arrow-circle-right"></i> Save');
                            window.location.href ='{{route("tenant.term-condition.index", ['tenant' => $segment])}}';
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Name already exist.', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#term-condition_button").prop('disabled', false);
                            $("#term-condition_button").html('<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function (data) {
                            $("#term-condition_button").html('Save');
                            $("#term-condition_button").prop('<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });
        });
    </script>
@endpush
