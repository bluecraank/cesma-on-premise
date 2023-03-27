// SSH Command fetch
$(document).ready(function () {
    $('select[name="execute-specify-switch"]').on('change', function () {
        if ($(this).val() == "specific-switch") {
            $("input[name='which_switch']").val("specific-switch");
            $(".execute-switch-select").removeClass('is-hidden')
            $(".location-select").addClass('is-hidden')
        } else if ($(this).val() == "every-switch") {
            $("input[name='which_switch']").val("every-switch");
            $(".execute-switch-select").addClass('is-hidden')
            $(".location-select").addClass('is-hidden')
        } else {
            $("input[name='which_switch']").val("specific-location");
            $(".location-select").removeClass('is-hidden')
            $(".execute-switch-select").addClass('is-hidden')
        }
    });

    $('select[name="fast-command"]').on('change', function () {
        if ($(this).val() != "Schnellaktion") { $("textarea[name='execute-command']").val($(this).val()); }
    });

    $("button[name='executeSwitchCommand'").click(async function () {
        if ($("select[name='execute-switch-select']").val() && $("textarea[name='execute-command']").val()) {
    
            let command = $("textarea[name='execute-command']").val();
            let which_switch = $("input[name='which_switch']").val();
            let api_token = $("input[name='_token']").val();
    
            if (which_switch == "every-switch") {
                switches = $("select[name='execute-switch-select']").children().map(function () { return { 'id': $(this).val(), 'name': $(this).text() } }).get();
            } else if (which_switch == "specific-switch") {
                switches = $("select[name='execute-switch-select']").val();
                $.each(switches, function (index, value) {
                    switches[index] = { id: value, name: $("select[name='execute-switch-select'] option[value='" + value + "']").text() };
                });
            } else {
                let location = $("select[name='execute-switch-select-loc']").val();
                switches = $("select[name='execute-switch-select']").children().map(function () { if ($(this).attr('data-location') == location) { return { 'id': $(this).val(), 'name': $(this).text() } } }).get();
            }
    
            $(".output-buttons").children().remove();
            $(".outputs").children().remove();
    
            execute(switches, command, which_switch, api_token);
        }
    });

    $('.output-buttons').on("click", "button", function () {
        let id = $(this).attr('data-id');
        $(this).siblings().removeClass("is-primary");
        $(this).addClass("is-primary");
        $(".outputs div").addClass("is-hidden");
        $(`.outputs div[data-id='${id}']`).removeClass("is-hidden");
    })
    
});


async function execute(switches, command) {
    $.each(switches, async function (index, value) {
        $(".output-buttons").append(`<button class='is-loading button' data-id='${value.id}'>${value.name}</button>`);

        let formData = new FormData();
        let token = $('meta[name="csrf-token"]').attr('content');
        formData.append('_token', token);
        formData.append("command", command);
        formData.append("id", value.id);

        fetch(
            '/switch/' + value.id + '/ssh/execute',
            {
                method: 'POST',
                body: formData
            }
        ).then((resp) => resp.json()).then(response => {
            $(".outputs").append(`<div class='is-hidden' data-id='${response.id}'><pre>${response.output}</pre></div>`)
            $(`.output-buttons button[data-id='${response.id}']`).removeClass("is-loading");
            $(`.output-buttons button[data-id='${response.id}']`).html(`${value.name} &nbsp; <i class="fa fa-${response.status}"></i>`)
        });
    });
}
// Functions
function editSwitchModal(id, name, hostname, location, building, room, number) {

    let modal = $('.modal-edit-switch');
    modal.find('.switch-id').val(id);
    modal.find('.switch-name').val(name);
    modal.find('.switch-numbering').val(number);
    modal.find('.switch-fqdn').val(hostname);
    modal.find('.switch-location').val(location);
    modal.find('.switch-building').val(building);
    modal.find('.switch-room').val(room);
    modal.show()
}

function deleteSwitchModal(id, name) {
    let modal = $('.modal-delete-switch');
    modal.find('.switch-id').val(id);
    modal.find('.switch-name').val(name);
    modal.show()
}

function editLocationModal(id, name) {
    let modal = $('.modal-edit-location');
    modal.find('.location-id').val(id);
    modal.find('.location-name').val(name);
    modal.show()
}

function deleteLocationModal(id, name) {
    let modal = $('.modal-delete-location');
    modal.find('.location-id').val(id);
    modal.find('.location-name').val(name);
    modal.show()
}

function editBuildingModal(id, name, location_id) {
    let modal = $('.modal-edit-building');
    modal.find('.building-id').val(id);
    modal.find('.building-name').val(name);
    // Select option if building id matches
    modal.find('.locations').val(location_id);
    modal.show()
}

function deleteBuildingModal(id, name) {
    let modal = $('.modal-delete-building');
    modal.find('.building-id').val(id);
    modal.find('.building-name').val(name);
    modal.show()
}

function editRoomModal(id, name, building_id) {
    let modal = $('.modal-edit-room');
    modal.find('.room-id').val(id);
    modal.find('.room-name').val(name);

    // Select option if building id matches
    modal.find('.buildings').val(building_id);
    modal.show()
}

function deleteRoomModal(id, name) {
    let modal = $('.modal-delete-room');
    modal.find('.room-id').val(id);
    modal.find('.room-name').val(name);
    modal.show()
}

