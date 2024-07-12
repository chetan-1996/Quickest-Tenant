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

$(document).on('click', '#select_all', function () {
    $(".single_checkbox").prop("checked", this.checked);
    $("#select_count").html($("input.single_checkbox:checked").length);
});

$(document).on('click', '.single_checkbox', function () {
    if ($('.single_checkbox:checked').length == $('.single_checkbox').length) {
        $('#select_all').prop('checked', true);
    } else {
        $('#select_all').prop('checked', false);
    }
    $("#select_count").html($("input.single_checkbox:checked").length);
});

