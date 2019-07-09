<?php

/**
 * Creates the settings fields and sections
 *
 * Creates each of the settings sections with the callbacks
 * tied to that class. Also creates each of the settings fields
 * with callbacks also tied to that class.
 *
 * @since    0.1
 * @package    WPCopyright
 * @subpackage WPCopyright/php
 * @author Brandon Snider <brandonjsnider@gmail.com>
 * @copyright Copyright 2017 Brandon Snider
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License version 2 (GPLv2)
 */
class WPCopyright_Settings {

	/**
	 * The plugin's options.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array    $options    The plugin's options, saved in the options table.
	 */
	private $options;

	/**
	 * The default choices.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array    $defaults    The plugin's default copyright choices.
	 */
	private $defaults;

	/**
	 * The callbacks.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array    $callbacks    The callbacks object.
	 */
	private $callbacks;
	
	/**
	 * The authors.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array    $postTypes    The site's post types.
	 */
	private $postTypes;
	
	/**
	 * The authors.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      object    $authors    Object containing the site's authors.
	 */
	private $authors;

	/**
	 * Sets options and callbacks.
	 *
	 * Functions run from the Manager class.
	 *
	 * @since    0.1
	 * @param    array      $opt          The plugin's options.
	 * @param    array      $def          The default options.
	 * @param    object     $callbacks    The Callbacks object.
	 * @param    array      $types        The site's post types.
	 * @param    array      $authors      The site's authors.
	 */
    public function __construct($opt, $def, WPCopyright_Callbacks_Interface $callbacks, $types, $authors) {
        $this->options = $opt;
		$this->defaults = $def;
		$this->callbacks = $callbacks;
		$this->postTypes = $types;
		$this->authors = $authors;
    }
	
	/**
	 * Creates settings sections.
	 *
	 * @since    0.1
	 */
    public function add_settings_sections() {

        /**
         * WordPress function to create the settings sections.
         *
         * @var     object     $callbacks     The callbacks object.
         */
        add_settings_section('wpcopyright_copyright_list',
							 __('Copyright Options', 'wpcopyright'),
							 array($this->callbacks, 'wpcopyright_section_list_cb'),
							 'wpcopyright'
							);
        add_settings_section('wpcopyright_copyright_custom',
							 __('Customize the License Markup', 'wpcopyright'),
							 array($this->callbacks, 'wpcopyright_section_customize_cb'),
							 'wpcopyright'
							);

        /**
         * Scripts the creation of these sections
         *
         * Uses the $defaults static array param to create
         * a section per choice. This means if more choices
         * are added at some point, they will automatically
         * get their own settings sections.
         *
         * @var     array     $defaults     The default options.
         */
        foreach ($this->defaults as $option => $value) {
            add_settings_section("wpcopyright_copyright_${option}",
								 __($value['title'] . " Options", 'wpcopyright') ,
				                 array($this->callbacks, "wpcopyright_customize_cb") ,
				                 'wpcopyright'
				               );
        }
    }

	/**
	 * Creates the settings fields.
	 *
	 * @since    0.1
	 */
    public function add_settings_fields() {

        /**
         * WordPress function to create the settings fields.
         *
         * The foreach loop creates a field for each choice's
         * description and text fields.
         *
         * @var      object      $callbacks      The callbacks object.
         * @var      array       $defaults       The default options.
         */
        foreach ($this->defaults as $choice => $ar) {
            foreach ($ar as $param => $value) {
                
                if ($param !== 'title') {
                    add_settings_field("wpcopyright_options[${choice}][${param}]",
									   __(ucfirst($param) , 'wpcopyright'),
									   array($this->callbacks, "wpcopyright_textarea_cb"),
						               'wpcopyright', "wpcopyright_copyright_${choice}", array(// this array is passed to the callback as $args.
                                       'label_for' => "${choice}_${param}",
                                       'choice' => $choice,
                                       'param' => $param
                                      ));
				}
            }
        }

        /**
         * This block adds the field for the default choice.
         *
         * @var      object      $callbacks      The callbacks object.
         */
        add_settings_field('wpcopyright_copyright_choice',
						   __('Default Choice', 'wpcopyright'),
						   array($this->callbacks, 'wpcopyright_copyright_choice_cb'),
						   'wpcopyright',
						   'wpcopyright_copyright_list',
						   array(// this array is passed to the callback as $args.
                           'label_for' => "wpcopyright_copyright_choice",
                           'class' => "wpcopyright_choice"
                          ));

        /**
         * This block adds the field that applies the default choice to all posts
         * or deletes the key/value from all posts.
         *
         * @param      object      $callbacks      The callbacks object.
         */
        add_settings_field('wpcopyright_apply_remove',
						   __('Possible Actions', 'wpcopyright') ,
						   array($this->callbacks, 'wpcopyright_apply_remove_cb'),
						   'wpcopyright', 'wpcopyright_copyright_list',
						   array(// this array is passed to the callback as $args.
                           'label_for' => "wpcopyright_apply_remove",
                           'class' => "wpcopyright-apply-remove"
                          ));

        /**
         * This block adds the field that determines if the
         * copyright text appears after the post content.
         *
         * @var      object      $callbacks      The callbacks object.
         */
        add_settings_field('wpcopyright_append_post',
						   __('Display Choices', 'wpcopyright') ,
						   array($this->callbacks, 'wpcopyright_append_post_cb'),
						   'wpcopyright',
						   'wpcopyright_copyright_list',
						   array(// this array is passed to the callback as $args.
                           'label_for' => "wpcopyright_append_post",
                           'class' => "wpcopyright-append-post"
                          ));
    }

