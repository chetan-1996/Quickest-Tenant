function toastrSuccess(message, title = null) {
    $.NotificationApp.send(title, message, "top-right", "rgba(0,0,0,0.2)", "success", 3e3, 1)
}

function toastrError(message, title = null) {
    $.NotificationApp.send(title, message, "top-right", "rgba(0,0,0,0.2)", "error", 3e3, 1)
}

function toastrInfo(message, title = null) {
    $.NotificationApp.send(title, message, "top-right", "rgba(0,0,0,0.2)", "info", 3e3, 1)
}

function toastrErrorWithText(message, title = null,) {
    $.NotificationApp.send(title, message, "top-right", "rgba(0,0,0,0.2)", "warning", 3e3, 1)
}

function toastrWarning(message, title = null) {
    $.NotificationApp.send(title, message, "top-right", "rgba(0,0,0,0.2)", "warning", 3e3, 1)
}

function goBack() {
    history.back()
}

//Open Modal
function openModal(modalName, modalTitle, modalForm, modalTitleName, id = 0, flag = 0) {
    $(modalTitleName).text(modalTitle);
    $(modalForm).parsley().reset();
    $(modalName).modal('show');
    if (flag == 1) {
        $("#image_one").val(null);
        $("#image_two").val(null);
        $("#image_three").val(null);
    }

    if (flag == 2) {
        $('#image_one, #image_two, #image_three').prop('required', true);
    }

    if (flag == 3) {
        $('.cust_company_name_div').hide();
        $('#city_id').html('<option value="">Choose</option>');
        $('#state_id').html('<option value="">Choose</option>');
        getStatesList(101);
        $("#customer-modal .advance-option").removeClass('d-none');
        $("#customer-modal .advance-option-div").addClass('d-none');
    }

    if (flag == 4) {
        // $("#sale_price").prop('readonly', true);
        // $("#cost_price").prop('readonly', true);
        $(".tax-div").hide();
        CKEDITOR.instances.technical_specification.setData('');
    }

    if (flag == 5) {
        CKEDITOR.instances['description'].setData('');
    }

    if (flag == 6) {
        if (id == 0) {
            $("#user_button").html("<i class='uil-arrow-circle-right'></i> Invite")
        }
    }
    resetForm(modalForm)
    $('#id').val(id);
}

function accessDeniedOpenModal(modalName, contentText) {
    $('#content-p').text(contentText);
    $(modalName).modal('show');
}

$('input[name="next_follow_up"]').click(function () {
    if ($(this).val() == 'Yes') {
        $(".follow-up-div").show();
        $("#followup_time").prop('required', true);
        $("#followup_date").prop('required', true);
    } else {
        $(".follow-up-div").hide();
        $("#followup_time").prop('required', false);
        $("#followup_date").prop('required', false);
    }
});

function openFollowUpModal(modalName, modalTitle, modalForm, modalTitleName, estimate_id = 0, id = 0, estimate_no, estimate_status, url, est_type = 0, customer_name, mobile_no) {
    let flg = 'denc';
    if (est_type) {
        flg = 'enc';
    }
    $("#event_id").val(0);
    follow_up_list(estimate_id, url, flg)
    $(modalTitleName).text(modalTitle);
    $(modalForm).parsley().reset();
    $(modalName).modal('show');
    resetForm(modalForm)
    $('#id').val(id);
    $('.estimate_customer_span').html(customer_name + "/" + mobile_no);
    $('.estimate_span').html('#' + estimate_no);
    $('#estimate_id').val(estimate_id);
    // $('#event_id').val(event_id);
    $('#estimate_status').val(estimate_status);
    $(".follow-up-div").show();
    $("#followup_time").prop('required', true);
    $("#followup_date").prop('required', true);
}

//Reset form validation
function resetForm(formName) {
    $(formName)[0].reset();
    $('#id').val('0');
}

//Form validation
function formValition(e) {
    $(e).parsley();
}

function resetFormValidation(formName) {
    $(formName).parsley().reset();
}

function remove_id(id, url, tableName = '') {
    if (id.length <= 0) {
        toastrWarning('Please select at least one record', 'Warning');
    } else {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            type: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#3085D6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
            confirmButtonClass: "btn btn-primary",
            cancelButtonClass: "btn btn-danger ml-1",
            buttonsStyling: !1,
            preConfirm: function () {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: { id: id },
                    dataType: "json",
                    success: function (data, textStatus, jqXHR) {
                        if (tableName) {
                            $(tableName).DataTable().ajax.reload();
                            $("#remove_" + id).closest("tr").hide('slow');
                            $('#select_all').prop('checked', false);
                            $("#select_count").html(0);
                            // toastrSuccess('Successfully removed');
                        }
                        if (!tableName) {
                            // toastrSuccess('Successfully removed');
                            location.href = SITEURL + '/lead'
                        }

                    },
                    error: function (xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText
                        switch (xhr.status) {
                            case 401:
                                toastrError('Error in saving...', 'Error');
                                break;
                            case 422:
                                toastrInfo('Please contact developer.', 'Info');
                                break;
                            case 409:
                                toastrInfo('Name already exist.', 'Warning');
                                break;
                            default:
                                if (tableName) {
                                    $(tableName).DataTable().ajax.reload();
                                }
                                toastrErrorWithText(xhr.responseJSON, 'Warning');
                        }
                    },
                    complete: function (data) {
                    }
                });
            }
        }).then(function (t) {
            t.value && Swal.fire({
                title: "Success",
                text: "Your record has been updated.",
                type: "success", showConfirmButton: !1,
                timer: 1500,
                confirmButtonClass: "btn btn-success",
                showConfirmButton: false
            })
        });
    }
}

