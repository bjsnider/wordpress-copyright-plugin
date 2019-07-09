<?php

/**
 * Provide an admin area view for the plugin
 *
 * @since    0.1
 * @package    WPCopyright
 * @subpackage WPCopyright/php
 * @author Brandon Snider <brandonjsnider@gmail.com>
 * @copyright Copyright 2017 Brandon Snider
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License version 2 (GPLv2)
 */
class WPCopyright_Admin_View {
    
    public function render() {
		
		// Security check.
		if (!current_user_can('manage_options')) {
			return;
		}
		
			?>
			<div class="wrap">
				<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
				<form action="options.php" method="post">
					<?php
					if (function_exists('wp_nonce_field')) {
					wp_nonce_field('wpcopyright_update_settings_', '');
					}
					// output security fields for the registered setting "wpcopyright"
					settings_fields('wpcopyright');
					// output setting sections and their fields
					// (sections are registered for "wpcopyright", each field is registered to a specific section)
					do_settings_sections('wpcopyright');
					?>
					<p class="buttonselector hide">
					<button type="reset" name="reset">Reset to Default Values</button>
					 <?php esc_html_e('This applies to the Description and Text fields only.', 'wpcopyright'); ?>
					</p>
					<?php // output save settings button
					submit_button('Save Settings');
					?>
				</form>
<?php
        }
}