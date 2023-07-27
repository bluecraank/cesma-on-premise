function switchTheme() {
    let theme = localStorage.getItem('theme') ? localStorage.getItem('theme') : 'light';
    if (theme == 'dark') {
        $('#theme').attr('href', '/css/dark.min.css');
        document.documentElement.setAttribute('data-theme', 'dark');
        $('#themeSwitch').val('dark');
    } else {
        $('#theme').attr('href', '/css/bulma.min.css');
        document.documentElement.setAttribute('data-theme', 'light');
        $('#themeSwitch').val('light');
    }
}


switchTheme();