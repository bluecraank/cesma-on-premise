import $ from "jquery";

$("#actionCreateBackup").on('click', function (element) {
    let ele = $(this);

    ele.addClass('is-loading');

    axios.post('/device/action/create-backup', {
        _token: $("meta[name=csrf-token]").attr("content"),
    }).then(response => {
        ele.find('i').removeClass();
        if (response.data.success == "true") {
            ele.removeClass('is-loading');
            $(ele).find('i').addClass('fas is-hidden-touch mr-1 fa-check');
        } else {
            ele.removeClass('is-loading');
            $(ele).find('i').addClass('fas is-hidden-touch mr-1 fa-exclamation-triangle');
        }
    });
});

$("#actionSyncPubkeys").on('click', function (element) {
    element.preventDefault();

    let ele = $(this);

    ele.addClass('is-loading');

    axios.post('/device/action/sync-pubkeys', {
        _token: $("meta[name=csrf-token]").attr("content"),
    }).then(response => {
        ele.find('i').removeClass();
        if (response.data.success == "true") {
            ele.removeClass('is-loading');
            $(ele).parent().siblings('.modal-card-body').find('.notification-wrapper').html('<div class="notification is-success">Public keys successfully synced.</div>');
        } else {
            ele.removeClass('is-loading');
            $(ele).parent().siblings('.modal-card-body').find('.notification-wrapper').html('<div class="notification is-danger">' + response.data.message + '</div>');
        }
    });
});

$(".is-cancel-button").on('click', function (element) {
    $('.port-vlan-select').each(function () {
        $(this).prop('disabled', true);
    });

    $('.port-description-input').each(function () {
        $(this).prop('disabled', true);
    });

    $('.clickable-tags').find('.is-submit').prop('disabled', true);

    $(this).addClass('is-hidden');
    $('.is-save-button').addClass('is-hidden');
    $('.is-edit-button').removeClass('is-hidden');
});

$(".is-save-button").on('click', function (element) {
    let id = window.device_id;
    let csrf = $('meta[name="csrf-token"]').attr('content');
    var data = new FormData();
    data.append('hash', window.apicookie);
    data.append('timestamp', window.apicookie_timestamp);

    $(element).addClass('is-loading');

    fetch('/device/' + id + '/action/prepare-api', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf
        },
        body: data
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.success == "true") {
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

                rows.each(function (index, value) {
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
                $(element).removeClass('is-loading');
                $.notify(data.message, {
                    style: 'bulma-error',
                    autoHideDelay: 8000
                });
            }
        });
});

$(".is-edit-button").on('click', function (element) {
    $('.port-vlan-select').each(function() {
        $(this).prop('disabled', false);
    });

    $('.port-description-input').each(function() {
        $(this).prop('disabled', false);
    });

    $('.clickable-tags').find('.is-submit').prop('disabled', false);

    $('.is-cancel-button').removeClass('is-hidden');
    $('.is-edit-button').addClass('is-hidden');
});