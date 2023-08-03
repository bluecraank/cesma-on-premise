import $ from "jquery";
import './bootstrap';
import './table-sorting';
import './ssh';
import "./switch-actions.js";
import 'file-saver';

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

    // Form-Submit
    if($(this).hasClass("no-prevent") || $(this).hasClass("submit")) {
        return true;
    }

    if($(this).attr("data-modal") == null || $(this).attr("data-modal") == "" || !$(this).attr("data-modal")) {
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

function exportCSV(table, name) {
    let csv = [];
    let rows = $(table).find("tr") 
    rows.each(function(index, row) {
        let cells = [];
        if(table.hasClass("without-header")) {
            cells = $(row).find("td");
        } else {
            cells = $(row).find("td, th");
        }


        let rowText = [];
        cells.each(function(index, cell) {
            let text = $(cell).text();
            if(text == "") {
                text = $(cell).find("span").text();
            }
            text = text.trim();
            
            rowText.push(text);
        });

        csv.push(rowText.join(';'));       
    })

    const csvFile = new Blob([csv.join('\n')], {type: "text/csv;charset=utf-8;"});
    saveAs(csvFile, name+".csv");
}

$(".export-csv-button").on('click', function(element) {
    let table = $(this).attr("data-table");
    let name = $(this).attr("data-file-name");
    exportCSV($("."+table), name);
});