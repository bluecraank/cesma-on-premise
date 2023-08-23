window.addEventListener('notify-success', message => {
    $.notify(message.detail.message, {
        style: 'bulma-success',
        autoHideDelay: 8000
    });
});

window.addEventListener('notify-error', message => {
    $.notify(message.detail.message, {
        style: 'bulma-error',
        autoHideDelay: 8000
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