function editVlanModal(id, name, description, ip, scan, sync, is_client_vlan) {
    let modal = $('.modal-edit-vlan');
    modal.find('.vlan-id').val(id);
    modal.find('.vlan-name').val(name);
    modal.find('.vlan-desc').val(description);
    modal.find('.vlan-ip_range').val(ip);
    if (scan == 1) {
        modal.find('.vlan-scan').prop('checked', true);
    } else {
        modal.find('.vlan-scan').prop('checked', false);
    }
    if (sync == 1) {
        modal.find('.vlan-sync').prop('checked', true);
    } else {
        modal.find('.vlan-sync').prop('checked', false);
    }
    if (is_client_vlan == 1) {
        modal.find('.vlan-is_client_vlan').prop('checked', false);
    } else {
        modal.find('.vlan-is_client_vlan').prop('checked', true);
    }
    modal.show()
}

function deleteVlanModal(id, name) {
    let modal = $('.modal-delete-vlan');
    modal.find('.vlan-id').val(id);
    modal.find('.vlan-name').val(name);
    modal.show()
}

function deleteUserModal(id, name) {
    let modal = $('.modal-delete-user');
    modal.find('.user-id').val(id);
    modal.find('.user-name').val(name);
    modal.show()
}

function deleteBackupModal(id, date) {
    let modal = $('.modal-delete-backup');
    modal.find('.backup-id').val(id);
    modal.find('.backup-date').val(date);
    modal.show()
}

function editUplinkModal(id, name, uplinks) {
    let modal = $('.modal-edit-uplinks');
    modal.find('.device-id').val(id);
    modal.find('.device-name').val(name);
    modal.find('.device-uplinks').val(uplinks);
    modal.show();
}

function restoreBackup(id, created_at, device, name) {
    let modal = $('.modal-upload-backup');
    modal.find('.id').val(id);
    modal.find('.name').val(name);
    modal.find('.device-id').val(device);
    modal.find('.created').val(created_at);
    modal.show()
}

function sw_actions(ele, type, id) {
    let uri = '/switch/' + id + '/action/create-backup';
    let cssclass = "fa-hdd";
    let reload = false;

    if (type == "refresh") {
        uri = '/switch/' + id + '/action/refresh';
        cssclass = "fa-sync";
        reload = true
    } else if (type == "pubkeys") {
        uri = '/switch/' + id + '/action/sync-pubkeys';
        cssclass = "fa-key";
    } else if (type == "vlans") {
        uri = '/switch/' + id + '/action/sync-vlans';
        cssclass = "fa-ethernet";
    }

    let formData = new FormData();
    formData.append('device_id', id);

    fetcher(uri, formData, ele, cssclass, reload);
}

function switchCreateBackup(ele) {
    uri = '/switch/action/create-backup';
    cssclass = "fa-hdd";
    let form = new FormData();

    fetcher(uri, form, ele, cssclass);
}

function switchSyncPubkeys() {
    let form = new FormData();
    let uri = '/switch/action/sync-pubkeys';
    let cssclass = 'fa-key';
    let ele = $(".syncPubButton");
    fetcher(uri, form, ele, cssclass);
    $(".modal-sync-pubkeys").hide();
}

// Fetcher function
async function fetcher(uri, form, ele, cssclass, timeout = false, callback = false) {

    let token = $('meta[name="csrf-token"]').attr('content');
    form.append('_token', token);

    $(ele).addClass('is-loading');
    await fetch(uri, {
        method: 'POST',
        body: form
    }).then(response => response.json())
        .then(data => {
            if (data.success == "true") {
                $(ele).addClass('is-success');
                $(ele).find('i').addClass('fa-check');
                $(ele).removeClass('is-loading');
                $(ele).find('i').removeClass(cssclass);
                $(ele).find('i').removeClass('fa-exclamation-triangle');
                $(ele).removeClass('is-danger');
                $.notify(data.message, {
                    style: 'bulma-success',
                    autoHideDelay: 8000
                });

                if (timeout) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1100)
                }

                if(callback) {
                    callback(true);
                }

            } else {
                $(ele).addClass('is-danger');
                $(ele).find('i').addClass('fa-exclamation-triangle');
                $(ele).find('i').removeClass(cssclass);
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');
                $.notify(data.message, {
                    style: 'bulma-error',
                    autoHideDelay: 8000
                });

                
                if(callback) {
                    callback(false);
                }
            }
        }
        );
}

function RouterModal(id, ip, desc, modal_id) {
    let modal = $('.' + modal_id);
    modal.find('.id').val(id);
    modal.find('.ip').val(ip);
    modal.find('.desc').val(desc);
    modal.show();
}

function VlanTemplateModal(id, name, vlans, modal_id) {
    let modal = $('.' + modal_id);
    modal.find('.id').val(id);
    modal.find('.name').val(name);
    // modal.find('.type').val(type);
    $.each(JSON.parse(vlans), function (key, value) {
        $('#vlan-select-ms').multiSelect('select', ""+value+"");
        $('#vlan-select-ms-2').multiSelect('select', ""+value+"");
    });

    modal.show();
}

window.addEventListener("scroll", () => {
    if (window.pageYOffset > 100) {
        $(".scroll-to-top").removeClass('is-hidden');
    } else {
        $(".scroll-to-top").addClass('is-hidden');
    }
});