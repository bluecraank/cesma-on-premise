$(document).ready(function () {
    // MultiSelect
    $('#switch-select-ms').multiSelect({
        selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
        selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
    });

    $('#switch-select-ms-2').multiSelect({
        selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
        selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
    });

    $('#vlan-select-ms').multiSelect({
        selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
        selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
    });

    $('#vlan-select-ms-2').multiSelect({
        selectableHeader: "<div class='content has-text-centered'>Verfügbar</div>",
        selectionHeader: "<div class='content has-text-centered'>Ausgewählt</div>"
    });
});