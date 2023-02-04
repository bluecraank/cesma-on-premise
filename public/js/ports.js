function checkUpdate() {
    let id = window.device_id
    let time = window.timestamp
    fetch('/switch/'+id+'/update-available?time='+time)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.updated) {
                $.notify(data.message, {
                    style: 'bulma-info',
                    autoHide: false,
                    clickToHide: true
                });

                clearInterval(interval);
            }
        });
}
var interval = setInterval(checkUpdate, 25000);

function checkVlanCount() {
    let vlans = $('#bulk-edit-ports').find('select').val();

    if (vlans.length > 1 && $('#bulk-edit-ports').find('input[name="type"]:checked').val() == 'untagged') {
        $('#bulk-edit-ports').find('.is-submit').prop('disabled', true);
        $('#bulk-edit-ports').find('.is-untagged-message').removeClass('is-hidden');
    } else {
        $('#bulk-edit-ports').find('.is-submit').prop('disabled', false);
        $('#bulk-edit-ports').find('.is-untagged-message').addClass('is-hidden');
    }
}

function submitBulkEditPorts(ele, id) {
    $(ele).addClass('is-loading');
    $(ele).siblings('button').addClass('is-hidden');
    $(ele).siblings('.submit-wait').removeClass('is-hidden');

    let ports = [];
    let y = 0;
    let modal2 = $('.modal-vlan-bulk-edit');
    modal2.find('.ports span.tag.is-primary').each(function () {
        let vid = $(this).attr('data-id');
        ports[y] = vid;
        y++;
    });

    $('#bulk-edit-ports .ports').val(JSON.stringify(ports));

    $('#bulk-edit-ports').submit();
}

function updatePortTaggedVlans(ele) {
    let modal = $('.modal-vlan-tagging');
    $(modal).hide();

    let componentId = $(ele).attr('data-component');
    let rowId = $(ele).attr('data-id');
    let deviceId = modal.find('.device_id').val();

    let tr = $('#' + rowId).css('opacity', '0.1');

    let vlans = [];

    let i = 0;
    modal.find('.modal-card-body span.tag.is-primary').each(function () {
        let vid = $(this).attr('data-id');
        vlans[i] = vid;
        i++;
    });



    window.livewire.find(componentId).call('prepareTaggedVlans', rowId, componentId, vlans);
}

function updateTaggedModal(pid, vlans, port, id, typ) {
    let vlansSplitted = vlans.split(',');

    let modal = $('.modal-vlan-tagging');
    modal.find('.port_id').val(port);
    modal.find('.device_id').val(id);
    modal.find('.port_id_title').html(port);
    modal.find('.typ-warning').addClass('is-hidden');
    let idw = $('#' + pid).attr('wire:id');

    $('.modal .is-submit').attr('data-component', idw);
    $('.modal .is-submit').attr('data-id', pid);

    console.log(idw);
    if (typ == 'access') {
        modal.find('.typ-warning').removeClass('is-hidden');
    }

    modal.find('.modal-card-body span.tag').removeClass('is-primary');

    vlansSplitted.forEach(function (vlan) {
        modal.find('.modal-card-body span.tag[data-id="' + vlan + '"]').addClass('is-primary');
    });

    modal.show();
}

function enableEditing() {
    $('.port-vlan-select').each(function() {
        $(this).prop('disabled', false);
    });

    $('.port-description-input').each(function() {
        $(this).prop('disabled', false);
    });

    $('.clickable-tags').find('.is-submit').prop('disabled', false);

    $('.is-save-button').removeClass('is-hidden');
    $('.is-edit-button').addClass('is-hidden');
}

function cancelEditing() {
    $('.port-vlan-select').each(function() {
        $(this).prop('disabled', true);
    });

    $('.port-description-input').each(function() {
        $(this).prop('disabled', true);
    });

    $('.clickable-tags').find('.is-submit').prop('disabled', true);

    $('.is-save-button').addClass('is-hidden');
    $('.is-edit-button').removeClass('is-hidden');
}

function saveEditedPorts(element) {
    let id = window.device_id;
    let csrf = $('meta[name="csrf-token"]').attr('content');
    var data = new FormData();
    data.append('hash', window.apicookie);
    data.append('timestamp', window.apicookie_timestamp);

    $(element).addClass('is-loading');

    fetch('/switch/' + id + '/action/prepare-api', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf
            },
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.apicookie_timestamp = data.timestamp;
                window.apicookie = data.hash;


                let rows = $('#portoverview').find('tr.changed');

                if (rows.length == 0) {
                    $.notify(window.msgnothingchanged, {
                        style: 'bulma-info',
                        autoHideDelay: 8000
                    });
                    $(element).removeClass('is-loading');
                    return;
                }

                rows.each(function(index, value) {
                    let com_id = $(this).attr('wire:id');
                    let cookie = window.apicookie;
                    if (index != rows.length - 1) {
                        window.livewire.find(com_id).call('sendPortVlanUpdate', cookie, false);
                    } else {
                        window.livewire.find(com_id).call('sendPortVlanUpdate', cookie, true).then(() => {
                            $(element).removeClass('is-loading');
                        });
                        window.apicookie = null;
                        window.apicookie_timestamp = null;
                    }
                });

                window.apicookie_timestamp = Date.now();
            } else {
                $.notify(data.message, {
                    style: 'bulma-error',
                    autoHideDelay: 8000
                });
            }
        });
}

$(document).ready(function () {
    $(".clickable-tags .modal-card-body span.tag").click(function () {
        $(this).toggleClass('is-primary');
    });

    window.addEventListener('notify-success', message => {
        let port = message.detail.portid;
        $('#' + port).css('opacity', '1');
        $.notify(message.detail.message, {
            style: 'bulma-success',
            autoHideDelay: 8000
        });
    });
});