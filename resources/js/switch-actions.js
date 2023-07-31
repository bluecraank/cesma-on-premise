import $ from "jquery";

$("#actionCreateBackup").on('click', function (element) {
    let ele = $(this);

    ele.addClass('is-loading');

    axios.post('/devices/action/create-backup', {
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

    axios.post('/devices/action/sync-pubkeys', {
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

$(".is-edit-button").on('click', function (element) {
    $('.port-vlan-select').each(function() {
        $(this).prop('disabled', false);
    });

    $('.port-description-input').each(function() {
        $(this).prop('disabled', false);
    });

    $('.clickable-tags').find('.is-submit').prop('disabled', false);

    $('.is-cancel-button').removeClass('is-hidden');
    $('.is-save-button').removeClass('is-hidden');
    $('.is-edit-button').addClass('is-hidden');
});

$("#clickable-vlans span.tag").on("click", function () {
    $(this).toggleClass('is-primary');
});

window.livewire.on('actionOpenTaggedVlansModal', rawData => {
    let data = JSON.parse(rawData);

    let modal = $('.modal-set-tagged-vlans');
    
    modal.find('.id').val(data.port_id);
    modal.find('.name').val(data.port_name);

    let vlansSplitted = data.tagged_vlans.split(',');
    let untaggedVlan = data.current_untagged_vlan;
    
    let livewire_key = $('#' + data.port_id).attr('wire:id');

    $('.modal .is-submit').attr('data-component', livewire_key);
    $('.modal .is-submit').attr('data-id', data.port_id);

    if (data.mode == 'access') {
        modal.find('.typ-warning').removeClass('is-hidden');
    } else {
        modal.find('.typ-warning').addClass('is-hidden');
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
})

$("#actionSetTaggedVlans").on("click", function () {
    let modal = $('.modal-set-tagged-vlans');
    $(modal).hide();

    let componentId = $(this).attr('data-component');
    let rowId = $(this).attr('data-id');

    $('#' + rowId).css('opacity', '0.1');

    let vlans = [];

    modal.find('#clickable-vlans span.tag.is-primary').each(function () {
        let vid = $(this).attr('data-id');
        vlans.push(vid);
    });

    window.livewire.find(componentId).call('prepareTaggedVlans', rowId, vlans);
});