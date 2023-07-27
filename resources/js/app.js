import './bootstrap';
import $ from "jquery";

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

$("#actionCreateBackup").on('click', function(element) {
    alert("works!");
});

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

// Show loading animation in submit button
$(document).on('submit','form',function(){
    $(this).find('button[type=submit]').addClass('is-loading');
    $(this).find('button.submit').addClass('is-loading');
 });