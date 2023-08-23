import './bootstrap';
import $ from 'jquery';
import 'multiselect/js/jquery.multi-select.js';

setTimeout(function () {
    $(".notification.is-response").slideUp(250);
}, 4000)

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
$("button").on('click', function () {

    // Form-Submit
    if ($(this).hasClass("no-prevent") || $(this).hasClass("submit")) {
        return true;
    }

    if ($(this).attr("data-modal") == null || $(this).attr("data-modal") == "" || !$(this).attr("data-modal")) {
        return false;
    }

    let modal = ".modal-" + $(this).attr("data-modal");

    $(modal).toggle();

    if ($(this).is(':visible')) {
        // Get data-attributes and put them into classes in modal
        $.each($(this).info(), function (data_key, data_value) {
            if (data_key !== "modal") {
                let element = $(modal).find("." + data_key)
                if (element.is("input[type=checkbox]")) {
                    let negate = element.attr("data-negate");

                    if (negate) {
                        data_value = !data_value;
                    }

                    if (data_value == 1) {
                        element.prop("checked", true);
                    } else {
                        element.prop("checked", false);
                    }
                } else if (element.is("select")) {
                    element.find("option[value=" + data_value + "]").attr("selected", "selected");
                } else if (element.is("textarea")) {
                    element.html(data_value);
                } else if (element.is("input")) {
                    element.val(data_value);
                }

                $(modal).find(".site-permission").prop("checked", false);

                if (data_key == "sites") {
                    let data = JSON.parse(data_value);
                    $.each(data, function (key, value) {
                        console.log(value);
                        $(modal).find(".checkbox input[value=" + value + "]").prop("checked", true);
                    });
                }

            }
        });
    }
});

// Show loading animation in submit button
$(document).on('submit', 'form', function () {
    $(this).find('button[type=submit]').addClass('is-loading');
    $(this).find('button.submit').addClass('is-loading');
});


