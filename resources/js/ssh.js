import $ from "jquery";

// SSH Command fetch
$(document).ready(function () {
    $('select[name="execute-specify-switch"]').on('change', function () {
        if ($(this).val() == "specific-switch") {
            $("input[name='which_switch']").val("specific-switch");
            $(".execute-switch-select").removeClass('is-hidden')
            $(".location-select").addClass('is-hidden')
        } else if ($(this).val() == "every-switch") {
            $("input[name='which_switch']").val("every-switch");
            $(".execute-switch-select").addClass('is-hidden')
            $(".location-select").addClass('is-hidden')
        } else {
            $("input[name='which_switch']").val("specific-location");
            $(".location-select").removeClass('is-hidden')
            $(".execute-switch-select").addClass('is-hidden')
        }
    });

    $('select[name="fast-command"]').on('change', function () {
        if ($(this).val() != "Schnellaktion") { $("textarea[name='execute-command']").val($(this).val()); }
    });

    $("button[name='executeSwitchCommand'").click(async function () {
        if ($("select[name='execute-switch-select']").val() && $("textarea[name='execute-command']").val()) {
    
            let command = $("textarea[name='execute-command']").val();
            let which_switch = $("input[name='which_switch']").val();
            let api_token = $("input[name='_token']").val();
    
            if (which_switch == "every-switch") {
                switches = $("select[name='execute-switch-select']").children().map(function () { return { 'id': $(this).val(), 'name': $(this).text() } }).get();
            } else if (which_switch == "specific-switch") {
                switches = $("select[name='execute-switch-select']").val();
                $.each(switches, function (index, value) {
                    switches[index] = { id: value, name: $("select[name='execute-switch-select'] option[value='" + value + "']").text() };
                });
            } else {
                let location = $("select[name='execute-switch-select-loc']").val();
                switches = $("select[name='execute-switch-select']").children().map(function () { if ($(this).attr('data-location') == location) { return { 'id': $(this).val(), 'name': $(this).text() } } }).get();
            }
    
            $(".output-buttons").children().remove();
            $(".outputs").children().remove();
    
            execute(switches, command, which_switch, api_token);
        }
    });

    $('.output-buttons').on("click", "button", function () {
        let id = $(this).attr('data-id');
        $(this).siblings().removeClass("is-primary");
        $(this).addClass("is-primary");
        $(".outputs div").addClass("is-hidden");
        $(`.outputs div[data-id='${id}']`).removeClass("is-hidden");
    })
    
});


async function execute(switches, command) {
    $.each(switches, async function (index, value) {
        $(".output-buttons").append(`<button class='is-loading button' data-id='${value.id}'>${value.name}</button>`);

        let formData = new FormData();
        let token = $('meta[name="csrf-token"]').attr('content');
        formData.append('_token', token);
        formData.append("command", command);
        formData.append("id", value.id);

        fetch(
            '/devices/' + value.id + '/ssh/execute',
            {
                method: 'POST',
                body: formData
            }
        ).then((resp) => resp.json()).then(response => {
            $(".outputs").append(`<div class='is-hidden' data-id='${response.id}'><pre>${response.output}</pre></div>`)
            $(`.output-buttons button[data-id='${response.id}']`).removeClass("is-loading");
            $(`.output-buttons button[data-id='${response.id}']`).html(`${value.name} &nbsp; <i class="fa fa-${response.status}"></i>`)
        });
    });
}