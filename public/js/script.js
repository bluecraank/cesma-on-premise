// Custom notification banner timeout
setTimeout(function () {
    $(".notification.status").slideUp(500);
}, 3000)

// Only one checkbox allowed
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

// Put fast-command in textarea
$('select[name="fast-command"]').on('change', function () {
    if ($(this).val() != "Schnellaktion") { $("textarea[name='execute-command']").val($(this).val()); }
});


// MultiSelect
$('#switch-select-ms').multiSelect({
    selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
    selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
})

$('#switch-select-ms-2').multiSelect({
    selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
    selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
})

// Loggingtable searchable
$('#fulltextsearch').on('input', function () {
    var text = $(this).val();
    $('#logging-table tbody tr').show();
    $('#logging-table tbody tr:not(:contains(' + text + '))').hide();
});

// fetch execute command api
$("button[name='executeSwitchCommand'").click(async function () {
    if ($("select[name='execute-switch-select']").val() && $("input[name='execute-passphrase']").val() && $("textarea[name='execute-command']").val()) {

        let command = $("textarea[name='execute-command']").val();
        let passphrase = $("input[name='execute-passphrase']").val();
        let which_switch = $("input[name='which_switch']").val();
        let token = $("input[name='_token']").val();

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

        execute(switches, command, passphrase, which_switch, token);
    }
});

async function execute(switches, command, passphrase, type, token) {

    $.each(switches, async function (index, value) {
        $(".output-buttons").append(`<button class='is-loading button' data-id='${value.id}'>${value.name}</button>`);

        let formData = new FormData();
        formData.append("command", command);
        formData.append("passphrase", passphrase);
        formData.append("id", value.id);
        formData.append("_token", token);

        fetch(
            '/switch/perform-ssh',
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


// Table Sorter
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

function getCellValue(row, index) { return $(row).find('td').eq(index).text() }


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

function uploadBackup(id, created_at, device,  name) {

    let modal = $('.modal-upload-backup');
    modal.find('.id').val(id);
    modal.find('.name').val(name);
    modal.find('.device-id').val(device);
    modal.find('.created').val(created_at);
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

function editVlanModal(id, name, description) {
    let modal = $('.modal-edit-vlan');
    modal.find('.vlan-id').val(id);
    modal.find('.vlan-name').val(name);
    modal.find('.vlan-desc').val(description);
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


// Verbotene Befehle Funktion
function add_blacklist_command() {
    var inputText = $('input[name="blacklist_new_command"]').val();
    $("#command-list-ul").append('<li><i onclick="$(this).parent().remove();" style="color:red;margin-right:10px;cursor:pointer" class="remove-command-list fa-sharp fa-solid fa-xmark"></i> ' + inputText + '</li>');
    $('input[name="blacklist_new_command"]').val("");
}

function submitSystemsettings() {
    let form = $('#form-systemsettings');

    var commands = $("#command-list-ul li").map(function () {
        return $(this).text().replace(" ", "");
    }).get();

    let stringify = JSON.stringify(commands)
    $('input[name="blacklist_commands"]').val(stringify);

    $(".modal-save-settings").show();
}

function refreshSwitch(ele) {
    //$("#refresh-form").submit();
    $(ele).addClass('is-loading');

    let form = $("#refresh-form").serialize();
    fetch('/switch/refresh', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: form
    }).then(response => response.json())
        .then(data => {

            if(data.success == "true") {
                $(ele).removeClass('is-loading');
                $(ele).addClass('is-success');
                $(ele).children().removeClass('fa-rotate');
                $(ele).children().addClass('fa-check'); 
                $(ele).children().remove('fa-exclamation-triangle');
                $(ele).remove('is-danger');
                setTimeout(function () {
                    window.location.reload();
                }, 750)

            } else {
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');
                $(ele).addClass('is-danger');
                $(ele).children().removeClass('fa-sync');
                $(ele).children().addClass('fa-exclamation-triangle');
                
                $(".notification.status ul li").text(data.error);
                $(".notification.status").slideDown(500);
            }
        }
    );

}

function uploadPubkeys(ele) {
    $(ele).addClass('is-loading');


    let form = $("#actions-form").serialize();
    fetch('/switch/upload/pubkeys', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: form
    }).then(response => response.json())
        .then(data => {

            if(data.success == "true") {
                $(ele).removeClass('is-loading');
                $(ele).addClass('is-success');
                $(ele).children().removeClass('fa-rotate');
                $(ele).children().addClass('fa-check'); 
                $(ele).children().remove('fa-exclamation-triangle');
                $(ele).remove('is-danger');
            } else {
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');
                $(ele).addClass('is-danger');
                $(ele).children().removeClass('fa-sync');
                $(ele).children().addClass('fa-exclamation-triangle');
                
                $(".notification.status ul li").text(data.error);
                $(".notification.status").slideDown(500);
            }
        }
    );

}

function getClients(ele) {
    $(ele).addClass('is-loading');


    let form = $("#actions-form").serialize();
    fetch('/switch/get/clients', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: form
    }).then(response => response.json())
        .then(data => {

            if(data.success == "true") {
                $(ele).removeClass('is-loading');
                $(ele).addClass('is-success');
                $(ele).children().remove('fa-exclamation-triangle');
                $(ele).remove('is-danger');
                $(ele).children().removeClass('fa-computer');
                $(ele).children().addClass('fa-check'); 
            } else {
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');
                $(ele).addClass('is-danger');
                $(ele).children().removeClass('fa-computer');
                $(ele).children().addClass('fa-exclamation-triangle');
                
                $(".notification.status ul li").text(data.error);
                $(".notification.status").slideDown(500);
            }
        }
    );

}

function createBackup(ele) {
    $(ele).addClass('is-loading');


    let form = $("#actions-form").serialize();
    fetch('/switch/create/backup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: form
    }).then(response => response.json())
        .then(data => {

            if(data.success == "true") {
                $(ele).removeClass('is-loading');
                $(ele).addClass('is-success');
                $(ele).children().remove('fa-exclamation-triangle');
                $(ele).remove('is-danger');
                $(ele).children().removeClass('fa-hdd');
                $(ele).children().addClass('fa-check'); 
            } else {
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');
                $(ele).addClass('is-danger');
                $(ele).children().removeClass('fa-hdd');
                $(ele).children().addClass('fa-exclamation-triangle');
                
                $(".notification.status ul li").text(data.error);
                $(".notification.status").slideDown(500);
            }
        }
    );

}

function doAllDeviceAction(type, ele) {
    $(ele).addClass('is-loading');



    let form = $("#form-all-devices").serialize();

    let uri = '/switch/create/backup/all';
    let cssclass = 'fa-hdd';

    if (type == "clients") {
        uri = '/switch/get/clients/all';
        cssclass = 'fa-computer';
    } else if (type == "pubkeys") {
        uri = '/switch/upload/pubkeys/all';
        cssclass = 'fa-sync';
    }

    fetch(uri, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: form
    }).then(response => response.json())
        .then(data => {
            if(data.success == "true") {
                $(ele).removeClass('is-loading');
                $(ele).addClass('is-success');
                $(ele).children().removeClass(cssclass);
                $(ele).children().addClass('fa-check'); 
                $(ele).children().remove('fa-exclamation-triangle');
                $(ele).remove('is-danger');
            } else {
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');
                $(ele).addClass('is-danger');
                $(ele).children().removeClass(cssclass);
                $(ele).children().addClass('fa-exclamation-triangle');
                
                $(".notification.status ul li").text(data.error);
                $(".notification.status").slideDown(500);
            }
        }
    );
}

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