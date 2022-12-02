// Custom notification banner timeout
setTimeout(function() {
    $(".status-msg").fadeOut(250);
}, 3000)

let uri = location.pathname.toString().split("/")[1];
//document.title = uri + " | Central Switch Management";

$(".menu-list ."+uri).addClass("has-text-link");

// Only one checkbox allowed
$('select[name="execute-specify-switch"]').on('change', function() {
    if ($(this).val() == "specific-switch") {
        $("input[name='which_switch']").val("specific-switch");
        $(".execute-switch-select").removeClass('is-hidden')
        $(".location-select").addClass('is-hidden')
    } else if($(this).val() == "every-switch") {
        $("input[name='which_switch']").val("every-switch");
        $(".execute-switch-select").addClass('is-hidden')
        $(".location-select").addClass('is-hidden')
    } else {
        $("input[name='which_switch']").val("specific-location");
        $(".location-select").removeClass('is-hidden')
        $(".execute-switch-select").addClass('is-hidden')
    }
});

// Put fast-command in textarea
$('select[name="fast-command"]').on('change', function() {
    if($(this).val() != "Schnellaktion") { $("textarea[name='execute-command']").val($(this).val()); }
});


// MultiSelect
$('#switch-select-ms').multiSelect({
    selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
    selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
})

$('#switch-select-ms-2').multiSelect({
    selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
    selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
})

// Loggingtable searchable
$('#fulltextsearch').on('input', function(){
    var text = $(this).val();
    $('#logging-table tbody tr').show();   
    $('#logging-table tbody tr:not(:contains(' + text + '))').hide();
});

// fetch execute command api
$( "button[name='executeSwitchCommand'" ).click(async function() {
   if($("select[name='execute-switch-select']").val() && $("input[name='execute-passphrase']").val() && $("textarea[name='execute-command']").val()) {

        var formData = {
            type: "EXECUTE",
            command: $("textarea[name='execute-command']").val(),
            passphrase: $("input[name='execute-passphrase']").val(),
            which_switch: $("input[name='which_switch']").val()
        };
      
        if(formData['which_switch'] == "every-switch") {
            which_switch_value = $("select[name='execute-switch-select']").children().map(function() {return $(this).val();}).get();
        } else if(formData['which_switch'] == "specific-switch") {
            which_switch_value = $("select[name='execute-switch-select']").val();
        } else {
            ajaxResult = []
            slocation = $("select[name='execute-switch-select-loc']").val();
            $.ajax({
                type: "POST", 
                url: "/api/v1/switch/bylocation",
                data: {location: slocation},
                dataType: "json",
                async: false,
                success: function(data)
                { 
                   ajaxResult = data;
                }
            }).responseText;
            which_switch_value = ajaxResult;
        }
        $(".output-buttons").children().remove();
        $(".outputs").children().remove();
        getExecuteOutput(which_switch_value, formData);
    }   
});

async function getExecuteOutput(which_switch_value, formData) {
    console.log(which_switch_value);
    $.each(which_switch_value, async function( index, value ) {
        if(formData['which_switch'] == "specific-location") {
            formData['value'] = value['id'];
            var host = value['name'] 
        } else {
            formData['value'] = value;
            var host = $("select[name='execute-switch-select'] option[value='"+formData.value+"'").text();
        }

        //$("#results tbody").append(`<tr data-id="${value}"><td>${host}</td><td class="second" style="word-wrap: break-word;max-width: 450px;">Awaiting...</td><td class="third has-text-centered"><div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></td></tr>`);
        $(".output-buttons").append(`<button class='is-loading button' data-id='${formData.value}'>${host}</button>`);
        let foormData = new FormData();
        foormData.append("command", formData.command);
        foormData.append("passphrase", formData.passphrase);
        foormData.append("id", formData.value);

        fetch(
            '/api/v1/switch/command',
            {
                method: 'POST',
                body: foormData
            }
        ).then((resp) => resp.json()).then(response => {
            //$("tr[data-id='"+value+"'] td.third").html(`<i class="fa fa-${response.status}"></i>`); 
            //$("tr[data-id='"+value+"'] td.second").html(response.output);
            $(".outputs").append(`<div class='is-hidden' data-id='${response.id}'><pre>${response.output}</pre></div>`)
            $(`.output-buttons button[data-id='${response.id}']`).removeClass("is-loading");
            $(`.output-buttons button[data-id='${response.id}']`).html(`${host} &nbsp; <i class="fa fa-${response.status}"></i>`)
        });
    });
}