function change_status(id, status, url, tableName, flag = 0, old_value = null) {
    var tmp_status = 'deactive';
    if (status == 0) {
        tmp_status = 'active';
    }

    if (flag)
        tmp_status = status;
    if (id.length <= 0) {
        toastrWarning('Please select at least one record', 'Warning');
    } else {
        Swal.fire({
            title: "Are you sure?",
            // text: "You won't be able to revert this!",
            text: "Are you sure want to " + tmp_status,
            type: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#3085D6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, change it!",
            confirmButtonClass: "btn btn-primary",
            cancelButtonClass: "btn btn-danger ml-1",
            buttonsStyling: !1,
            preConfirm: function () {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: { id: id, status: status },
                    dataType: "json",
                    success: function (data, textStatus, jqXHR) {
                        // toastrSuccess('Successfully updated');
                        $(tableName).DataTable().ajax.reload();
                        $('#select_all').prop('checked', false);
                        $("#select_count").html(0);
                    },
                    error: function (xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText
                        console.log(xhr.responseJSON.errors);
                        switch (xhr.status) {
                            case 401:
                                toastrError('Error in saving...', 'Error');
                                break;
                            case 422:
                                toastrInfo('Please contact developer.', 'Info');
                                break;
                            case 409:
                                toastrInfo('Name already exist.', 'Warning');
                                break;
                            default:
                                if (tableName) {
                                    $(tableName).DataTable().ajax.reload();
                                }
                                toastrErrorWithText(xhr.responseJSON.errors, 'Warning');
                        }
                    },
                    complete: function (data) {
                    }
                });
            }
        }).then(function (t) {
            if (t.isConfirmed)
                t.value && Swal.fire({
                    title: "Success",
                    text: "Your record has been updated.",
                    type: "success",
                    showConfirmButton: !1,
                    timer: 1500,
                    confirmButtonClass: "btn btn-success",
                    showConfirmButton: false
                })
            else
                var oldValueArr = old_value.split('_');
            $('#example-select_' + oldValueArr[1]).val(oldValueArr[0]);
        });
    }
}

function change_status_user(id, status, url, tableName, flag = 0, old_value = null) {
    var tmp_status = 'deactive';
    if (status == 0) {
        tmp_status = 'active';
    }

    if (flag)
        tmp_status = status;
    if (id.length <= 0) {
        toastrWarning('Please select at least one record', 'Warning');
    } else {
        /*Swal.fire({
            title: "Are you sure?",
            // text: "You won't be able to revert this!",
            text: "Are you sure want to " + tmp_status,
            type: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#3085D6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, change it!",
            confirmButtonClass: "btn btn-primary",
            cancelButtonClass: "btn btn-danger ml-1",
            buttonsStyling: !1,
            preConfirm: function () {*/
                $.ajax({
                    type: "POST",
                    url: url,
                    data: { id: id, status: status },
                    dataType: "json",
                    success: function (data, textStatus, jqXHR) {
                        // toastrSuccess('Successfully updated');
                        $(tableName).DataTable().ajax.reload();
                        $('#select_all').prop('checked', false);
                        $("#select_count").html(0);
                    },
                    error: function (xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText
                        console.log(xhr.responseJSON.errors);
                        switch (xhr.status) {
                            case 401:
                                toastrError('Error in saving...', 'Error');
                                break;
                            case 422:
                                toastrInfo('Please contact developer.', 'Info');
                                break;
                            case 409:
                                toastrInfo('Name already exist.', 'Warning');
                                break;
                            default:
                                if (tableName) {
                                    $(tableName).DataTable().ajax.reload();
                                }

                                Swal.fire({
                                    icon: "error",
                                    // title: "Oops...",
                                    type: "error",
                                    text: xhr.responseJSON.errors,
                                    showConfirmButton: !1,
                                   footer: '<a href="'+SITEURL+'/plan" class="btn btn-primary">Add Users</a>'
                                });

                                // toastrErrorWithText(xhr.responseJSON.errors, 'Warning');
                        }
                    },
                    complete: function (data) {
                    }
                });
           /* }
        }).then(function (t) {
            if (t.isConfirmed)
                t.value && Swal.fire({
                    title: "Success",
                    text: "Your record has been updated.",
                    type: "success",
                    showConfirmButton: !1,
                    timer: 1500,
                    confirmButtonClass: "btn btn-success",
                    showConfirmButton: false
                })
            else
                var oldValueArr = old_value.split('_');
            $('#example-select_' + oldValueArr[1]).val(oldValueArr[0]);
        });*/
    }
}

