$.notify.addStyle('bulma-success', {
    // html: "<div><i class='fa-solid fa-check mr-1'></i> <span data-notify-text/></div>",
    html: "<div><div class='notification is-success'><i class='fa-solid fa-check mr-3'></i><span data-notify-text/></div></div>",
    classes: {
        base: {
            "white-space": "nowrap",
            "background-color": "none",
            "padding": "0px",
            "color": "white"
        },
    }
});

$.notify.addStyle('bulma-error', {
    // html: "<div><i class='fa-solid fa-check mr-1'></i> <span data-notify-text/></div>",
    html: "<div><div class='notification is-danger'><i class='fa-solid fa-exclamation-triangle mr-3'></i><span data-notify-text/></div></div>",
    classes: {
        base: {
            "white-space": "nowrap",
            "background-color": "none",
            "padding": "0px",
            "color": "white"
        },
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

$(document).ready(function () {

    $(window).keydown(function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
});

// Table Sorting
$('th').click(function (e) {
    if (e.target !== e.currentTarget) return;
    var table = $(this).parents('table').eq(0)
    var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
    this.asc = !this.asc
    if (!this.asc) { rows = rows.reverse() }
    for (var i = 0; i < rows.length; i++) { table.append(rows[i]) }
})

$('th label').click(function (e) {
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

function collapseMenu(action, ele) {
    if (localStorage.getItem('menuIsCollapsed') != 'true' || action == 'hide') {
        $('.is-menu').css('min-width', '54px');
        $('.menu').css('width', '54px');
        $('.menu-label').hide();
        $('.menu-list .icon').siblings('span').hide();
        $('.logo-text').hide();
        $('.menu-list .icon').addClass('is-size-5 mt-3 ml-1');
        $('.is-username-info').hide();
        $(ele).children('i').removeClass('fa-angle-left');
        $(ele).children('i').addClass('fa-angle-right');
        localStorage.setItem('menuIsCollapsed', true);
    } else {
        $('.is-menu').css('min-width', '180px');
        $('.menu').css('width', '180px');
        $('.menu-label').show();
        $('.menu-list .icon').siblings('span').show();
        $('.logo-text').show();
        $('.menu-list .icon').removeClass('is-size-5 mt-3 ml-1');
        $(ele).children('i').addClass('fa-angle-left');
        $(ele).children('i').removeClass('fa-angle-right');
        $('.is-username-info').show();
        localStorage.setItem('menuIsCollapsed', false);
    }
}

// Custom notification banner timeout
setTimeout(function () {
    $(".notification.status").slideUp(250);
}, 6000)