$('.output-buttons').on("click","button", function(){
    let id = $(this).attr('data-id');
    $(this).siblings().removeClass("is-primary");
    $(this).addClass("is-primary");
    $(".outputs div").addClass("is-hidden");
    $(`.outputs div[data-id='${id}']`).removeClass("is-hidden");
})


// Table Sorter
$('th').click(function(){
    var table = $(this).parents('table').eq(0)
    var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
    this.asc = !this.asc
    if (!this.asc){rows = rows.reverse()}
    for (var i = 0; i < rows.length; i++){table.append(rows[i])}
})

function comparer(index) {
    return function(a, b) {
        var valA = getCellValue(a, index), valB = getCellValue(b, index)
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
    }
}

function getCellValue(row, index){ return $(row).find('td').eq(index).text() }


function editSwitchModal(data) {

    let modal = $('.modal-edit-switch');
    modal.find('.switch-id').val(data.id);
    modal.find('.switch-name').val(data.name);
    modal.find('.switch-numbering').val(data.numbering);
    modal.find('.switch-fqdn').val(data.fqdn);
    modal.find('.switch-ip').val(data.ip);
    modal.find('.switch-location').val(data.location);
    modal.find('.switch-building').val(data.building);
    modal.find('.switch-details').val(data.location_details);
    modal.show()
}

function deleteSwitchModal(id, name) {
    let modal = $('.modal-delete-switch');
    modal.find('.switch-id').val(id);
    modal.find('.switch-name').val(name);
    modal.show()
}

function editBuildingModal(id, name) {
    let modal = $('.modal-edit-building');
    modal.find('.building-id').val(id);
    modal.find('.building-name').val(name);
    modal.show()
}

function deleteBuildingModal(id, name) {
    let modal = $('.modal-delete-building');
    modal.find('.building-id').val(id);
    modal.find('.building-name').val(name);
    modal.show()
}

function deleteVlanModal(id, name) {
    let modal = $('.modal-delete-vlan');
    modal.find('.vlan-id').val(id);
    modal.find('.vlan-name').val(name);
    modal.show()
}

function editVlanModal(id, name, description) {
    let modal = $('.modal-edit-vlan');
    modal.find('.vlan-id').val(id);
    modal.find('.vlan-name').val(name);
    modal.find('.vlan-desc').val(description);
    modal.show()
}

// Live Switch Info Getter
$('select[name="port-switch-id"]').on('change', function() {
    let value = $('select[name="port-switch-id"]').val()
    let foormData = new FormData();
    foormData.append("port-switch-id", value);
    fetch(
        '/api/v1/switch/livedata',
        {
            method: 'POST',
            body: foormData
        }
    ).then((resp) => resp.json()).then(response => {
        $(".live-body").html(response.output);
    });
});

// Verbotene Befehle Funktion
function add_blacklist_command() {
    var inputText = $('input[name="blacklist_new_command"]').val();
    $("#command-list-ul").append('<li><i onclick="$(this).parent().remove();" style="color:red;margin-right:10px;cursor:pointer" class="remove-command-list fa-sharp fa-solid fa-xmark"></i> '+inputText+'</li>');
    $('input[name="blacklist_new_command"]').val("");
  }

$( document ).on( 'keydown', function ( e ) {
    if ( e.keyCode === 27 ) { // ESC
        $(".modal").hide();
    }
});

function submitSystemsettings() {
    let form = $('#form-systemsettings');

    var commands = $("#command-list-ul li").map(function() {
        return $(this).text().replace(" ", "");
    }).get();

    let stringify = JSON.stringify(commands)
    $('input[name="blacklist_commands"]').val(stringify);

    $(".modal-save-settings").show();
}

document.addEventListener('visibilitychange', function (event) {
    if (document.hidden) {
        console.log('not visible');
    } else {
        console.log('is visible');
    }
});

// $(window).on('load', function () {
//     $('#loading').fadeOut(100);
//   }) 

// Prevent F5 Refresh
// if ( window.history.replaceState ) {
//     window.history.replaceState(null, null, window.location.href );
// }