function fn_follow_up_history(url) {
    localStorage.setItem('start', moment().subtract(89, 'days'));
    localStorage.setItem('end', moment().subtract(1, 'days'));
    window.location.href = url;
}

$(document).on('click', '#select_all', function () {
    // $(".single_checkbox").prop("checked", this.checked);
    // $("#select_count").html($("input.single_checkbox:checked").length);
    var isChecked = $(this).prop('checked');

    $(".single_checkbox").each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop("checked", isChecked);
        }
    });
    $("#select_count").hide();
    if($("input.single_checkbox:checked").length > 0)
        $("#select_count").show();

    $("#select_count").html($("input.single_checkbox:checked").length);
});

$(document).on('click', '.single_checkbox', function () {
    if ($('.single_checkbox:checked').length == $('.single_checkbox').length) {
        $('#select_all').prop('checked', true);
    } else {
        $('#select_all').prop('checked', false);
    }
    $("#select_count").hide();
    if($("input.single_checkbox:checked").length > 0)
        $("#select_count").show();
    $("#select_count").html($("input.single_checkbox:checked").length);
});

$(document).on('click', '#select_all_fb', function () {
    // $(".single_checkbox").prop("checked", this.checked);
    // $("#select_count").html($("input.single_checkbox:checked").length);
    var isChecked = $(this).prop('checked');

    $(".single_checkbox_fb").each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop("checked", isChecked);
        }
    });
    // $("#select_count").hide();
    // if($("input.single_checkbox_fb:checked").length > 0)
        // $("#select_count").show();

    // $("#select_count").html($("input.single_checkbox:checked").length);
});

$(document).on('click', '.single_checkbox_fb', function () {
    if ($('.single_checkbox_fb:checked').length == $('.single_checkbox_fb').length) {
        $('#select_all_fb').prop('checked', true);
    } else {
        $('#select_all_fb').prop('checked', false);
    }
    // $("#select_count").hide();
    // if($("input.single_checkbox_fb:checked").length > 0)
        // $("#select_count").show();
    // $("#select_count").html($("input.single_checkbox:checked").length);
});


$(document).on('click', '#tradeindia_select_all', function () {
    var isChecked = $(this).prop('checked');
    $(".tradeindia_single_checkbox").each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop("checked", isChecked);
        }
    });

});

$(document).on('click', '.single_checkbox_fb', function () {
    if ($('.tradeindia_single_checkbox:checked').length == $('.tradeindia_single_checkbox').length) {
        $('#tradeindia_select_all').prop('checked', true);
    } else {
        $('#tradeindia_select_all').prop('checked', false);
    }
});