// Scroll to top
$(".scroll-to-top button").on('click', function (element) {
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

$("#actionLogout").on('click', function (element) {
    $("#logoutForm").submit();
});

function exportCSV(table, name) {
    let csv = [];
    let rows = $(table).find("tr")
    rows.each(function (index, row) {
        let cells = [];
        if (table.hasClass("without-header")) {
            cells = $(row).find("td");
        } else {
            cells = $(row).find("td, th");
        }


        let rowText = [];
        cells.each(function (index, cell) {
            let text = $(cell).text();
            if (text == "") {
                text = $(cell).find("span").text();
            }
            text = text.trim();

            rowText.push(text);
        });

        csv.push(rowText.join(';'));
    })

    const csvFile = new Blob([csv.join('\n')], { type: "text/csv;charset=utf-8;" });
    saveAs(csvFile, name + ".csv");
}

$(".export-csv-button").on('click', function () {
    let table = $(this).attr("data-table");
    let name = $(this).attr("data-file-name");
    exportCSV($("." + table), name);
});

$(".change-site-link").on('click', function () {
    let site = $(this).attr("data-id");
    $(".change-site-input").val(site);
    $("#change-site").submit();
});

$(".menu-is-dropdown").on('click', '.has-dropdown-icon', function () {
    let dropdown = $(this).parent();
    dropdown.toggleClass('is-active');

    if (dropdown.hasClass('is-active')) {
        dropdown.find(".dropdown-icon .icon i").removeClass('mdi-plus');
        dropdown.find(".dropdown-icon .icon i").addClass('mdi-minus');
    } else {
        dropdown.find(".dropdown-icon .icon i").removeClass('mdi-minus');
        dropdown.find(".dropdown-icon .icon i").addClass('mdi-plus');
    }
});

// Execute SSH command
document.addEventListener('exec-ssh-command', function (e) {
    setTimeout(function () {
        $(".results .card-content").append(`<button class='is-small is-loading button show-result-button' data-id='${e.detail.id}'>${e.detail.name}</button>`);
    }, 100);

    let formData = new FormData();
    let token = $('meta[name="csrf-token"]').attr('content');
    formData.append('_token', token);
    formData.append("command", e.detail.command);
    formData.append("id", e.detail.id);

    fetch(
        '/devices/' + e.detail.id + '/execute',
        {
            method: 'POST',
            body: formData
        }
    ).then((resp) => resp.json()).then(response => {
        console.log("Result!")
        console.log(response);

        let button = $(".results .card-content").find(`button[data-id='${e.detail.id}']`);
        button.html(e.detail.name);
        if (response.status == "check") {
            button.addClass('is-success');
        } else {
            button.addClass('is-danger');
        }

        button.removeClass('is-loading');

        $(".output-data").append(`<div data-id='${e.detail.id}' class='output-item is-hidden'><pre>${response.output}</pre></div>`);
    });
});


$(".results").on('click', '.show-result-button', function () {
    let id = $(this).attr("data-id");
    $(".output-item").addClass('is-hidden');
    $(".output-item[data-id='" + id + "']").toggleClass('is-hidden');
});

$(".is-collapsable-button").on('click', function () {
    $(this).parent().parent().siblings('.card-content').toggleClass('is-hidden');
    $(this).find("i").toggleClass('mdi-chevron-down').toggleClass('mdi-chevron-up');
});

// Sync Vlans
document.addEventListener('sync-vlan-to-device', function (e) {
    console.log(e.detail);

    setTimeout(function () {
        $("tbody.results").append(`<tr data-id='${e.detail.device}'><td>${e.detail.name}</td><td><button class='is-white button is-loading'></button></td><td><button class='is-white button is-loading'></button></td><td><button class='is-white button is-loading'></button></td><td><button class='is-white button is-loading'></button></td></tr>`);
    }, 100);

    console.log($("tbody.results"));
    let formData = new FormData();
    let token = $('meta[name="csrf-token"]').attr('content');
    formData.append('_token', token);
    formData.append("device", e.detail.device);
    formData.append("vlans", JSON.stringify(e.detail.vlans));
    formData.append("testmode", e.detail.testmode);
    formData.append("createVlans", e.detail.createVlans);
    formData.append("renameVlans", e.detail.renameVlans);
    formData.append("tagToUplink", e.detail.tagToUplink);


    fetch(
        '/devices/' + e.detail.device + '/sync-vlans',
        {
            method: 'POST',
            body: formData
        }
    ).then((resp) => resp.json()).then(response => {
        let testmode = response.test ? " (Testmode)" : "";

        let element = "<span class='has-text-success'>"+response.message+testmode+"</span>";
        if (response.status == "error") {
            element = "<span class='has-text-danger'>"+response.message+testmode+"</span>";
        }
        let data = `<td>${e.detail.name}</td><td>${element}</td><td>${response.created}</td><td>${response.renamed}</td><td>${response.tagged_to_uplink}</td>`
        $("tbody.results").find(`tr[data-id='${e.detail.device}']`).html(data);
    });
});

function comparer(index) {
    return function (a, b) {
        var valA = getCellValue(a, index), valB = getCellValue(b, index)
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
    }
}

function getCellValue(row, index) {
    return $(row).find('td').eq(index).text()
}

// Table Sorting
$(document).ready(function () {
    $('.table thead tr th').click(function (e) {
        if($(e).children().length > 0) {
            return;
        }

        if (e.target !== e.currentTarget) return;
        var table = $(this).parents('table').eq(0)
        var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
        this.asc = !this.asc
        if (!this.asc) { rows = rows.reverse() }
        for (var i = 0; i < rows.length; i++) { table.append(rows[i]) }
    });

    $('th label').click(function (e) {
        var table = $(this).parents('table').eq(0)
        var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).attr('data-row')))

        $('th label').children().addClass('is-hidden');
        $(this).children().removeClass('is-hidden');
        $(this).children().toggleClass('fa-angle-down');
        $(this).children().toggleClass('fa-angle-up');

        this.asc = !this.asc

        if (!this.asc) { rows = rows.reverse() }
        for (var i = 0; i < rows.length; i++) { table.append(rows[i]) }
    })
});

$(".action").on('click', function () {

    let action = $(this).attr("data-action");
    let id = $(this).attr("data-id");

    $(this).addClass("is-loading");

    let url;

    if(action == "backup") {
        url = "/devices/"+id+"/backup";
    } else if(action == "update") {
        url = "/devices/"+id+"/update";
    } else if(action == "sync-pubkeys") {
        url = "/devices/"+id+"/sync-pubkeys";
    }

    fetch(
        url,
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        }
    ).then((resp) => resp.json()).then(response => {
        var event = new CustomEvent('notify-error', { detail: { message: response.message}})
        var event2 = new CustomEvent('notify-success', { detail: { message: response.message}})

        $(this).removeClass("is-loading");


        if(response.success == "true") {
            window.dispatchEvent(event2);
            if(action == "update") {
                setTimeout(() => {
                    document.location.reload();
                  }, 1000);
            }
            return;
        }

        window.dispatchEvent(event);
    });
});
