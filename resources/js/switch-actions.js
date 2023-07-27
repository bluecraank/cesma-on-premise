import $ from "jquery";

$("#actionCreateBackup").on('click', function(element) {
    let ele = $(this);

    ele.addClass('is-loading');

    axios.post('/device/action/create-backup', {
        _token: $("meta[name=csrf-token]").attr("content"),
    }).then(response => {
        ele.find('i').removeClass();
        console.log(response.data.success);
        if(response.data.success == "true") {
            ele.removeClass('is-loading');
            $(ele).find('i').addClass('fas is-hidden-touch mr-1 fa-check');
        } else {
            ele.removeClass('is-loading');
            $(ele).find('i').addClass('fas is-hidden-touch mr-1 fa-exclamation-triangle');
        }
    });
});

$("#actionSyncPubkeys").on('click', function(element) {
    element.preventDefault();

    let ele = $(this);

    ele.addClass('is-loading');

    axios.post('/device/action/sync-pubkeys', {
        _token: $("meta[name=csrf-token]").attr("content"),
    }).then(response => {
        ele.find('i').removeClass();
        console.log(response.data.success);
        if(response.data.success == "true") {
            ele.removeClass('is-loading');
            $(ele).parent().siblings('.modal-card-body').find('.notification-wrapper').html('<div class="notification is-success">Public keys successfully synced.</div>');
        } else {
            ele.removeClass('is-loading');
            $(ele).parent().siblings('.modal-card-body').find('.notification-wrapper').html('<div class="notification is-danger">'+response.data.message+'</div>');
        }
    });
});