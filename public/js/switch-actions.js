$(document).ready(function () {
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

    $(".is-save-button").on('click', function () {
        let id = window.device_id;
        let element = this;
        let csrf = $('meta[name="csrf-token"]').attr('content');
        var data = new FormData();
        data.append('hash', window.apicookie);
        data.append('timestamp', window.apicookie_timestamp);

        $(element).addClass('is-loading');

        fetch('/devices/' + id + '/action/prepare-api', {
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
    $(".is-notification-uplink-button").click(function () {
        let id = $(this).attr("data-id");
        let csrf = $('meta[name="csrf-token"]').attr('content');
        axios.put('/devices/uplinks', {
            notification: id,
            _token: csrf,
            type: "notification"
        }).then(response => {
            if (response.data.success) {
                $.notify(response.data.message, {
                    style: 'bulma-success',
                    autoHideDelay: 8000
                });
            } else {
                $.notify(response.data.message, {
                    style: 'bulma-error',
                    autoHideDelay: 8000
                });
            }
        });
    });
    $("button.action").on('click', function() {
        let action = $(this).attr("data-action");
        let id = $(this).attr("data-id");
        let ele = $(this);
    
        ele.addClass('is-loading');
    
        axios.post('/devices/' + id + '/action/' + action, {
            device_id: id
        }).then(response => {
            if(response.data.success == "true") {
                $.notify(response.data.message, {
                    style: 'bulma-success',
                    autoHideDelay: 8000
                });
                if(action == "refresh") {
                    setTimeout(function() {
                        window.location.reload();
                    }, 1100)
                }
                ele.removeClass('is-loading');
            } else {
                ele.removeClass('is-loading');
                $.notify(response.data.message, {
                    style: 'bulma-error',
                    autoHideDelay: 8000
                });
            }
        });
    });
});




$(document).ready(function () {
    $.notify.addStyle('bulma-success', {
        html: "<div><div class='notification is-success'><i class='fa-solid fa-check mr-3'></i><span data-notify-text/></div></div>",
        classes: {
            base: {
                "white-space": "nowrap",
                "background-color": "none",
                "padding": "0px",
                "color": "white"
            },
        }
    });

    $.notify.addStyle('bulma-error', {
        html: "<div><div class='notification is-danger'><i class='fa-solid fa-exclamation-triangle mr-3'></i><span data-notify-text/></div></div>",
        classes: {
            base: {
                "white-space": "nowrap",
                "background-color": "none",
                "padding": "0px",
                "color": "white"
            },
        }
    });

    $.notify.addStyle('bulma-info', {
        html: "<div><div class='notification is-info'><i class='fa-solid fa-info mr-3'></i><span data-notify-html/></div></div>",
        classes: {
            base: {
                "white-space": "nowrap",
                "background-color": "none",
                "padding": "0px",
                "color": "white"
            },
        }
    });
});