function getDateWiseFollowUpList(url, date = null, follow_up_url) {

    $.ajax({
        async: false,
        type: "GET",
        url: url,
        data: { date: date },
        dataType: "json",
        beforeSend: function () {
            $('.loader').show();
        },

        success: function (res) {
            var tmpHtml = '';
            $.each(res.data, function (i, currProgram) {
                tmpHtml += '<h5 class="m-0 pb-2 fs-4">' +
                    ' <span class="badge badge-info-lighten p-1">' + i + '</span>' +
                    '</h5>';
                $.each(currProgram, function (key, val) {
                    tmpStr = '<span class="badge badge-outline-' + val.color + '">Take Follow up on :' + moment(val.start_date, 'DD-MM-YYYY HH:mm:ss').format("DD MMMM, YYYY HH:mm A") + '</span>';
                    tmpStatus = '<li class="list-inline-item"><i class="mdi mdi-circle-small"></i></li>' +
                        '<li class="list-inline-item"><span class="badge badge-outline-dark"><i class="mdi mdi-check-decagram"></i> ' + val.status + '</span></li>';
                    htmlAction = "";
                    if (val.user_loggedin_id == val.user_id)
                        htmlAction = "openFollowUpModal('#follow-up-modal','Schedule Follow Up','#follow-up-form','.modal-title'," + val.estimate_id + "," + val.id + ",'" + val.estimate_no + "','" + val.status + "','" + follow_up_url + "',0,'" + val.customer_name + "','" + val.mobile_no + "')";

                    if (val.next_follow_up == 1) {
                        tmpStr = '<span class="badge badge-outline-primary"><i class="mdi mdi-close-box"></i> ' + val.status + '</span>';
                        tmpStatus = '';
                        htmlAction = '';
                    }
                    var tmpNotes = val.notes;
                    if (val.notes.length > 50) {
                        tmpNotes = val.notes.slice(0, 50) + "...";
                    }
                    let tmpStatusColor = '';
                    if (val.status == 'Draft') {
                        tmpStatusColor = 'secondary';
                    }
                    if (val.status == 'Sent') {
                        tmpStatusColor = 'primary';
                    }
                    if (val.status == 'Inprogress') {
                        tmpStatusColor = 'warning';
                    }
                    if (val.status == 'Accept') {
                        tmpStatusColor = 'success';
                    }
                    if (val.status == 'Decline') {
                        tmpStatusColor = 'danger';
                    }
                    tmpHtml += '<div className="inbox-item rounded" style="width:fix-content;border-radius: 0.25rem!important;padding: 0rem 0;border: 1px solid var(--ct-border-color)!important;margin-bottom: 0.75rem!important;">' +

                        '<table class="table table-borderless table-wrap table-hover table-sm mb-0 table-responsive-stack" id="table-one">' +
                        '<tr>' +
                        '<td>' +
                        '<h5 class="font-15 text-dark my-1"><i class="mdi mdi-clock-outline"></i> ' + moment(val.created_datetime, 'DD-MM-YYYY HH:mm:ss').format("HH:mm A") + '</h5>' +
                        '<span class="text-muted font-13">' + val.notes + '</span>' +
                        '</td>' +
                        '<td>' +
                        '<span class="text-dark fw-bold font-15">' + val.customer_name + '</span>' +
                        '<h5 class="font-14 mt-1 fw-bold text-primary">' + val.estimate_no + '</h5>' +
                        '</td>' +
                        '<td>' +
                        '<span class="text-dark fw-bold font-15">' + val.mobile_no + '</span> <br>' +
                        '<span class="text-' + tmpStatusColor + ' fw-bold">' + val.status + '</span>' +
                        '</td>' +
                        '<td>' +
                        '<span class="text-dark fw-bold font-15">Created</span>' +
                        '<h5 class="font-14 mt-1 fw-bold">' + moment(val.created_datetime, 'DD-MM-YYYY HH:mm:ss').format("DD MMMM, YYYY HH:mm A") + '</h5>' +
                        '</td>' +
                        '<td>' +
                        '<span class="text-dark fw-bold font-15">Next</span>' +
                        '<h5 class="font-14 mt-1 fw-bold">' + moment(val.start_date, 'DD-MM-YYYY HH:mm:ss').format("DD MMMM, YYYY HH:mm A") + '</h5>' +
                        '</td>' +
                        '<td class="table-action text-dark">' +
                        '<a href="javascript:void(0);" onclick="' + htmlAction + '" class="action-icon text-dark">' +
                        '<i class="mdi mdi-calendar-clock font-20"></i>' +
                        ' </a><br>' +
                        '<span class="text-dark fw-bold font-14">Assigned to: ' + val.user_name + '</span>' +
                        '</td>' +
                        '</tr>' +
                        '</table>' +
                        '</div>';


                    /*tmpHtml += '<div class="inbox-item border rounded p-2 mb-2">' +
                        // ' <div class="inbox-item-img"><i class="mdi mdi-clock-alert widget-icon rounded-circle bg-secondary-lighten text-secondary"></i></div>'+
                        '<p class="inbox-item-author"><a href="javascript:void(0);"onclick="' + htmlAction + '" class="text-dark"><b>#' + val.estimate_no + ' (' + val.customer_name + ' - ' + val.mobile_no + ')</b></a></p>' +
                        '<p class="inbox-item-text">' + tmpNotes + '</p>' +
                        // '<p class="inbox-item-text">' +
                        '<ul class="inbox-item-text" style="padding-left:0px !important;">' +
                        '<li class="list-inline-item badge badge-secondary-lighten">' + moment(val.created_datetime, 'DD-MM-YYYY HH:mm:ss').format("DD MMMM, YYYY HH:mm A") + '</li>' +
                        '<li class="list-inline-item"><i class="mdi mdi-circle-small"></i></li>' +
                        '<li class="list-inline-item">' + tmpStr + '</li>' +
                        tmpStatus +
                        '<li class="list-inline-item"><i class="mdi mdi-circle-small"></i></li>' +
                        '<li class="list-inline-item badge badge-dark-lighten"><small>Created by - ' + val.user_name + '</small></li>' +
                        '</ul>' +
                        // '</p>' +
                        ' <p class="inbox-item-date">' +
                        ' <a href="javascript:void(0);" onclick="' + htmlAction + '" class="text-dark"> <i class="uil uil-stopwatch font-22"></i> </a>' +
                        '</p>' +
                        '</div>';*/
                });
            });
            $('.follow-up-list').html(tmpHtml);

        },
        error: function (xhr, status, error) {
            var status = JSON.parse(xhr.responseText);
            $(".follow-up-list").html('<div class="align-items-center text-center"><h4>' + status.success + '</h4></div>');
            $('.loader').show();
        },
        complete: function (data) {
            $('.loader').hide();
        }

    });
}

