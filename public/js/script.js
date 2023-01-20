// Essentials
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

$(document).ready(function() {
    $(window).keydown(function(event){
      if(event.keyCode == 13) {
        event.preventDefault();
        return false;
      }
    });
});

// Table Sorting
$('th').click(function (e) {
    if(e.target !== e.currentTarget) return;
    var table = $(this).parents('table').eq(0)
    var rows = table.find('tr:gt(1)').toArray().sort(comparer($(this).index()))
    this.asc = !this.asc
    if (!this.asc) { rows = rows.reverse() }
    for (var i = 0; i < rows.length; i++) { table.append(rows[i]) }
})

$('th label').click(function (e) {
    // if(e.target !== e.currentTarget) return;
    var table = $(this).parents('table').eq(0)
    var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).attr('data-row')))

    console.log($(this).index());

    $('th label').children().addClass('is-hidden');
    $(this).children().removeClass('is-hidden');
    $(this).children().toggleClass('fa-angle-down');
    $(this).children().toggleClass('fa-angle-up');

    this.asc = !this.asc

    if (!this.asc) { rows = rows.reverse() }
    for (var i = 0; i < rows.length; i++) { table.append(rows[i]) }
})

function comparer(index) {
    return function (a, b) {
        var valA = getCellValue(a, index), valB = getCellValue(b, index)
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
    }
}

function getCellValue(row, index) { 
    return $(row).find('td').eq(index).text() 
}

// MultiSelect
$('#switch-select-ms').multiSelect({
    selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
    selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
})

$('#switch-select-ms-2').multiSelect({
    selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
    selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
})

// Custom notification banner timeout
setTimeout(function () {
    $(".notification.status").slideUp(500);
}, 3000)