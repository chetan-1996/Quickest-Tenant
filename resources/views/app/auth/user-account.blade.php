@php
    $user_perm = App\Helpers\PermissionCheck::check_permission('role-list');
@endphp
@extends('app.layouts.app')
@section('title', 'Account')
@section('content')
    <link href="{{ asset('vendor/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        #imageUpload {
            display: none;
        }

        #profileImage {
            cursor: pointer;
        }

        #profile-container {
            width: 150px;
            height: 150px;
            overflow: hidden;
            -webkit-border-radius: 50%;
            -moz-border-radius: 50%;
            -ms-border-radius: 50%;
            -o-border-radius: 50%;
            border-radius: 50%;
        }

        #profile-container img {
            width: 150px;
            height: 150px;
        }
    </style>
    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
                            {{-- <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Hyper</a></li>
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                                    <li class="breadcrumb-item active">Invoice</li>
                                </ol>
                            </div> --}}
                            <h4 class="page-title">Account Setting</h4>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">

                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form id="account-setting-form" class="account-setting-form" method="post" data-parsley-validate
                                    enctype="multipart/form-data">
                                    <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-circle me-1"></i>
                                        Personal Info</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            {{-- @dd(Auth::user()->profile_icon) --}}
                                            <div class="mb-3" style="display: flex">
                                                <label for="name" class="form-label">Profile Photo</label>
                                                <div id="profile-container">
                                                    <img id="profileImage" src="{{(Auth::user()->profile_icon)?Storage::disk('s3')->temporaryUrl(Auth::user()->profile_icon,Carbon\Carbon::now()->addMinutes(20)): url('assets/images/users/avatar-1.jpg');}}" />
                                                </div>
                                                <input id="imageUpload" type="file" name="profile_icon" placeholder="Photo"
                                                    capture data-parsley-required="false" data-parsley-trigger="change"
                                                    accept="'image/jpg,image/jpeg,image/png,image/PNG,image/Png"
                                                    data-parsley-fileextension="jpg,png,jpeg" data-parsley-max-file-size="1024">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Full Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    placeholder="Enter full name" value="{{ $user->name }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="mobile_no" class="form-label">Mobile no <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="mobile_no" name="mobile_no"
                                                    placeholder="Enter mobile no" value="{{ $user->mobile_no }}">
                                            </div>
                                        </div> <!-- end col -->

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    placeholder="Enter email" value="{{ $user->email }}" readonly>
                                            </div>
                                        </div> <!-- end col -->
                                    </div> <!-- end row -->

                                    @if (!auth()->user()->company_id)
                                        <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-office-building me-1"></i>
                                            Company Info</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="company_name" class="form-label">Company Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="company_name" name="company_name"
                                                        placeholder="Enter company name" value="{{ $user->company_name }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="gst_no" class="form-label">Tax (GSTIN)</label>
                                                    <input type="text" class="form-control" id="gst_no" name="gst_no"
                                                        placeholder="Enter tax number" value="{{ $user->gst_no }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="cwebsite" class="form-label">Website</label>
                                                    <input type="text" class="form-control" id="website_link" name="website_link"
                                                        placeholder="Enter website url" value="{{ $user->website_link }}">
                                                </div>
                                            </div> <!-- end col -->
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="pincode" class="form-label">Pincode</label>
                                                    <input type="text" class="form-control" id="pincode" name="pincode"
                                                        placeholder="Enter pincode" value="{{ $user->pincode }}">
                                                </div>
                                            </div> <!-- end col -->


                                        </div> <!-- end row -->

                                        <div class="row">
                                            <div class="col-md-3 col-xl-3 col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Country </label>
                                                    <select data-toggle="select2" title="Country" name="country_id"
                                                        id="billing-country">
                                                        <option value="">Choose</option>
                                                        @foreach ($countries as $country)
                                                            <option value="{{ $country->id }}"
                                                                {{ $country->id == $user->country_id ? 'selected' : '' }}>
                                                                {{ $country->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-xl-3 col-lg-3">
                                                <div class="mb-3">
                                                    <label for="billing-state" class="form-label">State</label>
                                                    <select placeholder="Enter your state" data-toggle="select2" name="state_id"
                                                        title="State" id="billing-state">
                                                        <option value="">Choose</option>
                                                        @foreach ($states as $state)
                                                            <option value="{{ $state->id }}"
                                                                {{ $state->id == $user->state_id ? 'selected' : '' }}>
                                                                {{ $state->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    {{-- <input class="form-control" type="text"
                                                                    placeholder="Enter your state" id="billing-state" /> --}}
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-xl-3 col-lg-3">
                                                <div class="mb-3">
                                                    <label for="billing-city" class="form-label">Town /
                                                        City</label>
                                                    <input class="form-control" type="text" name="city_name"
                                                        placeholder="Enter your city name" id="billing-city"
                                                        value="{{ $user->city_name }}" />
                                                </div>
                                            </div>
                                            <!-- end col -->
                                            <div class="col-md-3 col-sm-3 col-lg-3">
                                                <div class="mb-3">
                                                    <label for="company_category" class="form-label">Business
                                                        Category</label>
                                                    <select data-toggle="select2" id="company_category" name="company_category"
                                                        disabled>
                                                        <option value="">Choose</option>
                                                        @foreach ($company_categories as $bus_category)
                                                            <option value="{{ $bus_category->id }}"
                                                                {{ $bus_category->id == $user->company_category ? 'selected' : '' }}>
                                                                {{ $bus_category->name }}</option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                            </div><!-- end col -->
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label for="address" class="form-label">Address</label>
                                                    <textarea class="form-control" id="address" name="address" rows="4" placeholder="Write something...">{{ $user->address }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                            {{--  </div> <!-- end row -->--}}

                            {{-- <div class="row">
                                            <!-- end col -->
                                        </div> <!-- end row --> --}}

                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-earth me-1"></i> Social
                            </h5>
                            <div class="row">
                                @if (!auth()->user()->company_id)
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="facebook_url" class="form-label">Facebook</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-facebook"></i></span>
                                            <input type="url" class="form-control" id="facebook_url" name="facebook_url"
                                                placeholder="Facebook url" data-parsley-type='url' data-parsley-trigger="input"
                                                value="{{ $user->facebook_url }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="twitter_url" class="form-label">Twitter</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-twitter"></i></span>
                                            <input type="url" class="form-control" id="twitter_url" name="twitter_url"
                                                placeholder="Twitter url" data-parsley-type='url' data-parsley-trigger="input"
                                                value="{{ $user->twitter_url }}">
                                        </div>
                                    </div>
                                </div> <!-- end col -->
                                {{-- </div> <!-- end row -->

                                        <div class="row"> --}}
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="instagram_url" class="form-label">Instagram</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-instagram"></i></span>
                                            <input type="url" class="form-control" id="instagram_url" name="instagram_url"
                                                placeholder="Instagram url" data-parsley-type='url' data-parsley-trigger="input"
                                                value="{{ $user->instagram_url }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="lin_link" class="form-label">Linkedin</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-linkedin"></i></span>
                                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url"
                                                placeholder="Linkedin url" data-parsley-type='url' data-parsley-trigger="input"
                                                value="{{ $user->linkedin_url }}">
                                        </div>
                                    </div>
                                </div> <!-- end col -->
                                @endif

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="call_url" class="form-label">Call</label>
                                        <div class="input-group">
                                                {{--<i class="mdi mdi-phone"></i>--}}
                                                <select class="form-select" id="call_code_url" name="call_code_url" style="width:30%">
                                                    <option value="">Choose</option>
                                                    @foreach ($countries_phone_code as $phone_code)
                                                    <option value="+{{  $phone_code->phonecode }}" {{ (('+'.$phone_code->phonecode == $user->call_code_url) || ($company_data->country_phonecode == $phone_code->phonecode)) ? 'selected' : '' }}>+{{ $phone_code->phonecode }}</option>
                                                    @endforeach
                                                </select>
                                            <input type="text" class="form-control" id="call_url" name="call_url" placeholder="Call" data-parsley-trigger="input" value="{{ ($user->call_url)?$user->call_url:$user->mobile_no; }}" style="width:70%">
                                        </div>
                                    </div>
                                </div> <!-- end col -->

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="gmail_url" class="form-label">Gmail</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-gmail"></i></span>
                                            <input type="text" class="form-control" id="gmail_url" name="gmail_url"
                                                placeholder="Gmail" data-parsley-trigger="input"
                                                value="{{ ($user->gmail_url)?$user->gmail_url:$user->email  }}">
                                        </div>
                                    </div>
                                </div> <!-- end col -->

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="whatsapp_url" class="form-label">Whatsapp</label>
                                        <div class="input-group">
                                            {{--<span class="input-group-text"><i class="mdi mdi-whatsapp"></i></span>--}}
                                            <select class="form-select" id="whatsapp_code_url" name="whatsapp_code_url" style="width:30%">
                                                <option value="">Choose</option>
                                                @foreach ($countries_phone_code as $phone_code)
                                                    <option value="+{{  $phone_code->phonecode }}" {{ (('+'.$phone_code->phonecode == $user->whatsapp_code_url) || ($company_data->country_phonecode == $phone_code->phonecode)) ? 'selected' : '' }}>+{{ $phone_code->phonecode }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" class="form-control" id="whatsapp_url" name="whatsapp_url"
                                                placeholder="Whatsapp" data-parsley-trigger="input"
                                                value="{{ ($user->whatsapp_url)?$user->whatsapp_url:$user->mobile_no }}" style="width:70%">
                                        </div>
                                    </div>
                                </div> <!-- end col -->

                            </div> <!-- end row -->
                            {{-- @endif--}}
                            </form>
                        </div> <!-- end card-body-->
                        </div> <!-- end card-body-->
                        <div class="card-footer">
                            <div class="text-end">
                                <input type="submit" id="account_setting_button" form="account-setting-form"
                                    class="btn btn-secondary" value="Save">
                            </div>

                        </div>
                    </div> <!-- end card -->
                </div> <!-- end col-->

                {{-- <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="header-title pt-2">Reset Password</h4>
                            </div>
                            <div class="card-body">
                                <form id="reset-password-form" class="reset-password-form" method="post" data-parsley-validate>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="old_password" class="form-label">Current Password <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="old_password"
                                                    name="old_password"
                                                    placeholder="Enter current password" data-parsley-required
                                                    data-parsley-trigger="input">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">New Password <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="new_password"
                                                    name="new_password"
                                                    placeholder="Enter new password" data-parsley-required
                                                    data-parsley-trigger="input">
                                            </div>
                                        </div> <!-- end col -->

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm Password <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="confirm_password"
                                                    name="confirm_password"
                                                    placeholder="Enter confirm password" data-parsley-required
                                                    data-parsley-equalto="#new_password" data-parsley-trigger="input"
                                                    data-parsley-equalto-message="Passwords do not match.">
                                            </div>
                                        </div> <!-- end col -->
                                    </div> <!-- end row -->
                                </form>

                            </div> <!-- end card-body-->
                            <div class="card-footer">
                                <div class="text-end">
                                    <input type="submit" id="reset_password_button" form="reset-password-form"
                                        class="btn btn-secondary" value="Save">
                                </div>

                            </div>
                        </div> <!-- end card -->
                    </div> <!-- end col--> --}}
            </div>
            <!-- end row -->

            </div>
        </div>
    </div>

@endsection
@push('scripts')
<!-- <script src="{{ asset('js/vendor.min.js') }}"></script> -->
    <script src="{{ asset('vendor/select2/js/select2.min.js')}}"></script>
    <!--<script src="{{ asset('assets/js/app.min.js') }}"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>                                                             
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script>
        $("#profileImage").click(function(e) {
            $("#imageUpload").click();
        });
        $('#company_category').select2();
        $('#billing-country').select2().on('select2:select', function (e) {
            var data = e.params.data;
            // selectCountry(this);
            // var selectedOption = $(this).find(':selected');
            // var dataId = selectedOption.data('id');
            console.log("Selected data-id: " + data.id);
            getStatesList(data.id);
            // $('#billing-state').select2().trigger('change')
            /*$("#country_id").val($('option:selected', this).data('id'));

            getStatesList($('option:selected', this).data('id'));

            // $('#whatsapp_country_code').val(data.id).change();
            $("#whatsapp_country_code option[data-id='" + dataId + "']").prop("selected", true);
            $('#whatsapp_country_code').select2({dropdownParent: $('#customer-modal')}).trigger('change');
            console.log($('#country_id').find(':selected').data('id'));


            // var optionToSelect = $('#whatsapp_country_code').find('option[data-id="' + dataId + '"]');
            // $('#whatsapp_country_code').select2('data', optionToSelect.data('data')).trigger('change'



            // var optionToSelect = $('#whatsapp_country_code').find('option[data-id="' + dataId + '"]');
            // $('#whatsapp_country_code').select2('data', optionToSelect.data('data')).trigger('change');






            /!* var option1 = $('#whatsapp_country_code').find('option[data-id="' + dataId + '"]');
             $('#whatsapp_country_code').val(option1.val()).trigger('change');*!/
            // console.log("Selected data-id: " + dataId);
            var option = $('#currency_name').find('option[data-id="' + dataId + '"]');
            // $('#currency_name').data('select2').trigger('select', { data: option.data('data') });
            $('#currency_name').val(option.val()).trigger('change');*/

            /* $(this).find('option[data-id="' + dataId + '"]')
             $('#currency_name').val(dataId).change();
             $('#currency_name').select2({dropdownParent: $('#customer-modal')}).trigger('change');*/
        });

        function fasterPreview(uploader) {
            if (uploader.files && uploader.files[0]) {
                var formData = new FormData();
                formData.append('profile_icon', uploader.files[0]);
                $.ajax({
                    cache: false,
                    contentType: false,
                    processData: false,
                    type: 'post',
                    data: formData,
                    url: "{{ route('tenant.update-image', ['tenant' => $segment]) }}",
                    success: function (response) {
                        // if (response.success) {
                            toastrSuccess('Profile photo uploaded...', 'Success');
                           /* $('#successMessage').show();
                            $('#successMessage').text(response.message);*/
                       /* } else {
                            $('#failedMessage').show();
                            $('#failedMessage').text(response.message);
                        }*/
                        /*setTimeout(() => {
                            location.reload();
                        }, 2000);*/
                    },
                    error: function(xhr, status, error) {
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
                       /* $("#account_setting_button").prop('disabled', false);
                        $("#account_setting_button").html(
                            '<i class="uil-arrow-circle-right"></i> Save');*/
                    },
                    complete: function(data) {
                        /*$("#account_setting_button").html('Save');
                        $("#account_setting_button").prop(
                            '<i class="uil-arrow-circle-right"></i> disabled', false);*/
                    }
                });
                $('#profileImage').attr('src',
                    window.URL.createObjectURL(uploader.files[0]));
            }
        }

        $("#imageUpload").change(function() {
            fasterPreview(this);
        });
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
/*
            $('#imageUpload').change(function (e) {
                profile = e.target.files[0];
                var formData = new FormData();
                formData.append('profile', profile);
                formData.append('user_id', user_id);


            });*/

            // window.Parsley.addValidator('maxFileSize', {
            //     validateString: function (_value, maxSize, parsleyInstance) {
            //         if (!window.FormData) {
            //             alert('You are making all developpers in the world cringe. Upgrade your browser!');
            //             return true;
            //         }
            //         var files = parsleyInstance.$element[0].files;
            //         return files.length != 1 || files[0].size <= maxSize * 1024;
            //     },
            //     requirementType: 'integer',
            //     messages: {
            //         en: 'This file should not be larger than %s Kb',
            //         fr: 'Ce fichier est plus grand que %s Kb.'
            //     }
            // });
            // window.ParsleyValidator.addValidator('fileextension', function (value, requirement) {
            //     var tagslistarr = requirement.split(',');
            //     var fileExtension = value.split('.').pop();
            //     var arr = [];
            //     $.each(tagslistarr, function (i, val) {
            //         arr.push(val);
            //     });
            //     if (jQuery.inArray(fileExtension, arr) != '-1') {
            //         //console.log("is in array");
            //         return true;
            //     } else {
            //         //console.log("is NOT in array");
            //         return false;
            //     }
            // }, 32)
            //     .addMessage('en', 'fileextension', 'The extension should be jpeg, jpg, png allowed');


            $('.account-setting-form').on('submit', function(e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('tenant.user-account', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        // data: $('.category-form').serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $("#account_setting_button").prop('disabled', true);
                            $("#account_setting_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function(data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            // $('#profile-modal').modal('toggle');
                            $("#account_setting_button").prop('disabled', true);
                            $("#account_setting_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                            window.location.reload();
                        },
                        error: function(xhr, status, error) {
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
                            $("#account_setting_button").prop('disabled', false);
                            $("#account_setting_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function(data) {
                            $("#account_setting_button").html('Save');
                            $("#account_setting_button").prop(
                                '<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });

            $('.reset-password-form').on('submit', function(e) {
                e.preventDefault();
                if ($(this).parsley().isValid()) {
                    $.ajax({
                        async: false,
                        type: 'POST',
                        url: '{{ route('tenant.user-reset-password', ['tenant' => $segment]) }}',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: new FormData(this),
                        // data: $('.category-form').serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $("#reset_password_button").prop('disabled', true);
                            $("#reset_password_button").html(
                                '<i class="mdi mdi-spin mdi-loading"></i> Loading...');
                        },
                        success: function(data) {
                            toastrSuccess('Successfully saved...', 'Success');
                            // $('#profile-modal').modal('toggle');
                            $("#reset_password_button").prop('disabled', true);
                            $("#reset_password_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                            window.location.reload();
                        },
                        error: function(xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText
                            switch (xhr.status) {
                                case 401:
                                    toastrError('Error in saving...', 'Error');
                                    break;
                                case 422:
                                    toastrInfo('The category is invalid.', 'Info');
                                    break;
                                case 409:
                                    toastrInfo('Current password wrong!', 'Warning');
                                    break;
                                default:
                                    toastrError('Error - ' + errorMessage, 'Error');
                            }
                            $("#reset_password_button").prop('disabled', false);
                            $("#reset_password_button").html(
                                '<i class="uil-arrow-circle-right"></i> Save');
                        },
                        complete: function(data) {
                            $("#reset_password_button").html('Save');
                            $("#reset_password_button").prop(
                                '<i class="uil-arrow-circle-right"></i> disabled', false);
                        }
                    });
                }
            });

            $('#country_id').on('change', function(e) {
                e.preventDefault();
                var country_id = jQuery(this).val();
                getStatesList(country_id);

            });

            $('#state_id').on('change', function(e) {
                e.preventDefault();
                var state_id = jQuery(this).val();
                getCityList(state_id);

            });

            getStatesList({{ $user->country_id }}, {{ $user->state_id }});
            getCityList({{ $user->state_id }}, {{ $user->city_id }});
        })

        // function get All States
        function getStatesList(country_id, seleted_id = 0) {
            $.ajax({
                async: false,
                url: "{{ url('get-states-by-country') }}",
                type: "POST",
                data: {
                    country_id: country_id
                },
                dataType: 'json',
                beforeSend: function() {
                    jQuery('select#state_id').find("option:eq(0)").html("Please wait..");
                },
                success: function(result) {
                    var options = '';
                    options += '<option value="">Choose</option>';
                    $.each(result.states, function(key, value) {
                        var selected = "";
                        if (value.id == seleted_id)
                            selected = 'selected';
                        options += '<option value="' + value.id + '" ' + selected + '>' + value.name +
                            '</option>';
                    });
                    $("#billing-state").html(options);
                    $('#billing-state').select2().trigger('change')
                    $('#city_id').html('<option value="">Choose</option>');
                },
                complete: function() {
                    // code
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }

        // function get All Cities
        function getCityList(state_id, seleted_id = 0) {
            $.ajax({
                async: false,
                url: "{{ url('get-cities-by-state') }}",
                type: "POST",
                data: {
                    state_id: state_id
                },
                dataType: 'json',
                beforeSend: function() {
                    jQuery('select#city_id').find("option:eq(0)").html("Please wait..");
                },
                success: function(result) {
                    var options = '';
                    options += '<option value="">Choose</option>';
                    $.each(result.cities, function(key, value) {
                        var selected = "";
                        if (value.id == seleted_id)
                            selected = 'selected';
                        options += '<option value="' + value.id + '" ' + selected + '>' + value.name +
                            '</option>';

                    });
                    $("#city_id").html(options);
                },
                complete: function() {
                    // code
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    </script>
@endpush
