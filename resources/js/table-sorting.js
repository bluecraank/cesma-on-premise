import $ from "jquery";

function comparer(index) {
    return function (a, b) {
        var valA = getCellValue(a, index), valB = getCellValue(b, index)
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
    }
}

function getCellValue(row, index) {
    return $(row).find('td').eq(index).text()
}

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
