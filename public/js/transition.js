$( ".cta" ).on('click', function(e) {
    e.preventDefault();
    var link = $(e.currentTarget);
    $( ".transition").toggleClass( "anim-trans" );
    setTimeout(function () {
        window:location.replace(link.attr('href'));
    }, 1500);
});