function follow_up_list(val, url, flg) {
    $.ajax({
        async: false,
        type: "GET",
        url: url,
        data: { val: val, flg: flg },
        dataType: "json",
        beforeSend: function () {
            $(".timeline-list").html('<div class="text-center">\n' +
                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>\n' +
                '</div>');
        },
        success: function (res) {
            var tmpHtml = '';
            $(".form-div").hide();
            if (jQuery.isEmptyObject(res.data)) {
                $(".form-div").show();
            } else {
                if (res.data[0].next_follow_up != 1) {
                    $(".form-div").show();
                }
            }
            $.each(res.data, function (key, val) {
                if (key == 0) {
                    $("#follow-up-modal #event_id").val(val.id);
                }
                var tmpStr = '';
                if (val.next_follow_up == 1)
                    tmpStr = '<li class="list-inline-item"><i class="mdi mdi-circle-small"></i></li> <span class="badge badge-outline-primary"><i class="mdi mdi-close-box"></i> ' + val.status + '</span>';
                tmpHtml += '<div class="border rounded p-2 mb-2">' +
                    '<p class="text-muted mb-0">' + val.notes + '</p>' +

                    '<ul class="inbox-item-text" style="padding-left:0px !important;margin-bottom:0px !important;">' +
                    '<li class="list-inline-item badge badge-secondary-lighten">' + val.created_at + '</li>';
                if (val.next_follow_up != 1) {
                    tmpHtml += '<li class="list-inline-item"><i class="mdi mdi-circle-small"></i></li>' +
                        '<li class="list-inline-item badge badge-secondary-lighten">Take Follow up on :' + val.start_date + '</li>';
                }
                tmpHtml += '<li class="list-inline-item">' + tmpStr + '</li>' +
                    '<li class="list-inline-item"><i class="mdi mdi-circle-small"></i></li>' +
                    '<li class="list-inline-item badge badge-dark-lighten"><small>Created by - ' + val.user_name + '</small></li>' +
                    '</ul>' +
                    // '<p class="text-muted mb-0"><span class="badge badge-secondary-lighten">' + val.start_date + '</span>' + tmpStr + ' <span class="text-muted">' + val.user_name + '</span></p>' +
                    '</div>';
            });
            $('.timeline-list').html(tmpHtml);
        },
        error: function (xhr, status, error) {
            var status = JSON.parse(xhr.responseText);
            $(".timeline-list").html('<div class="align-items-center text-center"><h4>' + status.success + '</h4></div>');
        },
        complete: function (data) {

        }
    });
}

function salesPerformanceChart(date_range,fil_user_id, url) {


    $.ajax({
        async: false,
        type: "GET",
        url: url,
        data: { date: date_range, fil_user_id:fil_user_id},
        dataType: "json",
        beforeSend: function () {
            $("#sales-performance-chart").html('<div class="text-center">' +
                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>' +
                '</div>');
        },
        success: function (res) {
            $("#total_task_span").html(res.total_task);
            $("#completed_task_span").html(res.completed_task);
            ApexCharts.exec('mychart', 'updateOptions', {

                labels: res.labels,
                series: res.series,
                plotOptions: {
                    radialBar: {
                        dataLabels: {
                            total: {
                                show: true,
                                label: res.labels[0],
                                formatter: function (w) {
                                    // By default this function returns the average of all series. The below is just an example to show the use of custom formatter function
                                    return res.series[0] + '%'
                                }
                            }
                        }
                    }
                }

            }, false, true);
            $('#preloader').hide();
        },
        error: function (xhr, status, error) {
            // var status = JSON.parse(xhr.responseText);
            // $(".timeline-list").html('<div class="align-items-center text-center"><h4>' + status.success + '</h4></div>');
        },
        complete: function (data) {
            $('#preloader').hide();
        }
    });
}

function barChart(date_range,fil_user_id) {
    $.ajax({
        type: "GET",
        // async: false,
        url: SITEURL + "/bar-chart",
        data: { date: date_range,fil_user_id:fil_user_id },
        dataType: "json",
        beforeSend: function () {
            $('#preloader').show();
            $('#status').show();
            $("#chart").html('<div class="text-center">' +
                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>' +
                '</div>');
        },
        success: function (res) {
            ApexCharts.exec('bar_chart', 'updateOptions', {
                series: [{
                    name: 'Total',
                    data: res.sent
                }, {
                    name: 'Accepted',
                    data: res.close
                }],
                xaxis: {
                    categories: res.labels,
                },
            }, false, true);
            item = res;
            $('#preloader').hide();
            $('#status').hide();
        }
    });
}

function estimate_duplicate(id) {
    $.ajax({
        type: "POST",
        async: false,
        url: SITEURL + "/estimate-duplicate",
        data: { id: id },
        dataType: "json",
        // beforeSend: function () {
        //     $("#chart").html('<div class="text-center">' +
        //         '<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>' +
        //         '</div>');
        // },
        success: function (res) {
            toastrSuccess('Estimate copied');
            $("#estimate-datatable").DataTable().ajax.reload();
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText
            switch (xhr.status) {
                case 401:
                    toastrError('Error in estimate coping...', 'Error');
                    break;
                case 422:
                    toastrInfo('Please contact developer.', 'Info');
                    break;
                case 409:
                    toastrInfo('Name already exist.', 'Warning');
                    break;
                default:
                    toastrError('Error - ' + errorMessage, 'Error');
            }
        },
    });
}

