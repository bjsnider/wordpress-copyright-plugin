<?php

/**
 * Creates the callbacks.
 *
 * Defines the callbacks to be used on the settings page.
 *
 * @since    0.1
 * @package    WPCopyright
 * @subpackage WPCopyright/php
 * @author Brandon Snider <brandonjsnider@gmail.com>
 * @copyright Copyright 2017 Brandon Snider
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License version 2 (GPLv2)
 */
class WPCopyright_Callbacks implements WPCopyright_Callbacks_Interface {

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
	 * @var      object    $authors     Object containing the site's authors.
	 */
	private $authors;

	/**
	 * Sets options and callbacks.
	 *
	 * Functions run from the Manager class.
	 *
	 * @since    0.1
	 * @param    array     $opt         The plugin's options.
	 * @param    array     $def         The default options.
	 * @param    array     $types       The site's post types.
	 * @param    array     $authors     The site's authors.
	 */
    public function __construct($opt, $def, $types, $authors) {
        $this->options = $opt;
		$this->defaults = $def;
		$this->postTypes = $types;
		$this->authors = $authors;
    }
    
	/**
	 * (non-PHPdoc)
	 * @see WPCopyright_Callbacks_Interface::wpcopyright_section_customize_cb()
	 */
    public function wpcopyright_section_customize_cb($args) {
    // Just echoes escaped HTML.
?>
    <p id="<?php
        echo esc_attr($args['id']); ?>">
    <?php
        esc_html_e('You can change the description, which will appear in the title attribute,
                          and as a tooltip when hovered, and the text, which will be displayed below the post.
                          You may use the following shortcodes: [blog-title], which expands to whatever you have named your blog,
                           and [cp-years], which expands to the year the post was published up to the present, eg. 2001-2017.', 'wpcopyright'); ?>

    </p>

    <?php
    }
    
	/**
	 * (non-PHPdoc)
	 * @see WPCopyright_Callbacks_Interface::wpcopyright_customize_cb()
	 */
    public function wpcopyright_customize_cb($args) {

?>
    <p id="<?php // Echo an empty paragraph for now.
        echo esc_attr($args['id']); ?>">
        <?php
		if ($args['id'] === 'wpcopyright_copyright_wpcopyright_custom') {
            _e('As a custom choice, you can declare anything you want, including,
            but not limited to, a license not in this list. There is no guarantee 
            what you write here will survive a hypothetical court case. You may want to look at the licenses listed on
            the Software Package Data Exchange <a href="https://spdx.org/licenses/">list</a>, or the GNU.org license 
            <a href="https://www.gnu.org/licenses/license-list.en.html">list.</a>', 'wpcopyright');
        }
        ?>

    </p>

    <?php
    }
    
	/**
	 * (non-PHPdoc)
	 * @see WPCopyright_Callbacks_Interface::wpcopyright_append_post_cb()
	 */
    public function wpcopyright_append_post_cb($args) {
        // Echo the HTML that provides the selection checkbox.
?>
    <p class="description">
    <?php
        esc_html_e('You can choose to display the copyright notice
                          at the end of each post.', 'wpcopyright'); ?>

    </p>
    <span for="<?php
        echo esc_attr($args['class']); ?>"><?php
        esc_html_e('Display at the end of the post?', 'wpcopyright'); ?></span>
    <input type="checkbox" name="wpcopyright_options[<?php
        echo esc_attr($args['label_for']); ?>]"
    class="<?php
        echo esc_attr($args['class']); ?>"
    id="<?php
        echo esc_attr($args['label_for']); ?>"<?php
        checked($this->options[$args['label_for']], 'on'); ?>>

    <?php
    }
    
	/**
	 * (non-PHPdoc)
	 * @see WPCopyright_Callbacks_Interface::wpcopyright_section_list_cb()
	 */
    public function wpcopyright_section_list_cb($args) {

?>
    <p id="<?php
        echo esc_attr($args['id']); ?>">
    <?php
        esc_html_e('List of Copyright Options.', 'wpcopyright'); ?>

    </p>

    <?php
    }
    
	/**
	 * (non-PHPdoc)
	 * @see WPCopyright_Callbacks_Interface::wpcopyright_copyright_choice_cb()
	 */
    public function wpcopyright_copyright_choice_cb($args) {

        /*
         * Assign None as the default choice.
         *
         * @var     string     $checked     The default value.
         */
        $checked = $this->options[$args['label_for']];
?>

 <select id="<?php
        echo esc_attr($args['label_for']); ?>"
 name="<?php
        echo esc_attr('wpcopyright_options[' . $args['label_for']); ?>]">
	<option value="<?php echo esc_attr('none', 'wpcopyright'); ?>"<?php selected($checked, 'none') ?>>
	    <?php // Translators: the other options have technical names and therefore must be represented in English.
        esc_html_e('None', 'wpcopyright'); ?>

	</option>
 <?php
 $choice = '';
        foreach ($this->defaults as $ch => $val) {
			// Build a string using heredoc formatting to enable easy reading at the page source.
			$value = esc_attr($ch);
			$selected = selected($checked, $ch, false);
			$title = esc_html($val['title']);
$choice .= <<<CHOICE
	<option value="$value"$selected>
		$title
	</option>

CHOICE;
		}
		echo $choice;
?>
 </select>

    <p class="description">
 <?php
        esc_html_e('Please select the default choice. This will be applied automatically to all new posts
				   unless the author manually selects another choice.', 'wpcopyright'); ?>

    </p>
 <?php
    }
    
	/**
	 * (non-PHPdoc)
	 * @see WPCopyright_Callbacks_Interface::wpcopyright_apply_remove_cb()
	 */
    public function wpcopyright_apply_remove_cb($args) {
		$label_for = esc_attr($args['label_for']);
        $choosecarefully = esc_html('Choose Carefully', 'wpcopyright');
        $applytoall = esc_html('Apply to all Posts', 'wpcopyright');
        $removefromall = esc_html('Remove from all Posts', 'wpcopyright');
        $desc = esc_html('You could apply the default choice to all previous posts, or remove the copyright info from all posts.
				   The former option only applies to the "post" post type.', 'wpcopyright');
        $applyremove = <<<APPLYREMOVE

    <select id="$label_for" name="$label_for">
        <option value="" disabled selected>
            $choosecarefully
		</option>
        <option value="apply">
            $applytoall
		</option>
        <option value="none">
            $removefromall
		</option>
    </select>
        <p class="description">
            $desc
        </p>
    <div class="wpcopyright-option-section">

APPLYREMOVE;
        echo $applyremove;

        
        /*
		 * Apply to specific author section.
		 * 
         * Adapted from wp_list_authors. This gets the post count.
         *
         * @param      wpdb      $wpdb            The wpdb object.
         * @param      array     $author_count    The number of posts for each author.
         * @param      object    $author_info     Contains each author's information.
         * @param      string    $authChoice      The licenses in option tags.
         */
        global $wpdb;
        $author_count = array();
        foreach ((array) $wpdb->get_results("SELECT DISTINCT post_author, COUNT(ID) AS count FROM $wpdb->posts WHERE " . get_private_posts_cap_sql($this->postTypes) . " GROUP BY post_author") as $row) {
            // This array will have the post counts for all authors.
            $author_count[$row->post_author] = $row->count;
        }
        // Check for results
        if ($this->authors) {
            ?>
            <select name="wpcopyright_author_id" id="wpcopyright_author_id">
            <option value="" selected>
             <?php esc_html_e('Select an author', 'wpcopyright'); ?>
            </option>
            <?php
            // loop through each author
            foreach ($this->authors as $ID) {
                // get all the user's data
                $author_info = get_userdata($ID);
                $posts = isset($author_count[$ID]) ? $author_count[$ID] : 0;
                echo '<option value="' . esc_html($ID) . '">' . esc_html($author_info->nickname) . '(' . $posts . ' posts)</option>' . PHP_EOL;
            }
            echo '</select>' . PHP_EOL;
        } else {
            echo 'No authors found';
        }
        ?>
    <select id="wpcopyright_author_choice" name="wpcopyright_author_choice">
        <option value="" selected>
        <?php
        esc_html_e('Select a license', 'wpcopyright'); ?>
	    </option>
	    <option value="none">
	    <?php // Translators: the other options have technical names and therefore must be represented in English.
        esc_html_e('None', 'wpcopyright'); ?>
	    </option>
 <?php
 $authChoice = '';
        foreach ($this->defaults as $ch => $val) {
            $value = esc_attr($ch);
			// Build a string using heredoc formatting to enable easy reading at the page source.
			$title = esc_html($val['title']);
$authChoice .= <<<AUTHCHOICE
	<option value="$value">
		$title
	</option>

AUTHCHOICE;
		}
		echo $authChoice;
?>
    </select>

    <p class="description">
 <?php
        esc_html_e("You could apply one of the licenses to a specific author's posts.", 'wpcopyright'); ?>

    </p>
 </div><!-- .wpcopyright-option-section -->

    <div class="wpcopyright-option-section">
        <?php
		
        /*
		 * Apply to specific post types section.
		 * 
         * @param      array     $args            The args sent to the 
         * @param      array     $author_count    The number of posts for each author.
         * @param      object    $author_info     Contains each author's information.
         * @param      string    $authChoice      The licenses in option tags.
         */
        $args = array(
    	'public' => true
    	);        
            ?>

    <select name="wpcopyright_post_type" id="wpcopyright_post_type">
        <option value="" selected>
            <?php esc_html_e('Select a post type', 'wpcopyright'); ?>

        </option>
<?php
        // Loop through each author.
        foreach ($this->postTypes as $label => $name) {
        // Get all the user's data.
?>
        <option value="<?php echo esc_html($name);?>">
            <?php echo esc_html($label) . PHP_EOL; ?>
        </option>
<?php
        }
?>
    </select>

    <select id="wpcopyright_post_choice" name="wpcopyright_post_choice">
        <option value="" selected>
			<?php
        esc_html_e('Select a license', 'wpcopyright'); ?>

	    </option>
	    <option value="none">
			<?php // Translators: the other options have technical names and therefore must be represented in English.
        esc_html_e('None', 'wpcopyright'); ?>

	    </option>
 <?php
 $choice = '';
        foreach ($this->defaults as $ch => $val) {
            $value = esc_html($ch);
			// Build a string using heredoc formatting to enable easy reading at the page source.
			$title = esc_html($val['title']);
$postChoice .= <<<POSTCHOICE
		<option value="$value">
			$title
		</option>

POSTCHOICE;
		}
		echo $postChoice;
?>
    </select>

    <p class="description">
 <?php
        esc_html_e("You could apply one of the licenses to a specific post type.", 'wpcopyright'); ?>

    </p>
 </div><!-- .wpcopyright-option-section -->
    <?php
    }
    
	/**
	 * (non-PHPdoc)
	 * @see WPCopyright_Callbacks_Interface::wpcopyright_textarea_cb()
	 */
    public function wpcopyright_textarea_cb($args) {

        /*
         * @var     string     $text          Existing text, if any.
         * @var     string     $label_for     The label/id attribute.
         * @var     string     $name          The name attribute.
         * @var     string     $defaultvalue  The default value.
         * @var     string     $textarea      The license's text or description.
         */
        $text = esc_textarea($this->options[$args['choice']][$args['param']]);
		$label_for = esc_attr($args['label_for']);
		$name = esc_attr("wpcopyright_options[${args['choice']}][${args['param']}]");
		$defaultvalue = esc_attr($this->defaults[$args['choice']][$args['param']]);
		$textarea = <<<TEXTAREA
		
    <textarea rows="4" cols="55" name="$name" class="resettable" id="$label_for"
    data-defaultvalue="$defaultvalue">
$text
	</textarea>

TEXTAREA;
		echo $textarea;
    }

	/**
	 * (non-PHPdoc)
	 * @see WPCopyright_Callbacks_Interface::wpcopyright_options_page_html()
	 */
    public function wpcopyright_options_page_html() {

        /**
         * Includes the class for the options page html.
         *
         * @var    object    $adminView    The admin view object.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'php/class-wpcopyright-plugin-admin-view.php';
		$adminView = new WPCopyright_Admin_View();
        $adminView->render();
    }
}