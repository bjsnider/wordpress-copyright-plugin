(function($) {
	'use strict';

$(function() {
    $("p.buttonselector button").click(function() {
		// Prevent the reset button from resetting using the DB values.
        event.preventDefault();
        $('.resettable').each(function() {
        var $this = $(this),
		// Use the values loaded in the data-defaultvalue attribute.
        defaultValue = $this.data('defaultvalue');
        
        $this.val(defaultValue);
        });
        
    });
});
})(jQuery);
