/*
 * Post Bulk Edit Script
 * Hooks into the inline post editor functionality to extend it to our custom metadata
 */

(function($) {

    // Prepopulating our quick-edit post info
    var $inline_editor = inlineEditPost.edit;
    inlineEditPost.edit = function(id) {

        // Call old copy 
        $inline_editor.apply( this, arguments);

        // Our custom functionality below
        var post_id = 0;
        if( typeof(id) == 'object') {
            post_id = parseInt(this.getId(id));
        }

        // Check if there is a post.
        if(post_id !== 0) {

            // Capture the row.
            $row = $('#edit-' + post_id);

            // Capture the copyright options code block.
            $wpcopyright_options = $('#wpcopyright_options_' + post_id);
            // Capture the copyright choice.
            wpcopyright_choice = $wpcopyright_options.data('choice');
            select = $row.find('select#wpcopyright_choice');
            select.find('option').each(function() {
                var choice = $(this);
                if (choice.val() === wpcopyright_choice) {
                choice.attr('selected', true);
                }
            });
        }
    };

$( document ).on( 'click', '#bulk_edit', function() {
        // Capture the nonce.
        var $nonce = $('#wpcopyright_bulkedit_').val();
		// define the bulk edit row
		var $bulk_row = $( '#bulk-edit' );

		// get the selected post ids that are being edited
		var $post_ids = new Array();
		$bulk_row.find( '#bulk-titles' ).children().each( function() {
			$post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
		});

		// get the data
		var $copyright_choice = $bulk_row.find( 'select#wpcopyright_choice' ).val();

		// save the data
		$.ajax({
			url: ajaxurl, // this is a variable that WordPress has already defined for us
			type: 'POST',
			async: false,
			cache: false,
			data: {
				action: 'save_bulk_edit_wpcopyright', // this is the name of our WP AJAX function that we'll set up next
				post_ids: $post_ids, // and these are the 3 parameters we're passing to our function
				wpcopyright_choice: $copyright_choice,
				wpcopyright_bulkedit_: $nonce
			}
		});
	});
})(jQuery);