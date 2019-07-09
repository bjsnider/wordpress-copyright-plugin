<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks. Also maintains the unique identifier of this plugin
 * as well as the current version of the plugin.
 *
 * @since    0.1
 * @package    WPCopyright
 * @subpackage WPCopyright/php
 * @author Brandon Snider <brandonjsnider@gmail.com>
 * @copyright Copyright 2017 Brandon Snider
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License version 2 (GPLv2)
 */
class WPCopyright {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	* The options out of the database.
	*
	* @since    0.1
	* @access   protected
	* @var      array      $options      The plugin's options from the database.
	*/
    protected $options;

	/**
	 * The default choices.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      array    $defaults    The plugin's default copyright choices.
	 */
	protected $defaults;
	
	/**
	 * The authors.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      array    $authors    Object containing the site's authors.
	 */
	protected $authors = array();
	
	/**
	 * The post types.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      array    $types    Object containing the site's post types.
	 */
	protected $postTypes = array();
	
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1
	 */
	public function __construct($def) {
		$this->plugin_name = 'wpcopyright-plugin';
		$this->version = '0.1';
		$this->defaults = $def;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Require the following files that make up the plugin:
	 *
	 * - WPCopyright_Widget. Defines the widget.
	 * - WPCopyright_i18n. Defines internationalization functionality.
	 * - WPCopyright_Admin. Defines all hooks for the admin area.
	 * - WPCopyright_Settings. Defines the settings page.
	 * - WPCopyright_Shortcodes. Defines the shortcodes.
	 * - WPCopyright_Callbacks-Interface. Defines the callbacks interface.
	 * - WPCopyright_Callbacks. Defines the callbacks for the settings page.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		$classes = array(
			'admin',
			'i18n',
			'settings',
			'shortcodes',
			'callbacks-interface',
			'callbacks',
			'widget'
		);
		foreach ($classes as $class) {
			if (!class_exists($class) and !interface_exists($class)) {
				require_once plugin_dir_path(dirname(__FILE__)) . "php/class-wpcopyright-plugin-$class.php";
			}
		}

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1
	 * @access   private
	 */
	public function setup() {
		
		$this->set_options();
		$this->load_dependencies();
		
		// Initialize the translations object.
		$plugin_i18n = new WPCopyright_i18n();
		add_action('plugins_loaded', array($plugin_i18n, 'load_wpcopyright_textdomain'));
		
		// Initialize the admin object.
		$plugin_admin = new WPCopyright_Admin($this->get_plugin_name(), $this->get_version(), $this->get_defaults(), $this->get_options());
		add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
        add_action('add_meta_boxes', array($plugin_admin, 'add_meta_box'));
        add_action('save_post', array($plugin_admin, 'wpcopyright_save'));
        add_action('edit_post', array($plugin_admin, 'wpcopyright_remove'));
        add_action('quick_edit_custom_box', array($plugin_admin, 'display_quick_edit_custom'), 10, 2);
        add_action('bulk_edit_custom_box', array($plugin_admin, 'display_bulk_edit_custom'), 10, 2);
		add_action('wp_ajax_save_bulk_edit_wpcopyright', array($plugin_admin, 'save_bulk_edit_wpcopyright'));
        add_action('manage_pages_columns', array($plugin_admin, 'add_custom_admin_column'), 10, 1);
        add_action('manage_pages_custom_column', array($plugin_admin, 'manage_custom_admin_columns'), 10, 2);
        add_action('manage_posts_columns', array($plugin_admin, 'add_custom_admin_column'), 10, 1);
        add_action('manage_posts_custom_column', array($plugin_admin, 'manage_custom_admin_columns'), 10, 2);
		
		// Initialize the shortcodes object.
		$shortcodes = new WPCopyright_Shortcodes();
		
		// Add the two shortcodes.
	    add_shortcode('cp-years', array($shortcodes, 'wpcopyright_years_shortcode'));
	    add_shortcode('blog-title', array($shortcodes, 'wpcopyright_blogtitle_shortcode'));
		
		// Add the settings function.
		add_action('admin_init', array($this, 'register_wpcopyright_setting'));
		
		// Create the authors and post types arrays.
		$this->set_authors();
		$this->set_post_types();
		
		// Initialize the callbacks object, an instance of WPCopyright_Callbacks_Interface
		$callbacks = new WPCopyright_Callbacks($this->get_options(), $this->get_defaults(), $this->get_post_types(), $this->get_authors());
		
		// Initialize the settings object, passing the callbacks object.
        $copySettings = new WPCopyright_Settings($this->get_options(), $this->get_defaults(), $callbacks, $this->get_post_types(), $this->get_authors());
        add_action('admin_init', array($copySettings, 'add_settings_sections'));
        add_action('admin_init', array($copySettings, 'add_settings_fields'));
        add_action('admin_menu', array($copySettings, 'wpcopyright_options_page'));
        add_filter('the_content', array($copySettings, 'wpcopyright_show_after_content'));
		add_action('admin_menu', array($copySettings, 'wpcopyright_add_to_all'));

		// Initialize the widgets object.
        $widget = new WPCopyright_Widget($this->get_options(), $this->get_defaults());
        add_action('widgets_init', array($this, 'register_wpcopyright_widget'));
    }

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1
	 * @access    protected
	 * @return    string        The name of the plugin.
	 */
	protected function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1
	 * @access    protected
	 * @return    string        The version number of the plugin.
	 */
	protected function get_version() {
		return $this->version;
	}

    /**
	 * Sets the values in the $options param
	 *
	 * Queries the database for the saved settings
	 * and assigns them to the $options property.
	 *
	 * @since     0.1
	 * @access    protected
	 */
	protected function set_options() {

		/**
		 * Assigns the settings to the property.
		 * 
		 * @since    0.1
		 * @var      array     $options     Saved wpcopyright settings.
		 */
	    $this->options = get_option('wpcopyright_options');
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1
	 * @access    protected
	 * @return    string       The version number of the plugin.
	 */
	protected function get_options() {
		
		/**
		 * Returns the settings.
		 * 
		 * @since    0.1
		 * @var      array     $options     Saved wpcopyright settings.
		 */
		return $this->options;
	}
	
	/**
	 * Retrieve the defaults.
	 *
	 * @since     0.1
	 * @access    protected
	 * @return    array        The defaults array.
	 */
	protected function get_defaults() {
		
		/**
		 * Returns the defaults.
		 * 
		 * @since    0.1
		 * @var      array     $defaults     Default settings array.
		 */
		return $this->defaults;
	}
	
	/**
	 * Register the widget.
	 *
	 * @since     0.1
	 */
	public function register_wpcopyright_widget() {
		register_widget('Wpcopyright_Widget');
	}

	/**
	 * Registers the wpcopyright_options setting.
	 *
	 * @since    0.1
	 */
    public function register_wpcopyright_setting() {
        register_setting('wpcopyright', 'wpcopyright_options');
    }

	/**
	 * Set the site's post types.
	 *
	 * @since     0.1
	 * @access    protected
	 */
    protected function set_post_types() {

		/**
		 * Sets the $postTypes array.
		 * 
		 * @since     0.1
		 * @var       array      $args      The options sent to get_post_types().
		 * @var       object     $types     The post types object.
		 */
        $args = array(
    	'public' => true
    	);
	    $types = get_post_types($args, 'objects');
	    foreach ($types as $type) {
	        $this->postTypes[$type->label] = $type->name;
	    }
    }
    
	/**
	 * Set the site's authors.
	 *
	 * @since     0.1
	 * @access    protected
	 */
    protected function set_authors() {

		/**
		 * Sets the $authors array.
		 * 
		 * @since     0.1
		 * @var       array      $args             The options sent to get_post_types().
		 * @var       object     $wp_user_query    The WP_User_Query object.
		 * @var       object     $authors          The authors object.
		 */
         // prepare arguments
        $args  = array(
        // Search only for roles capable of creating posts.
        'role__in' => array('Contributor', 'Author', 'Editor', 'Administrator'),
        // Order results by display_name.
        'orderby' => 'post_count'
        );
        // Create the WP_User_Query object
        $wp_user_query = new WP_User_Query($args);
        // Get the results
        $authors = $wp_user_query->get_results();
        if (!is_null($authors)) {
            // Restrict the array to the ID. We don't use anything else.
            foreach ($authors as $author) {
                $this->authors[] = $author->ID;
            }
        }

    }
	
    /**
	 * Get the site's post types.
	 *
	 * @since     0.1
	 * @access    protected
	 * @return    array        The post types, as [label] => name.
	 */
    protected function get_post_types() {
		
		/**
		 * Gets the $postTypes array.
		 * 
		 * @since    0.1
		 * @var      array    $postTypes    The post types.
		 */
        return $this->postTypes;
    }
	
    /**
	 * Get the site's authors.
	 *
	 * @since     0.1
	 * @access    protected
	 * @return    array        The authors, by ID.
	 */
    protected function get_authors() {
		
		/**
		 * Sets the $authors array.
		 * 
		 * @since    0.1
		 * @var      array     $authors     The authors' IDs as the array values.
		 */
        return $this->authors;
    }
}
