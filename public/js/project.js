$(document).ready(function() {
    $('.js-like-project').on('click', function(e) {
        e.preventDefault();

        var link = $(e.currentTarget);
        link.toggleClass('far').toggleClass('fas');
        if (link.hasClass('far'))
        {
            $('.js-like-project-count').text(Number($('.js-like-project-count').text()) - 1);
        } else
        {
            $('.js-like-project-count').text(Number($('.js-like-project-count').text()) + 1);
        }

        $.ajax({
            method: 'POST',
            url: link.attr('href')
        }).done(function(data) {
            $('.js-like-project-count').html(data.hearts);
        });
    });
});