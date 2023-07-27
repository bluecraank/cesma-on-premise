import './bootstrap';
import $ from "jquery";

$("#actionCreateBackup").on('click', function(element) {
    alert("works!");
});

$("button").on('click', function(element) {

    if(element.attr("data-modal") === NULL) {
        return false;
    }

    let modal = ".modal-" + element.attr("data-modal");

    $(modal).toggle();

    if($(element).is(':visible')) {    
        // Get data-attributes and put them into classes in modal
        $.each(element.data(), function(data_key, data_value) {
            console.log(data_key)
            console.log(data_value)
    
            $(modal).find(data_key).val(data_value);
        });
    }

    console.log("Toggling modal " + modal + " - state " + $(element).is(':visible'))
});