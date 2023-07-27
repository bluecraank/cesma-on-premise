function checkUpdate() {
    let id = window.device_id
    let time = window.timestamp
    fetch('/device/'+id+'/update-available?time='+time)
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

function updateTaggedModal(pid, vlans, untaggedVlan, port, id, typ) {
    let vlansSplitted = vlans.split(',');

    let modal = $('.modal-vlan-tagging');
    modal.find('.port_id').val(port);
    modal.find('.device_id').val(id);
    modal.find('.port_id_title').html(port);
    modal.find('.typ-warning').addClass('is-hidden');
    let idw = $('#' + pid).attr('wire:id');

    $('.modal .is-submit').attr('data-component', idw);
    $('.modal .is-submit').attr('data-id', pid);

    if (typ == 'access') {
        modal.find('.typ-warning').removeClass('is-hidden');
    }

    modal.find('.modal-card-body #clickable-vlans span.tag').removeClass('is-primary');
    modal.find('.modal-card-body #clickable-vlans span.tag').removeClass('is-info');

    vlansSplitted.forEach(function (vlan) {
        if (untaggedVlan == vlan) {
            modal.find('.modal-card-body span.tag[data-id="' + vlan + '"]').addClass('is-info');
        } else {
            modal.find('.modal-card-body span.tag[data-id="' + vlan + '"]').addClass('is-primary');
        }
    });

    modal.show();
}

$(document).ready(function () {
    $(".clickable-tags .modal-card-body #clickable-vlans span.tag").click(function () {
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

    window.addEventListener('notify-error', message => {
        let port = message.detail.portid;
        $('#' + port).css('opacity', '1');
        $.notify(message.detail.message, {
            style: 'bulma-error',
            autoHideDelay: 8000
        });
    });
});