$("#portoverview").on('dblclick', 'td.input-field', function () {
    let cell_data = $.trim($(this).text());
    let id = $(this).attr('data-port');
    let tmp = "<div data-current-description=\"" + cell_data + "\" id=\"" + id +
        "\" class=\"control\"><input class=\"input is-info\" type=\"text\" placeholder=\"Portname\" value=\"" +
        cell_data +
        "\"></div>";

    $(this).html(tmp);

    $("#" + id).keyup(function (event) {
        if (event.which == 13) {
            storePortDescription(this, $(this).find('input').val(), $(this).attr('data-current-description'), $(this).attr('id'),
                '{{ $device->id }}');
        } else if (event.which == 27) {
            $(this).parent().html($(this).find('input').val());
        }
    });
});

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

function storePortDescription(ele, description, old_desc, port, device) {

    if (description == old_desc) {
        $(ele).parent().html(description);
        return;
    }

    let form = new FormData();
    let uri = '/switch/' + device + '/action/update-port-name';
    let cssclass = 'fa-edit';

    $(ele).find('.icon').hide();
    form.append('port', port);
    form.append('description', description);
    form.append('device_id', device);

    fetcher(uri, form, ele, cssclass, false, function (success) {
        if (success) {
            $(ele).parent().html(description);
        }
        $(ele).find('.icon').show();
    });
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
    })    
});