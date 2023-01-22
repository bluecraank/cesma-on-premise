var theme = localStorage.getItem('theme') ? localStorage.getItem('theme') : 'light';

function switchTheme(theme) {
    if (theme == 'dark') {
        $('#theme').attr('href', '/css/dark.min.css');
        $('#themeSwitch').val('dark');
    } else {
        $('#theme').attr('href', '/css/bulma.min.css');
        $('#themeSwitch').val('light');
    }
}

switchTheme(theme);