	/**
	 * Create the options page
	 *
	 * @since    0.1
	 */
    public function wpcopyright_options_page() {

        // Add menu page under Settings.
        add_submenu_page('options-general.php',
						 'Copyright',
						 __('Copyright Options', 'wpcopyright'),
						 'manage_options',
						 'wpcopyright',
						 array($this->callbacks, 'wpcopyright_options_page_html')
						);
    }

	/**
	 * Determines whether to append the copyright choice to the post.
	 *
	 * @since       0.1
	 * @return      string      The post with the copyright choice.
	 */
    public function wpcopyright_show_after_content() {

        /**
         * Hooked to the_content
         *
         * @var      array       $options        The wpcopyright options from the database.
         * @var      post        $post           The post object.
         * @var      string      $choice         The copyright choice.
         * @var      string      $copyright      The html and the text/description.
         */
        if ($this->options['wpcopyright_append_post'] === 'on') {
            global $post;
            
            if (is_single()) {
                $choice = get_post_meta($post->ID, '_wpcopyright_post_copyright_choice', true) ? get_post_meta($post->ID, '_wpcopyright_post_copyright_choice', true) : false;
                
                if ($choice) {
                    $copyright = sprintf(
                                         '<div class="copyright-notice">' . PHP_EOL . '<p title="%1$s">%2$s</p>' . PHP_EOL . "</div><!-- .copyright-notice -->" . PHP_EOL,
										 esc_attr($this->options[$choice]['description']),
                                         $this->options[$choice]['text']
                                       );
                    $post->post_content .= $copyright;
                }
            }
            return $post->post_content;
		}
    }

	/**
	 * Run the user preference on the database.
	 *
	 * @since    0.1
	 */
    public function wpcopyright_add_to_all() {

        /**
         * Checks $_POST for the key.
         *
         * Creates a new WP_Query object if the key isn't empty.
         *
         * @var      WP_Query      $wpcopyrightQuery      The query object.
         * @var      array         $options               The saved wpcopyright_options.
         * @var      array         $args                  The args sent to the query object.
         * @var      array         $post_ids              The complete list of post IDs.
         */
		if (!empty($_POST['wpcopyright_apply_remove'])) {
			$post_ids = array();
			$args = array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'fields'         => 'ids'
			);
			$query = new WP_Query($args);
	
			if (!empty ($query->posts))
				$post_ids = $query->posts; // just the post IDs
				
				if ($this->options['wpcopyright_copyright_choice'] !== 'none') {
							// Loop through the list of IDs.
							foreach ($post_ids as $post) {
							if ($_POST['wpcopyright_apply_remove'] === 'apply') {
								update_post_meta($post, '_wpcopyright_post_copyright_choice', $this->options['wpcopyright_copyright_choice']);
							} elseif ($_POST['wpcopyright_apply_remove'] === 'none') {
								delete_post_meta($post, '_wpcopyright_post_copyright_choice');
							}
						}
				} else {
					add_settings_error('wpcopyright_messages', 'wpcopyright_message', __("Error: You can't apply &quot;none&quot; as a choice.", 'wpcopyright') , 'error');
					return;
				}
		}

		/* These two blocks have had their functions heavily abstracted out to
		 * the private functions below due to the heavy duplication of actions.
		 */
		 
		// Begin authors block.
		
		if (!empty($_POST['wpcopyright_author_id']) &&  !empty($_POST['wpcopyright_author_choice']))
			$this->validateAndApply('author', $_POST['wpcopyright_author_id'], $_POST['wpcopyright_author_choice']);
		
		// Begin post types block.
		
