// SSH Command fetch
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

async function execute(switches, command, type, api_token) {
    $.each(switches, async function (index, value) {
        $(".output-buttons").append(`<button class='is-loading button' data-id='${value.id}'>${value.name}</button>`);

        let formData = new FormData();
        let token = $('meta[name="csrf-token"]').attr('content');
        formData.append('_token', token);
        formData.append("command", command);
        formData.append("id", value.id);

        fetch(
            '/switch/'+value.id+'/ssh/execute',
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

$('.output-buttons').on("click", "button", function () {
    let id = $(this).attr('data-id');
    $(this).siblings().removeClass("is-primary");
    $(this).addClass("is-primary");
    $(".outputs div").addClass("is-hidden");
    $(`.outputs div[data-id='${id}']`).removeClass("is-hidden");
})

// Functions
function editSwitchModal(id, name, hostname, location, building, details, number) {

    let modal = $('.modal-edit-switch');
    modal.find('.switch-id').val(id);
    modal.find('.switch-name').val(name);
    modal.find('.switch-numbering').val(number);
    modal.find('.switch-fqdn').val(hostname);
    modal.find('.switch-location').val(location);
    modal.find('.switch-building').val(building);
    modal.find('.switch-details').val(details);
    modal.show()
}

function deleteSwitchModal(id, name) {
    let modal = $('.modal-delete-switch');
    modal.find('.switch-id').val(id);
    modal.find('.switch-name').val(name);
    modal.show()
}

function editBuildingModal(id, name) {
    let modal = $('.modal-edit-building');
    modal.find('.building-id').val(id);
    modal.find('.building-name').val(name);
    modal.show()
}

function deleteBuildingModal(id, name) {
    let modal = $('.modal-delete-building');
    modal.find('.building-id').val(id);
    modal.find('.building-name').val(name);
    modal.show()
}

function editVlanModal(id, name, description, ip, scan, sync, is_client_vlan) {
    let modal = $('.modal-edit-vlan');
    modal.find('.vlan-id').val(id);
    modal.find('.vlan-name').val(name);
    modal.find('.vlan-desc').val(description);
    modal.find('.vlan-ip_range').val(ip);
    if(scan == 1) {
        modal.find('.vlan-scan').prop('checked', true);
    } else {
        modal.find('.vlan-scan').prop('checked', false);
    }
    if(sync == 1) {
        modal.find('.vlan-sync').prop('checked', true);
    } else {
        modal.find('.vlan-sync').prop('checked', false);
    }
    if(is_client_vlan == 1) {
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

function updateUntaggedPorts(id) {
    let ports = [];
    let vlans = [];
    let device = id;

    let i = 0;
    $(".port-vlan-select").each(function() {
        if($(this).attr('data-current-vlan') != $(this).val()) {
            device = $(this).attr('data-id');

            let port = $(this).attr('data-port');
            ports[i] = port;
            vlans[i] = ($(this).val());

            i++;
        }
    });

    let token = $('meta[name="csrf-token"]').attr('content');

    let formData = new FormData();
    formData.append('ports', JSON.stringify(ports));
    formData.append('vlans', JSON.stringify(vlans));
    formData.append('device', device);
    formData.append('_token', token);

    let uri = '/switch/'+device+'/port-vlans/untagged';

    $(".live-body").css('opacity', '0.5');
    fetch(uri, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success == "true") {
            $(".live-body").css('opacity', '1');
            $(".save-vlans").addClass('is-hidden');
            $(".edit-vlans").removeClass('is-hidden');
            $(".response-update-vlan").removeClass('is-hidden');
            $(".response-update-vlan").addClass('is-success');
            $(".response-update-vlan").removeClass('is-danger');
            $(".response-update-vlan-text").html("<b>Success:</b> " + data.message);
        } else {
            $(".live-body").css('opacity', '1');
            $(".save-vlans").addClass('is-hidden');
            $(".edit-vlans").removeClass('is-hidden');
            $(".response-update-vlan").removeClass('is-hidden');
            $(".response-update-vlan").addClass('is-danger');
            $(".response-update-vlan").removeClass('is-success');
            $(".response-update-vlan-text").html("<b>Error:</b> " + data.message);
        }
    });

    $(".port-vlan-select").each(function() {
        $(this).prop('disabled', true);
    });
}

function updateTaggedModal(vlans, port, id) {
    let vlansSplitted = vlans.split(',');

    let modal = $('.modal-vlan-tagging');
    modal.find('.port_id').val(port);
    modal.find('.device_id').val(id);

    modal.find('.modal-card-body span.tag').removeClass('is-primary');

    vlansSplitted.forEach(function(vlan) {
        modal.find('.modal-card-body span.tag[data-id="'+vlan+'"]').addClass('is-primary');
    });

    modal.show();
}

$(".modal-vlan-tagging .modal-card-body span.tag").click(function () {
    $(this).toggleClass('is-primary');
});

function updateTaggedVlans() {
    let modal = $('.modal-vlan-tagging');
    let port = modal.find('.port_id').val();
    let device = modal.find('.device_id').val();
    let token = $('meta[name="csrf-token"]').attr('content');

    let vlans = [];

    let i = 0;
    modal.find('.modal-card-body span.tag.is-primary').each(function() {
        let vid = $(this).attr('data-id');
        vlans[i] = vid;
        i++;
    });

    let formData = new FormData();
    formData.append('port', port);
    formData.append('vlans', JSON.stringify(vlans));
    formData.append('device', device);
    formData.append('_token', token);

    let uri = '/switch/'+device+'/port-vlans/tagged';

    $(".modal-vlan-tagging .is-cancel").addClass('is-hidden');
    $(".modal-vlan-tagging .is-info").removeClass('is-hidden');
    $(".modal-vlan-tagging .is-submit").addClass('is-loading');
    fetch(uri, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        $(".response-update-vlan").removeClass('is-success');
        $(".response-update-vlan").removeClass('is-danger');
        if(data.success == "true") {
            $(".response-update-vlan").addClass('is-success');
            $(".response-update-vlan-text").html("<b>Success:</b> " + data.message);
        } else {
            $(".response-update-vlan").addClass('is-danger');
            $(".response-update-vlan-text").html("<b>Error:</b> " + data.message);
        }
        modal.hide();
        modal.find('.is-cancel').removeClass('is-hidden');
        modal.find('.is-info').addClass('is-hidden');
        modal.find('.is-submit').removeClass('is-loading');
        $(".save-vlans").addClass('is-hidden');
        $(".edit-vlans").removeClass('is-hidden');
        $(".response-update-vlan").removeClass('is-hidden');
    });

}

function restoreBackup(id, created_at, device,  name) {
    let modal = $('.modal-upload-backup');
    modal.find('.id').val(id);
    modal.find('.name').val(name);
    modal.find('.device-id').val(device);
    modal.find('.created').val(created_at);
    modal.show()
}

function sw_actions(ele, type, id) {
    let uri = '/switch/'+id+'/backup/create';
    let cssclass = "fa-hdd";
    let reload = false;

    if(type == "refresh") {
        uri = '/switch/'+id+'/refresh';
        cssclass = "fa-sync";
        reload = true
    } else if(type == "pubkeys") {
        uri = '/switch/'+id+'/ssh/pubkeys';
        cssclass = "fa-key";
    }

    let formData = new FormData();
    formData.append('device_id', id);

    fetcher(uri, formData, ele, cssclass, reload);    
}

function device_overview_actions(type, ele) {

    let uri = '/switch/every/backup/create';
    let cssclass = 'fa-hdd';

    if (type == "clients") {
        uri = '/switch/every/clients';
        cssclass = 'fa-computer';
    } else if (type == "pubkeys") {
        $(".modal-sync-pubkeys").show();
        return false;
    }

    let form = new FormData();

    fetcher(uri, form, ele, cssclass);
}


function syncPubkeys() {
    let form = new FormData();
    let uri = '/switch/every/pubkeys';
    let cssclass = 'fa-key';
    let ele = $(".syncPubButton");
    fetcher(uri, form, ele, cssclass);
    $(".modal-sync-pubkeys").hide();
}

// Fetcher function
function fetcher(uri, form, ele, cssclass, timeout = false) {

    let token = $('meta[name="csrf-token"]').attr('content');
    form.append('_token', token);

    $(ele).addClass('is-loading');
    fetch(uri, {
        method: 'POST',
        body: form
    }).then(response => response.json())
        .then(data => {
            if(data.success == "true") {
                $(ele).addClass('is-success');
                $(ele).children('i').addClass('fa-check'); 
                $(ele).removeClass('is-loading');
                $(ele).children('i').removeClass(cssclass);
                $(ele).children('i').removeClass('fa-exclamation-triangle');
                $(ele).removeClass('is-danger');

                if(timeout) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1100)
                }
            } else {
                $(ele).addClass('is-danger');
                $(ele).children('i').addClass('fa-exclamation-triangle');
                $(ele).children('i').removeClass(cssclass);
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');  
                // $(".notification.status ul li").text(data.message);
                // $(".notification.status").slideDown(500);
            }
        }
    );
}