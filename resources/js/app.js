import $ from "jquery";
import './bootstrap';
import './table-sorting';
import './ssh';
import "./switch-actions.js";

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
});

$("#themeSwitch").on("change", function () {
    let theme = $(this).val();
    if (theme == 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        switchTheme('dark');

    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
        switchTheme('light');
    }
});

$("#themeSwitch").val(document.documentElement.getAttribute('data-theme'));

// $.fn.data() replacement, because empty data attributes are not returned
$.fn.info = function () {
    var data = {};
    [].forEach.call(this.get(0).attributes, function (attr) {
        if (/^data-/.test(attr.name)) {
            var camelCaseName = attr.name.substr(5).replace(/-(.)/g, function ($0, $1) {
                return $1.toUpperCase();
            });
            data[camelCaseName] = attr.value;
        }
    });
    return data;
}

// Listen on buttons, toggle modals and fill them with data
$("button").on('click', function() {

    if($(this).attr("data-modal") == null || $(this).attr("data-modal") == "" || !$(this).attr("data-modal")) {
        return false;
    }

    let modal = ".modal-" + $(this).attr("data-modal");

    console.log(modal)

    $(modal).toggle();

    if($(this).is(':visible')) {    
        // Get data-attributes and put them into classes in modal
        $.each($(this).info(), function(data_key, data_value) {
            if(data_key !== "modal") {
                let element = $(modal).find("."+data_key)
                if(element.is("input[type=checkbox]")) {
                    let negate = element.attr("data-negate");

                    if(negate) {
                        data_value = !data_value;
                    }

                    if(data_value == 1) {
                        element.prop("checked", true);
                    } else {
                        element.prop("checked", false);
                    }
                } else if(element.is("select")) {
                    element.find("option[value="+data_value+"]").attr("selected", "selected");
                } else if (element.is("textarea")) {
                    element.html(data_value);
                } else if (element.is("input")) {
                    element.val(data_value);
                }
            }
        });
    }
});

$("button.action").on('click', function() {
    let action = $(this).attr("data-action");
    let id = $(this).attr("data-id");
    let ele = $(this);

    ele.addClass('is-loading');

    axios.post('/device/' + id + '/action/' + action, {
        device_id: id
    }).then(response => {
        ele.find('i').removeClass();
        if(response.data.success == "true") {
            if(action == "refresh") {
                setTimeout(function() {
                    window.location.reload();
                }, 1100)
            }
            ele.removeClass('is-loading');
            $(ele).find('i').addClass('fas is-hidden-touch mr-1 fa-check');
        } else {
            ele.removeClass('is-loading');
            $(ele).find('i').addClass('fas is-hidden-touch mr-1 fa-exclamation-triangle');
        }
    });
});

// Show loading animation in submit button
$(document).on('submit','form',function(){
    $(this).find('button[type=submit]').addClass('is-loading');
    $(this).find('button.submit').addClass('is-loading');
 });


// Scroll to top
$(".scroll-to-top button").on('click', function(element) {
    $('html, body').animate({ scrollTop: 0 }, 'normal');
});

window.addEventListener("scroll", () => {
    if (window.pageYOffset > 100) {
        $(".scroll-to-top").removeClass('is-hidden');
    } else {
        $(".scroll-to-top").addClass('is-hidden');
    }
});

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

$("#actionLogout").on('click', function(element) {
    $("#logoutForm").submit();
});

$("#systemTabList li").on('click', function(element) {
    let tab = $(this).attr("data-tab");
    $(this).siblings().removeClass('is-active');
    $(this).addClass('is-active');
    $('.tabsbox').addClass('is-hidden');
    $('.tab-parent').find("[data-id='"+tab+"']").removeClass('is-hidden');
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