function unReadNotification() {
    $.ajax({
        // async: false,
        type: "GET",
        url: SITEURL + '/notification',
        dataType: "json",
        beforeSend: function () {
            /*$("#list-notification").html('<div class="text-center">' +
                '<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>' +
                '</div>');*/
            // $("#noti-alert").html('<i class="mdi mdi-dots-circle mdi-spin font-20 text-muted"></i>');
            $(".bell-notification").attr('disabled',true);
        },
        success: function (data, textStatus, jqXHR) {
            let htmlStr = '';
            let notArr = new Array();
            $.each(data.data, function (i, currProgram) {
                htmlStr += '<h5 class="text-muted font-13 fw-normal mt-2">' + i + '</h5>';
                $.each(currProgram, function (index, value) {
                    notArr.push(value.last_activity_id);
                    let nt = value.last_activity;
                    let fc = '<div class="notify-icon bg-primary rounded-circle fs-16"><i class="mdi mdi-calendar text-white"></i></div>';
                    let tmp_start_time = value.start_date;
                    if (value.timeline_activity_type == 8) {
                        tmp_start_time = value.display_timeline_updated_time_at
                        nt = value.last_internal_remarks;
                        fc = '<div class="notify-icon bg-secondary rounded-circle fs-16"><i class="mdi mdi-arrow-top-right text-white"></i></div>';
                    }

                    if (!nt) {
                        nt = '';
                    }

                    estimateNo = '';
                    if (value.estimate_no) {
                        estimateNo = value.estimate_no;
                    }

                    var action_url = SITEURL + '/lead/timeline/' + value.id;
                    htmlStr += '<a href="' + action_url + '" class="dropdown-item p-0 notify-item card read-noti shadow-none mb-2">\n' +
                        '<div class="card-body">\n' +
                        '<span class="float-end noti-close-btn text-muted">' +
                        '<div class="form-check notification-check">' +
                        '<input class="form-check-input border-muted read_notification_chk" type="checkbox" data-id="' + value.last_activity_id + '" value="" id="all-notification-check01">' +
                        '<label class="form-check-label" htmlFor="all-notification-check01"></label>' +
                        '</div>' +
                        '</span>' +
                        // '<span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>   \n' +
                        '<div class="d-flex align-items-center">\n' +
                        '<div class="flex-shrink-0">\n' +
                        fc +
                        /* '<div class="notify-icon bg-primary rounded-circle fs-16">\n' +
                         '<i class="mdi mdi-comment-account-outline"></i>\n' +
                         '</div>\n' +*/
                        '</div>\n' +
                        '<div class="flex-grow-1 text-truncate ms-2">\n' +
                        //timeline_activity_type
                        '<h5 class="noti-item-title fw-semibold font-14">' + value.name + '<br>' + estimateNo + '<small class="fw-normal text-muted ms-1">' + tmp_start_time + '</small></h5>\n' +
                        '<small class="noti-item-subtitle text-muted">' + nt + '</small>\n' +
                        '</div>\n' +
                        '</div>\n' +
                        '</div>\n' +
                        '</a>';
                    /*htmlStr += '<a href="javascript:void(0);" class="dropdown-item notify-item">\n' +
                        '<div class="notify-icon bg-primary">\n' +
                        '<i class="mdi mdi-comment-account-outline"></i>\n' +
                        '</div>\n' +
                        '<p class="notify-details">'+value.estimate_no+'\n' +
                        '<small class="text-muted">'+value.notes+'</small>\n' +
                        '</p>\n' +
                        '</a>';*/
                });
            });
            $('.tmp_arr').attr('data-id', notArr);
            $("#noti-alert").removeClass("noti-icon-badge");
            $("#noti-alert").empty();
            if (data.notification_count > 0) {
                $("#noti-alert").addClass("noti-icon-badge");
            }
            $("#list-notification").html(htmlStr);
            $(".bell-notification").attr('disabled',false);
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.status + ': ' + xhr.statusText
            switch (xhr.status) {
                case 401:
                    toastrError('Error in saving...', 'Error');
                    break;
                case 422:
                    // toastrInfo('Notification not found!', 'Info');
                    $("#list-notification").html('<div class="text-center">' +
                        'No data available in notification' +
                        '</div>');
                    break;
                case 409:
                    toastrInfo('Notification not found!', 'Warning');
                    break;
                default:
                    toastrError('Error - ' + errorMessage, 'Error');
            }
            // $("#noti-alert").empty();
            $(".bell-notification").attr('disabled',false);
        },
        complete: function (data) {
        }
    });

}

var firebaseConfig = {
    apiKey: "AIzaSyDdNbQcpa4T_ZSQdKh7Wy7xchw9B5L-fFk",
    authDomain: "quick-estimate-6badd.firebaseapp.com",
    projectId: "quick-estimate-6badd",
    storageBucket: "quick-estimate-6badd.appspot.com",
    messagingSenderId: "758224170660",
    appId: "1:758224170660:web:76663dedb0eb14c36cc031",
    measurementId: "G-PHKCK3MTW7"
};
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

function startFCM() {
    messaging
        .requestPermission()
        .then(function () {
            return messaging.getToken()
        })
        .then(function (response) {
            console.log("token : " + response);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: SITEURL + '/store-token',
                type: 'POST',
                data: {
                    token: response
                },
                dataType: 'JSON',
                success: function (response) {
                    // alert('Token stored.');
                },
                error: function (error) {
                    console.log(error);
                },
            });
        }).catch(function (error) {
            console.log(error);
        });
}

