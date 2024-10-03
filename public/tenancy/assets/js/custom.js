function showToast(type, message, title = null) {
    toastr[type](message, title, {
        closeButton: true,          // Adds a close button to the notification
        debug: false,               // Shows debug information in the console
        newestOnTop: true,          // Newest notifications appear on top
        progressBar: true,          // Adds a progress bar to the notification
        positionClass: 'toast-bottom-right', // Position of the notification
        preventDuplicates: true,    // Prevent duplicate notifications
        onclick: null,              // A function to be called when the notification is clicked
        showDuration: 300,          // Duration to show the notification (in milliseconds)
        hideDuration: 1000,         // Duration to hide the notification (in milliseconds)
        timeOut: 5000,              // Duration to keep the notification visible (in milliseconds)
        extendedTimeOut: 1000,      // Extended time out when the user hovers over the notification
        showEasing: 'swing',        // Easing function to use when showing the notification
        hideEasing: 'linear',       // Easing function to use when hiding the notification
        showMethod: 'fadeIn',       // Animation method to use when showing the notification
        hideMethod: 'fadeOut'       // Animation method to use when hiding the notification
    });
}

function toastrSuccess(message, title = null) {
    showToast('success', message, title);
}

function toastrError(message, title = null) {
    showToast('error', message, title);
}

function toastrInfo(message, title = null) {
    showToast('info', message, title);
}

function toastrWarning(message, title = null) {
    showToast('warning', message, title);
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

    if(flag==2){
        $('#image_one, #image_two, #image_three').prop('required', true);
    }

    if(flag==3){
        $('#city_id').html('<option value="">Choose</option>');
        $('#state_id').html('<option value="">Choose</option>');
    }

    if(flag==4){
        $("#sale_price").prop('readonly', true);
        $("#cost_price").prop('readonly', true);
        $(".tax-div").hide();
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

function openFollowUpModal(modalName, modalTitle, modalForm, modalTitleName, estimate_id = 0, id = 0, followup_date = null, notes = null) {
    $(modalTitleName).text(modalTitle);
    $(modalForm).parsley().reset();
    $(modalName).modal('show');
    resetForm(modalForm)
    $('#id').val(id);
    $('#estimate_id').val(estimate_id);
    if (notes)
        $('#notes').val(notes);

    if (followup_date) {

        var follow_up_date = moment(moment(followup_date, 'DD-MM-YYYY')).format('DD/MM/YYYY');

        var follow_up_time = moment(moment(followup_date, 'DD-MM-YYYY HH:mm')).format('HH:mm');

        $('#followup_date').val(follow_up_date);
        $('#followup_time').val(follow_up_time);
    }

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


function remove_id(id, url, tableName) {
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
                    async: false,
                    type: "POST",
                    url: url,
                    data: {id: id},
                    dataType: "json",
                    success: function (data, textStatus, jqXHR) {

                        $(tableName).DataTable().ajax.reload();
                        $("#remove_" + id).closest("tr").hide('slow');
                        $('#select_all').prop('checked', false);
                        $("#select_count").html(0);
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
                text: "Your record has been updated.",
                type: "success", showConfirmButton: !1,
                timer: 1500,
                confirmButtonClass: "btn btn-success",
                showConfirmButton: false
            })
        });
    }
}

function change_status(id, status, url, tableName, flag = 0,old_value=null) {
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
                    async: false,
                    type: "POST",
                    url: url,
                    data: {id: id, status: status},
                    dataType: "json",
                    success: function (data, textStatus, jqXHR) {
                        toastrSuccess('Successfully updated');
                        $(tableName).DataTable().ajax.reload();
                        $('#select_all').prop('checked', false);
                        $("#select_count").html(0);
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
                $('#example-select').val(this.defaultValue);
        });
    }
}

function accessDeniedOpenModal(modalName, contentText) {
    $('#content-p').text(contentText);
    $(modalName).modal('show');
}

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

//Open Modal
function openFilterModal(modalName) {
    // $(modalTitleName).text(modalTitle);
    $(modalName).modal('show');

}

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

