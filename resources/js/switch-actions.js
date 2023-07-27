import $ from "jquery";

$("#actionCreateBackup").on('click', function(element) {
    axios.post('/device/action/create-backup', {
        device_id: $(this).attr("data-device-id")
    })
});

function sw_actions(ele, type, id) {
    let uri = '/device/' + id + '/action/create-backup';
    let cssclass = "fa-hdd";
    let reload = false;

    if (type == "refresh") {
        uri = '/device/' + id + '/action/refresh';
        cssclass = "fa-sync";
        reload = true
    } else if (type == "pubkeys") {
        uri = '/device/' + id + '/action/sync-pubkeys';
        cssclass = "fa-key";
    } else if (type == "vlans") {
        uri = '/device/' + id + '/action/sync-vlans';
        cssclass = "fa-ethernet";
    }

    let formData = new FormData();
    formData.append('device_id', id);

    fetcher(uri, formData, ele, cssclass, reload);
}

function switchCreateBackup(ele) {
    uri = '/device/action/create-backup';
    cssclass = "fa-hdd";
    let form = new FormData();

    fetcher(uri, form, ele, cssclass);
}

function switchSyncPubkeys() {
    let form = new FormData();
    let uri = '/device/action/sync-pubkeys';
    let cssclass = 'fa-key';
    let ele = $(".syncPubButton");
    fetcher(uri, form, ele, cssclass);
    $(".modal-sync-pubkeys").hide();
}