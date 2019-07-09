<?php

/**
 *
 * @link              https://github.com/bjsnider/wordpress-copyright-plugin
 * @since             0.1
 * @package           WPCopyright
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Copyright Manager
 * Plugin URI:        http://example.com/wpcopyright-plugin-uri/
 * Description:       Displays custom per-post copyright declarations.
 * Version:           0.1
 * Author:            Brandon Snider
 * Author URI:        https://github.com/bjsnider
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpcopyright-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

new WPCopyright_Init();

class WPCopyright_Init {

	/**
	 * The default choices.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array        $defaults    The plugin's default copyright choices.
	 */
	private $defaults = array(
       'wpcopyright_allrights' => array('title' => 'All Rights Reserved', 'description' => 'Full advantage of all protections offered by copyright laws. This declaration is no longer necessary but still widely used. It is applied by default in most places.', 'text' => 'Copyright &copy; [cp-years] [blog-title] All Rights Reserved.'),
       'wpcopyright_attribution' => array('title' => 'CC Attribution 4.0', 'description' => 'This license lets others distribute, remix, tweak, and build upon your work, even commercially, as long as they credit you for the original creation. This is the most accommodating of licenses offered. Recommended for maximum dissemination and use of licensed materials. Legal code: https://creativecommons.org/licenses/by/4.0/legalcode', 'text' => '<a rel="license" href="https://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://licensebuttons.net/l/by/4.0/88x31.png"></a><br>[cp-years] [blog-title] Except where otherwise noted, this work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0</a>.'),
       'wpcopyright_attribution_sharealike' => array('title' => 'CC Attribution-ShareAlike 4.0', 'description' => 'This license lets others remix, tweak, and build upon your work even for commercial purposes, as long as they credit you and license their new creations under the identical terms. This license is often compared to “copyleft” free and open source software licenses. All new works based on yours will carry the same license, so any derivatives will also allow commercial use. Legal code: https://creativecommons.org/licenses/by-sa/4.0/legalcode', 'text' => '<a rel="license" href="https://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://licensebuttons.net/l/by-sa/4.0/88x31.png"></a><br>[cp-years] [blog-title] Except where otherwise noted, this work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0</a>.'),
       'wpcopyright_attribution_noderivs' => array('title' => 'CC Attribution-NoDerivatives 4.0', 'description' => 'This license allows for redistribution, commercial and non-commercial, as long as it is passed along unchanged and in whole, with credit to you. Legal code: https://creativecommons.org/licenses/by-nd/4.0/legalcode', 'text' => '<a rel="license" href="https://creativecommons.org/licenses/by-nd/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://licensebuttons.net/l/by-nd/4.0/88x31.png"></a><br>[cp-years] [blog-title] is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-nd/4.0/">Creative Commons Attribution-NoDerivatives 4.0</a>.'),
       'wpcopyright_attribution_noncommercial' => array('title' => 'CC Attribution-NonCommercial 4.0', 'description' => 'This license lets others remix, tweak, and build upon your work non-commercially, and although their new works must also acknowledge you and be non-commercial, they don’t have to license their derivative works on the same terms. Legal code: https://creativecommons.org/licenses/by-nc/4.0/legalcode', 'text' => '<a rel="license" href="https://creativecommons.org/licenses/by-nc/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://licensebuttons.net/l/by-nc/4.0/88x31.png"></a><br>[cp-years] [blog-title] Except where otherwise noted, this work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-nc/4.0/">Creative Commons Attribution-NonCommercial 4.0</a>.'),
       'wpcopyright_attribution_noncommercial_sharealike' => array('title' => 'CC Attribution-NonCommercial-ShareAlike 4.0', 'description' => 'This license lets others remix, tweak, and build upon your work non-commercially, as long as they credit you and license their new creations under the identical terms. Legal code: https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode', 'text' => '<a rel="license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://licensebuttons.net/l/by-nc-sa/4.0/88x31.png"></a><br>[cp-years] [blog-title] Except where otherwise noted, this work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-nc-sa/4.0/">Creative Commons Attribution-NonCommercial-ShareAlike 4.0</a>.'),
       'wpcopyright_attribution_noncommercial_noderivs' => array('title' => 'CC Attribution-NonCommercial-NoDerivatives 4.0', 'description' => 'This license is the most restrictive of our six main licenses, only allowing others to download your works and share them with others as long as they credit you, but they can’t change them in any way or use them commercially. Legal code: https://creativecommons.org/licenses/by-nc-nd/4.0/legalcode', 'text' => '<a rel="license" href="https://creativecommons.org/licenses/by-nc-nd/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://licensebuttons.net/l/by-nc-nd/4.0/88x31.png"></a><br>[cp-years] [blog-title] Except where otherwise noted, this work is licensed under a <a rel="license" href="https://creativecommons.org/licenses/by-nc-nd/4.0/">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0</a>.'),
       'wpcopyright_norights' => array('title' => 'No Rights Reserved', 'description' => 'Waive all copyrights and related or neighboring rights that you may have in all jurisdictions worldwide, such as your moral rights (to the extent waivable), your publicity or privacy rights, rights you have protecting against unfair competition, and database rights and rights protecting the extraction, dissemination and reuse of data. Legal code: https://creativecommons.org/publicdomain/zero/1.0/legalcode', 'text' => '<a rel="license" href="https://creativecommons.org/publicdomain/zero/1.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://licensebuttons.net/p/zero/1.0/88x31.png"></a><br>[blog-title] has dedicated this work to the public domain by waiving all rights to the work worldwide under copyright law, including all related and neighboring rights, to the extent allowed by law.'),
       'wpcopyright_reprinted' => array('title' => 'Reprinted by Permission', 'description' => 'This site asserts no rights over the material. All rights revert to the original rightsholder.', 'text' => '[cp-years] [blog-title] This article was reprinted by permission. All rights belong to the original rightsholder.'),
       'wpcopyright_custom' => array('title' => 'Custom Choice', 'description' => '', 'text' => '')
        );
    /**
	 * Initialize the plugin.
	 *
	 * Execute the function which runs the plugin.
	 *
	 * @since    0.1
	 */
     public function __construct() {
		/* I'm waiting to load the controller until everything else is done in order
		 * to load all custom post types. */
         add_action('wp_loaded', array($this, 'run_wpcopyright_plugin'));
     }
    /**
	 * Activation callback.
	 *
	 * @since    0.1
	 */
    public function activate_wpcopyright_plugin() {
    		// Additional defaults.
    		$otherdefaults = array(
    		'wpcopyright_default_choice' => 'none'
    		, 'wpcopyright_apply_remove' => ''
    		, 'wpcopyright_append_post' => ''
    		);
    		// Combine the two into one array.
    		$options = array_merge($this->defaults, $otherdefaults);
    		// Check if the options already exist, create if they don't.
    		if (!get_option('wpcopyright_options')) {
    			update_option('wpcopyright_options', $options);
    		}
    }
    
    /**
     * The code that runs during plugin deactivation.
     * 
     * @since 0.1
     */
    public function deactivate_wpcopyright_plugin() {
    	// Nothing to do for now.
    }
    
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     * 
     * @since 0.1
     */
    public function wpcopyright_uninstall() {
    
    if (!defined('WP_UNINSTALL_PLUGIN'))
    	exit;
    
    /*
     * Remove traces left behind in the database by the plugin.
     *
     * @since 0.1
     */
    delete_option('wpcopyright_options');
    delete_option('widget_wpcopyright_widget');
    }
    
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    0.1
     */
    public function run_wpcopyright_plugin() {

        register_activation_hook(__FILE__, array($this, 'activate_wpcopyright_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_wpcopyright_plugin'));
        register_uninstall_hook(__FILE__, array($this, 'wpcopyright_uninstall'));
        /**
         * The code that runs during plugin activation.
         */
        require_once plugin_dir_path(__FILE__) . 'php/class-wpcopyright-plugin.php';
    	$plugin = new WPCopyright($this->defaults);
    	$plugin->setup();
    }
}
