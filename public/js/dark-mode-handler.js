$(document).ready(function() {
    const navbar = $('.main-header.navbar');
    const darkModeWidget = $('#dark-mode-switch');

    function updateNavbar() {
        if ($('body').hasClass('dark-mode')) {
            navbar.removeClass('navbar-light').addClass('navbar-dark');
        } else {
            navbar.removeClass('navbar-dark').addClass('navbar-light');
        }
    }

    darkModeWidget.on('click', function() {
        setTimeout(updateNavbar, 50);
    });

    setInterval(updateNavbar, 200);

    updateNavbar();
});