messaging.onMessage(function (payload) {
    const title = payload.notification.title;
    const options = {
        body: payload.notification.body,
        icon: payload.notification.icon,
        click_action: payload.notification.click_action
    };
    // new Notification(title, options);
    const notif = new Notification(title, options);

    /*notif.onclick = function(event) {
        window.location.href = payload.notification.click_action;
        // alert(payload.notification.click_action); // working FINE
    };*/
    /*
        // Notifcation click event
        self.addEventListener('notificationclick', function (event) {
            event.notification.close();
            clients.openWindow("https://youtu.be/PAvHeRGZ_lA"); //even simple static link not opening
        });
        return self.registration.showNotification(title,
            options);*/
});

/*self.addEventListener(
    "notificationclick",
    (event) => {
        event.notification.close();
        if (event.action === "archive") {
            // User selected the Archive action.
            archiveEmail();
        } else {
            // User selected (e.g., clicked in) the main body of notification.
            clients.openWindow("/abc.com");
        }
    },
    false
);*/
/*self.addEventListener('notificationclick',function(event){
    var action_click = event.notification.click_action;
    alert('fdfdf'+action_click);
    event.notification.close();

    event.waitUntil(
        clients.opens.window(action_click)
    );
})*/

// $(document).ready(function () {
//     setInterval(function () {
//         unReadNotification()
//     }, 5000);
// });
function funcStrLimit(str) {
    if (str != null && str.length > 20)
        str = '<span title="' + str + '">'+str.substring(0, 40) + "<span title='" + str + "'>...</span></span>";
    return str;
}

function funcStrLimits(str, str_len = 20, fd = 0) {
    if (str != null && str.length > str_len)
        if (fd == 1) {
            var c= str.replace(/(<([^>]+)>)/ig,"");
            var a = "title='"+c+"'";
            str = '<span '+a+'>'+str.substring(0, str_len) + '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" '+a+'><b> ...</b></a></span>';
        } else {
            str = '<span title="' + str + '">'+str.substring(0, str_len) + '<a href="#" data-bs-toggle="tooltip" data-bs-html="true" title="'+str+'"><b> ...</b></a></span>';
        }
    return str;
}

function image_remove(id, url, image_path) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: !0,
        confirmButtonColor: "#3085D6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        confirmButtonClass: "btn btn-primary",
        cancelButtonClass: "btn btn-danger ml-1",
        buttonsStyling: !1,
        preConfirm: function () {
            $.ajax({
                type: "POST",
                url: url,
                data: { id: id, image_path: image_path },
                dataType: "json",
                success: function (data, textStatus, jqXHR) {
                    $("#image_preview_div").hide('slow');
                    // toastrSuccess('Successfully removed');
                },
                error: function (xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText
                    switch (xhr.status) {
                        case 401:
                            toastrError('Error in saving...', 'Error');
                            break;
                        case 422:
                            toastrInfo('Please contact developer.', 'Info');
                            break;
                        case 409:
                            toastrInfo('Name already exist.', 'Warning');
                            break;
                        default:
                            toastrError('Error - ' + errorMessage, 'Error');
                    }
                },
                complete: function (data) {
                }
            });
        }
    }).then(function (t) {
        t.value && Swal.fire({
            title: "Success",
            text: "Your image has been deleted.",
            type: "success", showConfirmButton: !1,
            timer: 1500,
            confirmButtonClass: "btn btn-success",
            showConfirmButton: false
        })
    });

}

function image_aboutus_remove(id, url, image_path) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: !0,
        confirmButtonColor: "#3085D6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        confirmButtonClass: "btn btn-primary",
        cancelButtonClass: "btn btn-danger ml-1",
        buttonsStyling: !1,
        preConfirm: function () {
            $.ajax({
                type: "POST",
                url: url,
                data: { id: id, image_path: image_path },
                dataType: "json",
                success: function (data, textStatus, jqXHR) {
                    $("#tmp_img_aboutus_" + id).hide('slow');
                    // toastrSuccess('Successfully removed');
                },
                error: function (xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText
                    switch (xhr.status) {
                        case 401:
                            toastrError('Error in saving...', 'Error');
                            break;
                        case 422:
                            toastrInfo('Please contact developer.', 'Info');
                            break;
                        case 409:
                            toastrInfo('Name already exist.', 'Warning');
                            break;
                        default:
                            toastrError('Error - ' + errorMessage, 'Error');
                    }
                },
                complete: function (data) {
                }
            });
        }
    }).then(function (t) {
        t.value && Swal.fire({
            title: "Success",
            text: "Your image has been deleted.",
            type: "success", showConfirmButton: !1,
            timer: 1500,
            confirmButtonClass: "btn btn-success",
            showConfirmButton: false
        })
    });

}

function image_cover_remove(id, url, image_path) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: !0,
        confirmButtonColor: "#3085D6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        confirmButtonClass: "btn btn-primary",
        cancelButtonClass: "btn btn-danger ml-1",
        buttonsStyling: !1,
        preConfirm: function () {
            $.ajax({
                type: "POST",
                url: url,
                data: { id: id, image_path: image_path },
                dataType: "json",
                success: function (data, textStatus, jqXHR) {
                    $("#tmp_img_cover_" + id).hide('slow');
                    // toastrSuccess('Successfully removed');
                },
                error: function (xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText
                    switch (xhr.status) {
                        case 401:
                            toastrError('Error in saving...', 'Error');
                            break;
                        case 422:
                            toastrInfo('Please contact developer.', 'Info');
                            break;
                        case 409:
                            toastrInfo('Name already exist.', 'Warning');
                            break;
                        default:
                            toastrError('Error - ' + errorMessage, 'Error');
                    }
                },
                complete: function (data) {
                }
            });
        }
    }).then(function (t) {
        t.value && Swal.fire({
            title: "Success",
            text: "Your image has been deleted.",
            type: "success", showConfirmButton: !1,
            timer: 1500,
            confirmButtonClass: "btn btn-success",
            showConfirmButton: false
        })
    });

}

