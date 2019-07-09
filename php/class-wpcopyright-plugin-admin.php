<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and admin methods.
 * 
 *
 * @since    0.1
 * @package    WPCopyright
 * @subpackage WPCopyright/php
 * @author Brandon Snider <brandonjsnider@gmail.com>
 * @copyright Copyright 2017 Brandon Snider
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License version 2 (GPLv2)
 */
class WPCopyright_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $version     The current version of this plugin.
	 */
	private $version;

	/**
	 * The default choices.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      array    $defaults    The plugin's default copyright choices.
	 */
	private $defaults;
	
	/**
	* The options out of the database.
	*
	* @since    0.1
	* @access   private
	* @var      array      $options      The plugin's options from the database.
	*/
    private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1
	 * @param    string    $plugin_name     The name of this plugin.
	 * @param    string    $version         The version of this plugin.
	 */
	public function __construct($plugin_name, $version, $def, $opt) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->defaults = $def;
		$this->options = $opt;
	}

    /**
     * Adds the stylesheet where necessary
     *
     * The stylesheet is only loaded on the plugin's settings page,
     * the post edit page, and the new post page.
     *
     * @since    0.1
     * @param    string     $hook     The codename of the current page.
     */
    public function enqueue_scripts($hook) {

        /**
         * Runs wp_enqueue_style script
         *
         * @var      array      $cssPages      The allowed pages on which the stylesheet will appear.
         * @var      string     $settingsPage  The wpcopyright settings page codename.
         * @var      string     $editpage      The hook name for the edit posts page.
         * @var      array      $combined      The combination of $cssPages and $editpage.
         */
		$editpage = 'edit.php';
        $settingsPage = 'settings_page_wpcopyright';
        $cssPages = array(
            'post.php',
            'post-new.php',
            $settingsPage
        );
        // Initial location check.
        $combined = $cssPages;
        $combined[] = $editpage;
        if (!in_array($hook, $combined))
            return;

        if (in_array($hook, $cssPages, true))
			wp_enqueue_style($this->plugin_name, plugins_url('css/wpcopyright-plugin-admin.css', dirname(__FILE__)), array() , $this->version, 'all');

        if ($hook === $editpage) {
			// This script must be enqueued at the end of the page, this the last option is "true".
			wp_enqueue_script($this->plugin_name, plugins_url('js/wpcopyright-plugin-quickeditbox.js', dirname(__FILE__)), array('jquery'), $this->version, true);
		} elseif ($hook === $settingsPage) {
			wp_enqueue_script($this->plugin_name, plugins_url('js/wpcopyright-plugin-admin.js', dirname(__FILE__)), array('jquery'), $this->version, false);
        }
	}

    /**
     * Adds the meta box
     *
     * @since    1.0.
     */
    public function add_meta_box() {
		$postTypes = get_post_types();
        add_meta_box('wpcopyright_copyright_choice_meta_box', 'Copyright Choices', array(
            $this,
            'wpcopyright_render_meta_box'
        ) , $postTypes, 'side', 'core');
    }

    /**
     * Saves the choice in the database.
     *
     * This function runs update_post_meta with the key and the value
     * attached to the post's ID.
     *
     * @since    0.1
     * @param    int     $post_id     The post's ID.
     */
    public function wpcopyright_save($post_id) {
        
        if (!isset($_POST['wpcopyright_meta_box_']) &&
        !isset($_POST['wpcopyright_quickedit_']) &&
        !isset($_POST['wpcopyright_bulkedit_'])) {
            return $post_id;
        }
        
        if (!wp_verify_nonce($_POST['wpcopyright_meta_box_'], basename(__FILE__)) &&
        !wp_verify_nonce($_POST['wpcopyright_quickedit_'], basename(__FILE__)) &&
        !wp_verify_nonce($_POST['wpcopyright_bulkedit_'], basename(__FILE__))) {
            return $post_id;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
        
        if (array_key_exists('wpcopyright_choice', $_POST) && !$this->validate_choice($_POST['wpcopyright_choice'])) {
            return $post_id;
        }
        
        if (!in_array($_POST['wpcopyright_choice'], array('none'))) {
            update_post_meta($post_id, '_wpcopyright_post_copyright_choice', $_POST['wpcopyright_choice']);
        } elseif ($_POST['wpcopyright_choice'] === "none") {
            delete_post_meta($post_id, '_wpcopyright_post_copyright_choice');
        }
    }

    /**
     * Removes the choice in the database.
     *
     * This function runs delete_post_meta with the key
     * attached to the post's ID. This will be hooked to "edit post",
     * since it makes no sense to provide for removing something that
     * doesn't exist yet.
     *
     * @since      0.1
     * @param      int     $post_id     The post's ID.
     */
    public function wpcopyright_remove($post_id) {
        
        if (!isset($_POST['wpcopyright_meta_box_']) &&
        !isset($_POST['wpcopyright_quickedit_']) &&
        !isset($_POST['wpcopyright_bulkedit_'])) {
            return $post_id;
        }

        if (!wp_verify_nonce($_POST['wpcopyright_meta_box_'], basename(__FILE__)) &&
        !wp_verify_nonce($_POST['wpcopyright_quickedit_'], basename(__FILE__))) {
            return $post_id;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
        
        if (array_key_exists('wpcopyright_choice', $_POST) &&
        in_array($_POST['wpcopyright_choice'], array("none", "remove"))) {
            delete_post_meta($post_id, '_wpcopyright_post_copyright_choice');
        }
    }

    /**
     * Creates the meta box.
     *
     * Pulls in the html include template after defining the string
     * that appears inside.
     *
     * @since      0.1
     * @param      post     $post     Object containing the post's attributes.
     */
    public function wpcopyright_render_meta_box($post) {

        /**
         * Interior of the meta box
         *
         * @var      string      $default       Checks the database for a default choice, or sets 'none'.
         * @var      string      $value         Checks the database for a choice.
         * @var      string      $exists        Echoes the html around the choice, if one exists.
         * @var      string      $optionStr     The html list of possible choices.
         */
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field(basename(__FILE__) , 'wpcopyright_meta_box_');
        }
        $default = get_post_meta($post->ID, '_wpcopyright_post_copyright_choice', true);
        
        if ($default) {
            printf(__('<p>This post already has a copyright notice: </p><p class="wpcopyright-exists">%s</p><p>You can remove this notice by selecting "None" below.</p>', 'wpcopyright'),
                    $this->defaults[$default]['title']
                    );
        } else {
            $default = !empty($this->options['wpcopyright_copyright_choice']) ? $this->options['wpcopyright_copyright_choice'] : 'none';
        }
		$none = __('None', 'wpcopyright');
		$noneck = checked($default, 'none', false);
        $optionStr = <<<TOPSTR

	<div id="wpcopyright-meta-box">
		<fieldset>
			<legend class="screen-reader-text">Copyright Choices</legend>
			<input type="radio" name="wpcopyright_choice" id="copyright_choice_none" value="none"$noneck>
			<label for="copyright_choice_none">$none</label>
TOPSTR;
        foreach ($this->defaults as $choice => $opt) {
			$checked = checked($default, $choice, false);
			$title = esc_html($this->defaults[$choice]['title']);
$optionStr .= <<<OPTIONSTR

				<br><input type="radio" name="wpcopyright_choice" id="copyright_choice_$choice" value="$choice"$checked>
				<label for="copyright_choice_$choice">$title</label>
		
OPTIONSTR;
        }
		$optionStr .= "</fieldset>" . PHP_EOL . "</div>" . PHP_EOL;
		echo $optionStr;
    }
	
	/** Add a custom column to the quickedit screen.
	 *
	 * Shows a column on the quickedit screen called
	 * "Copyright Options".
	 *
	 * @since    0.1
	 * @param    array    $columns    The existing columns.
	 */
    public function add_custom_admin_column($columns) {
       
	   /**
	    * Combines the arrays.
	    *
	    * Folds the new column into the existing columns.
	    * 
	    * @var    array    $new_column    The column.
	    */
        $new_column['wpcopyright_options'] = 'Copyright Notice';
        return array_merge($columns, $new_column);
    }
    
	/** Show the copyright value.
	 *
	 * Shows the post's copyright value in the column.
	 *
	 * @since    0.1
	 * @param    string    $column_name   The existing columns.
	 * @param    int       $post_id       The post ID.
	 */
    public function manage_custom_admin_columns($column_name, $post_id) {
    
	   /**
	    * Markup that displays the choice.
	    *
	    * Displays the title and the choice using some markup.
	    * 
	    * @var    string    $value    The copyright choice value.
	    * @var    string    $title    The title.
	    */
        $value = !empty(get_post_meta($post_id, '_wpcopyright_post_copyright_choice', true)) ?
        get_post_meta($post_id, '_wpcopyright_post_copyright_choice', true) : 'none';
        if ($value !== 'none') {
            $title = $this->defaults[$value]['title'];
        } else {
            $title = 'None';
        }
        $html = '<div id="wpcopyright_options_' . $post_id . '" data-choice="' . $value . '">' . $title . "</div>" . PHP_EOL;

        echo $html;
    }

	/** Show the copyright choices.
	 *
	 * Shows the post's copyright choices in the quick edit screen.
	 *
	 * @since    0.1
	 * @param    string     $column     The wpcopyright column.
	 */
    public function display_quick_edit_custom($column) {
		
		/**
	    * Markup that displays the choice.
	    *
	    * Displays the title and the choice using some markup.
	    * 
	    * @var    string     $nonce     The name of the nonce.
	    * @var    string     $noneStr   This is added to the html just after the opening "select" element.
	    */
		$nonce = 'wpcopyright_quickedit_';
        $noneStr = '<option value="none">' . __('None', 'wpcopyright') . '</option>';
		// This is the abstracted function which creates the edit box markup.
        $this->editbox($column, $nonce, $noneStr);
    }

	/** Show the copyright choices.
	 *
	 * Shows the post's copyright choices in the quick edit screen.
	 *
	 * @since    0.1
	 * @param    string     $column     The wpcopyright column.
	 */
    public function display_bulk_edit_custom($column) {
		
		/**
	    * Markup that displays the choice.
	    *
	    * Displays the title and the choice using some markup.
	    * 
	    * @var    string     $nonce     The name of the nonce.
	    * @var    string     $noneStr   This is added to the html just after the opening "select" element.
	    */
		$nonce = 'wpcopyright_bulkedit_';
        $noneStr = '<option value="" selected>' . __('— No Change —', 'wpcopyright') . '</option>' . PHP_EOL .
			'<option value="none">' . __('None', 'wpcopyright') . '</option>' . PHP_EOL;
		// This is the abstracted function which creates the edit box markup.
		$this->editbox($column, $nonce, $noneStr);
    }
	
    /** Show the copyright choices.
	 *
	 * Shows the post's copyright choices in the quick edit screen.
	 *
	 * @since    0.1
	 * @param    string    $column     The wpcopyright column.
	 * @param    string    $nonce      The nonce.
	 * @param    string    $noneStr    The "None" options.
	 */
	private function editBox($column, $nonce, $noneStr) {
	    // Add the nonce.
	    if (function_exists('wp_nonce_field')) {
            wp_nonce_field(basename(__FILE__) , $nonce);
        }
        /**
	    * Markup that displays the choices in the form.
	    * 
	    * @var     string    $optionStr    The form markup.
	    */
        if($column === 'wpcopyright_options') {
			// I'm using a lot of line endings because I want the code readable in the source.
            $optionStr = '<fieldset class="inline-edit-col-right">' . PHP_EOL . '<div class="inline-edit-col">' . PHP_EOL .
            '<label class="inline-edit-wpcopyright">' . PHP_EOL .
            '<span class="title">' . __('Copyright', 'wpcopyright') . "</span>" . PHP_EOL . "<br>" . PHP_EOL .
            '<select name="wpcopyright_choice" id="wpcopyright_choice">' . PHP_EOL .
            $noneStr;
            foreach ($this->defaults as $choice => $opt) {
                $optionStr.= '<option value="' . $choice . '">' . PHP_EOL .
                esc_html($this->defaults[$choice]['title']) . PHP_EOL . "</option>" .
                PHP_EOL . "<br>";
            }
    		$optionStr .=  PHP_EOL . "</select>" . PHP_EOL . "</label>" . PHP_EOL . "</div>" . PHP_EOL . "</fieldset>" . PHP_EOL;
    		echo $optionStr;
        }
	}
	
	/** Saves the bulk edit screen choice.
	 *
	 * Saves the choice, including the "none" option, from data posted using
	 * jquery. This method is a version of the save/remove methods above.
	 *
	 * @since    0.1
	 */
	public function save_bulk_edit_wpcopyright() {
		
		/**
	    * Markup that displays the choice.
	    *
	    * Displays the title and the choice using some markup.
	    * 
	    * @var    array    $post_ids    The post IDs.
	    */
		$post_ids = (isset( $_POST[ 'post_ids' ]) && !empty($_POST[ 'post_ids' ])) ? $_POST['post_ids'] : array();
        foreach ($post_ids as $post_id) {
            // This is a data validation step -- 
            // just making absolutely sure these really are post IDs.
            if (ctype_digit($post_id))
                $this->wpcopyright_save($post_id);
        }
	}
	
	/** Validate the choice string.
	 *
	 * Checks the choice string against the possible values
	 * in the options array. This ensures that the value must
	 * be acceptable before going into the database.
	 *
	 * @since    0.1
	 * @param    string    $choice    The copyright choice.
	 * @return   boolean
	 */
    private function validate_choice($choice) {

		/**
	    * Markup that displays the choice.
	    *
	    * Displays the title and the choice using some markup.
	    * 
	    * @var    array     $valid     The options plus 'none'.
	    */
		$valid = $this->options;
        $valid['none'] = '';
        $choice = sanitize_key($choice);
		if (!array_key_exists($choice, $valid)) {
			return false;
		}
			return true;
    }
}
