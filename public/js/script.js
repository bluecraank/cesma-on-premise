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
    if ($("select[name='execute-switch-select']").val() && $("input[name='execute-passphrase']").val() && $("textarea[name='execute-command']").val()) {

        let command = $("textarea[name='execute-command']").val();
        let passphrase = $("input[name='execute-passphrase']").val();
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

        execute(switches, command, passphrase, which_switch, api_token);
    }
});

async function execute(switches, command, passphrase, type, api_token) {
    $.each(switches, async function (index, value) {
        $(".output-buttons").append(`<button class='is-loading button' data-id='${value.id}'>${value.name}</button>`);

        let formData = new FormData();
        formData.append("command", command);
        formData.append("passphrase", passphrase);
        formData.append("id", value.id);
        formData.append("_token", api_token);
        formData.append("api_token", $('#executeForm').find('input[name="api_token"]').val())

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
function editSwitchModal(id, name, hostname, location, building, details, number, uplinks) {

    let modal = $('.modal-edit-switch');
    modal.find('.switch-id').val(id);
    modal.find('.switch-name').val(name);
    modal.find('.switch-numbering').val(number);
    modal.find('.switch-fqdn').val(hostname);
    modal.find('.switch-location').val(location);
    modal.find('.switch-building').val(building);
    modal.find('.switch-details').val(details);
    modal.find('.switch-uplinks').val(uplinks);
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

function editVlanModal(id, name, description, ip, scan) {
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

function updateUntaggedPorts() {
    let ports = [];
    let vlans = [];
    let device = 0;

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

    let uri = '/switch/'+device+'/ports/update';

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
            $(".response-update-vlan-text").html("<b>Success:</b> " + data.error);
        } else {
            $(".live-body").css('opacity', '1');
            $(".save-vlans").addClass('is-hidden');
            $(".edit-vlans").removeClass('is-hidden');
            $(".response-update-vlan").removeClass('is-hidden');
            $(".response-update-vlan").addClass('is-danger');
            $(".response-update-vlan").removeClass('is-success');
            $(".response-update-vlan-text").html("<b>Error:</b> " + data.error);
        }
    });

    $(".port-vlan-select").each(function() {
        $(this).prop('disabled', true);
    });
}

function refreshSwitch(ele) {
    $(ele).addClass('is-loading');
    let form = $("#refresh-form").serialize();
    let id = $("#refresh-form .device_id").val();

    let uri = '/switch/'+id+'/refresh';
    let cssclass = 'fa-rotate';

    fetcher(uri, form, ele, cssclass, true);
}

function restoreBackup(id, created_at, device,  name) {
    let modal = $('.modal-upload-backup');
    modal.find('.id').val(id);
    modal.find('.name').val(name);
    modal.find('.device-id').val(device);
    modal.find('.created').val(created_at);
    modal.show()
}

function device_live_actions(ele, type) {
    let form = $("#actions-form").serialize();
    let id = $("#actions-form .device_id").val();

    let uri = '/switch/'+id+'/backup/create';
    let cssclass = "fa-hdd";

    if(type == "clients") {
        uri = '/switch/'+id+'/clients';
        cssclass = "fa-computer";
    } else if(type == "pubkeys") {
        uri = '/switch/'+id+'/ssh/pubkeys';
        cssclass = "fa-sync";
    }

    fetcher(uri, form, ele, cssclass);    
}

function device_overview_actions(type, ele) {
    let form = $("#form-all-devices").serialize();
    let uri = '/switch/every/backup/create';
    let cssclass = 'fa-hdd';
    if (type == "clients") {
        uri = '/switch/every/clients';
        cssclass = 'fa-computer';
    } else if (type == "pubkeys") {
        $(".modal-sync-pubkeys").show();
        return false;
    }
    fetcher(uri, form, ele, cssclass);
}

function syncPubkeys() {
    let form = $("#form-sync-pubkeys").serialize();
    let uri = '/switch/every/pubkeys';
    let cssclass = 'fa-sync';
    let ele = $(".sync-pubkeys-button");
    fetcher(uri, form, ele, cssclass);
    $(".modal-sync-pubkeys").hide();
}

// Fetcher function
function fetcher(uri, form, ele, cssclass, timeout = false) {
    $(ele).addClass('is-loading');
    fetch(uri, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: form
    }).then(response => response.json())
        .then(data => {
            if(data.success == "true") {
                $(ele).addClass('is-success');
                $(ele).children().addClass('fa-check'); 
                $(ele).removeClass('is-loading');
                $(ele).children().removeClass(cssclass);
                $(ele).children().removeClass('fa-exclamation-triangle');
                $(ele).removeClass('is-danger');

                if(timeout) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1100)
                }
            } else {
                $(ele).addClass('is-danger');
                $(ele).children().addClass('fa-exclamation-triangle');
                $(ele).children().removeClass(cssclass);
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');  
                // $(".notification.status ul li").text(data.error);
                // $(".notification.status").slideDown(500);
            }
        }
    );
}

// Essentials
$(document).on('keydown', function (e) {
    if (e.keyCode === 27) {
        $('.modal').hide();
    }
});

$(document).mouseup(function (e) {
    var container = $(".dropdown.is-active");
    if (!container.is(e.target) && container.has(e.target).length === 0) {
        container.removeClass('is-active');
    }
});

$(document).ready(function() {
    $(window).keydown(function(event){
      if(event.keyCode == 13) {
        event.preventDefault();
        return false;
      }
    });
});

// Table Sorting
$('th').click(function () {
    var table = $(this).parents('table').eq(0)
    var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
    this.asc = !this.asc
    if (!this.asc) { rows = rows.reverse() }
    for (var i = 0; i < rows.length; i++) { table.append(rows[i]) }
})

function comparer(index) {
    return function (a, b) {
        var valA = getCellValue(a, index), valB = getCellValue(b, index)
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
    }
}

function getCellValue(row, index) { 
    return $(row).find('td').eq(index).text() 
}

// MultiSelect
$('#switch-select-ms').multiSelect({
    selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
    selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
})

$('#switch-select-ms-2').multiSelect({
    selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
    selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
})

// Custom notification banner timeout
setTimeout(function () {
    $(".notification.status").slideUp(500);
}, 3000)