$(document).on('click', '#select_all_today', function () {
    var isChecked = $(this).prop('checked');

    $(".single_checkbox_today").each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop("checked", isChecked);
        }
    });
    $("#select_count").hide();
    if($("input.single_checkbox_today:checked").length > 0)
        $("#select_count").show();

    // $("#select_count").html($("input.single_checkbox_today:checked").length);
});

$(document).on('click', '.single_checkbox_today', function () {
    if ($('.single_checkbox_today:checked').length == $('.single_checkbox_today').length) {
        $('#select_all_today').prop('checked', true);
    } else {
        $('#select_all_today').prop('checked', false);
    }
    $("#select_count").hide();
    if($("input.single_checkbox_today:checked").length > 0)
        $("#select_count").show();
    // $("#select_count").html($("input.single_checkbox_today:checked").length);
});

$(document).on('click', '#select_all_upcoming', function () {
    var isChecked = $(this).prop('checked');

    $(".single_checkbox_upcoming").each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop("checked", isChecked);
        }
    });
    $("#select_count").hide();
    if($("input.single_checkbox_upcoming:checked").length > 0)
        $("#select_count").show();

    // $("#select_count").html($("input.single_checkbox_upcoming:checked").length);
});

$(document).on('click', '.single_checkbox_upcoming', function () {
    if ($('.single_checkbox_upcoming:checked').length == $('.single_checkbox_upcoming').length) {
        $('#select_all_upcoming').prop('checked', true);
    } else {
        $('#select_all_upcoming').prop('checked', false);
    }
    $("#select_count").hide();
    if($("input.single_checkbox_upcoming:checked").length > 0)
        $("#select_count").show();
    // $("#select_count").html($("input.single_checkbox_upcoming:checked").length);
});

$(document).on('click', '#select_all_overdue', function () {
    var isChecked = $(this).prop('checked');

    $(".single_checkbox_overdue").each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop("checked", isChecked);
        }
    });
    $("#select_count").hide();
    if($("input.single_checkbox_overdue:checked").length > 0)
        $("#select_count").show();

    // $("#select_count").html($("input.single_checkbox_overdue:checked").length);
});

$(document).on('click', '.single_checkbox_overdue', function () {
    if ($('.single_checkbox_overdue:checked').length == $('.single_checkbox_overdue').length) {
        $('#select_all_overdue').prop('checked', true);
    } else {
        $('#select_all_overdue').prop('checked', false);
    }
    $("#select_count").hide();
    if($("input.single_checkbox_overdue:checked").length > 0)
        $("#select_count").show();
    // $("#select_count").html($("input.single_checkbox_overdue:checked").length);
});

$(document).on('click', '#select_all_someday', function () {
    var isChecked = $(this).prop('checked');

    $(".single_checkbox_someday").each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop("checked", isChecked);
        }
    });
    $("#select_count").hide();
    if($("input.single_checkbox_someday:checked").length > 0)
        $("#select_count").show();

    // $("#select_count").html($("input.single_checkbox_someday:checked").length);
});

$(document).on('click', '.single_checkbox_someday', function () {
    if ($('.single_checkbox_someday:checked').length == $('.single_checkbox_someday').length) {
        $('#select_all_someday').prop('checked', true);
    } else {
        $('#select_all_someday').prop('checked', false);
    }
    $("#select_count").hide();
    if($("input.single_checkbox_someday:checked").length > 0)
        $("#select_count").show();
    // $("#select_count").html($("input.single_checkbox_someday:checked").length);
});

$(document).on('click', '#select_all_never', function () {
    var isChecked = $(this).prop('checked');

    $(".single_checkbox_never").each(function () {
        if (!$(this).prop('disabled')) {
            $(this).prop("checked", isChecked);
        }
    });
    $("#select_count").hide();
    if($("input.single_checkbox_never:checked").length > 0)
        $("#select_count").show();

    // $("#select_count").html($("input.single_checkbox_never:checked").length);
});

$(document).on('click', '.single_checkbox_never', function () {
    if ($('.single_checkbox_never:checked').length == $('.single_checkbox_never').length) {
        $('#select_all_never').prop('checked', true);
    } else {
        $('#select_all_never').prop('checked', false);
    }
    $("#select_count").hide();
    if($("input.single_checkbox_never:checked").length > 0)
        $("#select_count").show();
    // $("#select_count").html($("input.single_checkbox_never:checked").length);
});


//Open Modal
function openFilterModal(modalName) {
    // $(modalTitleName).text(modalTitle);
    $(modalName).modal('show');

}
