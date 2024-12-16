(function($) {
    $('#river-cruise-booking-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                $('#form-response').text(response.data).css('color', 'green');
            } else {
                $('#form-response').text(response.data).css('color', 'red');
            }
        });
    });
})(jQuery);
