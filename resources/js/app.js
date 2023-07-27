import $ from "jquery";
import './bootstrap';
import './table-sorting';
import './ssh';
import "./switch-actions.js";

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

    if($(this).attr("data-modal") === null) {
        return false;
    }

    let modal = ".modal-" + $(this).attr("data-modal");

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

    console.log("Toggling modal " + modal + " - state " + $(this).is(':visible'))
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
        console.log(response.data.success);
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

// Fetcher function
async function fetcher(uri, form, ele, cssclass, timeout = false, callback = false) {

    let token = $('meta[name="csrf-token"]').attr('content');
    form.append('_token', token);

    $(ele).addClass('is-loading');
    await fetch(uri, {
        method: 'POST',
        body: form
    }).then(response => response.json())
        .then(data => {
            if (data.success == "true") {
                $(ele).addClass('is-success');
                $(ele).find('i').addClass('fa-check');
                $(ele).removeClass('is-loading');
                $(ele).find('i').removeClass(cssclass);
                $(ele).find('i').removeClass('fa-exclamation-triangle');
                $(ele).removeClass('is-danger');
                $.notify(data.message, {
                    style: 'bulma-success',
                    autoHideDelay: 8000
                });

                if (timeout) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1100)
                }

                if(callback) {
                    callback(true);
                }

            } else {
                $(ele).addClass('is-danger');
                $(ele).find('i').addClass('fa-exclamation-triangle');
                $(ele).find('i').removeClass(cssclass);
                $(ele).removeClass('is-loading');
                $(ele).removeClass('is-primary');
                $.notify(data.message, {
                    style: 'bulma-error',
                    autoHideDelay: 8000
                });

                
                if(callback) {
                    callback(false);
                }
            }
        }
        );
}