		if (!empty($_POST['wpcopyright_post_type']) &&  !empty($_POST['wpcopyright_post_choice']))
			$this->validateAndApply('type', $_POST['wpcopyright_post_type'], $_POST['wpcopyright_post_choice']);
    }
	
	/**
	 * Execute the validate and apply functions.
	 *
	 * Abstracts the execution of the validate/apply functions
	 * so as to duplicate as few lines as possible.
	 *
	 * @since    0.1
	 * @param    string      $cat         The author/type selection.
     * @param    string      $opt         The author ID or the post type.
     * @param    string      $choice      The copyright choice.
	 */
    private function validateAndApply($cat, $opt, $choice) {
		
        /**
         * Run the validators and apply changes.
         * 
         * @var    array    $args    The args sent to the query object.
         */
        $args = array(
    			'post_type'      => 'any',
    			'posts_per_page' => -1,
    			'fields'         => 'ids'
    			);
		// Assign $opt to the $args based on the choice of author or post type.
    	if ($cat ==='author') {
    	    $args['author'] = $opt;
    	} elseif ($cat === 'type') {
    	    $args['post_type'] = $opt;
    	}
		// Validate the $choice and $opt using the private methods below.
    	$choice = $this->validateChoice($choice);
    	$opt = $this->validateArg($cat, $opt);
    	if ($opt && $choice) {
    		$this->applyChoice($args, $choice);
		// The following code is for troubleshooting purposes only.
    	} elseif (!$choice && !$posts) {
    	    add_settings_error('wpcopyright_messages', 'wpcopyright_message', __("Error: The form inputs failed to validate (both).", 'wpcopyright') , 'error');
			return;
    	} elseif (!$opt) {
    	    add_settings_error('wpcopyright_messages', 'wpcopyright_message', __("Error: The form inputs failed to validate (validateArg).", 'wpcopyright') , 'error');
			return;
    	} elseif (!$choice) {
    	    add_settings_error('wpcopyright_messages', 'wpcopyright_message', __("Error: The form inputs failed to validate (validateChoice).", 'wpcopyright') , 'error');
			return;
    	}
    }
    
	/**
	 * Validate the copyright choice.
	 *
	 * @since    0.1
	 * @param    string    $choice    The copyright choice.
	 */
    private function validateChoice($choice) {
		
        /**
         * Validate the copyright choice.
         *
         * @var    array    $defaults    The default copyright options.
         */
    	// Sanitize the input.
    	$choice = sanitize_text_field($choice);
    	// Validate data. Builds an array with all choices and "none" combined.
    	$defaults = array('none');
    	foreach ($this->defaults as $key => $value) {
    		$defaults[] = $key;
    	}
		// The only allowed value is one that matches the $defaults array.
    	if (in_array($choice, $defaults))
    		return $choice;
    		
    	return false;
    }
    
	/**
	 * Validate the authors or post types.
	 *
	 * @since    0.1
	 * @param    string     $cat     The author/type selection.
	 * @param    string     $arg     The author ID or the post type.
	 */
    private function validateArg($cat, $arg) {
		
        /**
         * Validate the authors or post types.
         * 
         * @var    array    $paramList     The authors or post types.
         */
    	// This could be the author or the post type.
    	if ($cat === 'author') {
    		$paramList = $this->authors;
    	} elseif ($cat === 'type') {
    		$paramList = $this->postTypes;
    	} else {
			add_settings_error('wpcopyright_messages', 'wpcopyright_message', __("Error: The value from this input was found to be incorrect.", 'wpcopyright') , 'error');
    		return false;
    	}
    	$arg = sanitize_text_field($arg);
    	// Validate data.
    	if (in_array($arg, $paramList))
    		return $arg;
    		
    	return false;
    }
    
	/**
	 * Run the user preference on the database.
	 *
	 * @since    0.1
	 * @param    array      $args       The options being used with WP_Query.
	 * @param    string     $choice     The copyright choice.
	 */
    private function applyChoice($args, $choice) {
    
        /**
         * Alter the postmeta options.
         *
         * @var    WP_Query     $query        The query object.
         * @var    array        $post_ids     The complete list of post IDs.
         */
    	$query = new WP_Query($args);
    
    	if (!empty ($query->posts)) {
			// Just the post IDs.
    		$post_ids = $query->posts;

        	foreach ($post_ids as $post) {
				// Uncomment the settings error lines to see the command being run with variables expanded.
        		if ($choice !== 'none') {
        			update_post_meta($post, '_wpcopyright_post_copyright_choice', $choice);
        			//add_settings_error( 'wpcopyright_messages', 'wpcopyright_message', __( "Ran the command: update_post_meta($post, '_wpcopyright_post_copyright_choice', $choice)", 'wpcopyright' ), 'updated' );
        		} else {
        			delete_post_meta($post, '_wpcopyright_post_copyright_choice');
        			//add_settings_error( 'wpcopyright_messages', 'wpcopyright_message', __( "Ran the command: delete_post_meta($post, '_wpcopyright_post_copyright_choice')", 'wpcopyright' ), 'updated' );
        		}
        	}
    	} else {
    	    add_settings_error('wpcopyright_messages', 'wpcopyright_message', __("Error: There are no posts to change.", 'wpcopyright') , 'error');
			return;
    	}
    }
}
