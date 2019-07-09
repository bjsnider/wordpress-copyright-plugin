<?php

/**
 * Widget class.
 * 
 * The widget will display the description in a paragraph tag's
 * title attribute, and then display the text in the paragraph.
 * The text can include html markup.
 * 
 * @since    0.1
 * @package    WPCopyright
 * @subpackage WPCopyright/php
 * @author Brandon Snider <brandonjsnider@gmail.com>
 * @copyright Copyright 2017 Brandon Snider
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License version 2 (GPLv2)
 */
class WPCopyright_Widget extends WP_Widget {
    
	public function __construct($opts = array(), $def = array()) {
		
		/**
		 * @var     array     $widget_ops     Widget options.
		 */
		$widget_ops = array(
			'classname' => 'wpcopyright_widget',
			'description' => "Displays the post's copyright information"
		);
		parent::__construct('wpcopyright_widget', 'Post Copyright Widget', $widget_ops);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @since     0.1
	 * @param     array      $args
	 * @param     array      $instance
	 */
	public function widget($args, $instance) {
		
		/**
		 * Outputs the content of the widget.
		 *
		 * @var      string      $wpcopyrightWidget     The entire widget as a string.
		 * @var      post        $post                  The post object.
		 * @var      string      $choice                The copyright choice string.
		 * @var      array       $defaults              The array with defaults.
		 */
        if (is_single()) {
        global $post;
		$options = get_option('wpcopyright_options');
        $wpcopyrightWidget = $args['before_widget'];
		if (!empty($instance['title'])) {
			$wpcopyrightWidget .= $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
		}
            $choice = get_post_meta($post->ID, '_wpcopyright_post_copyright_choice', true) ? get_post_meta($post->ID, '_wpcopyright_post_copyright_choice', true) : false;
            if ($choice) {
				$wpcopyrightWidget .= <<<MARKUP
	<div class="copyright-notice">
        <p title="{$options[$choice]['description']}">
            {$options[$choice]['text']}
		</p>
	</div><!-- .copyright-notice -->
MARKUP;
            }
		$wpcopyrightWidget .= $args['after_widget'];
		/**
		 * Without this line, the shortcode won't expand.
		 */
		$wpcopyrightWidget = do_shortcode($wpcopyrightWidget);
        echo $wpcopyrightWidget;
        }
	}

	/**
	 * Outputs the options form on admin.
	 * 
	 * @since     0.1
	 * @param     array     $instance     The widget options
	 */
	public function form($instance) {
		
		/** Outputs the options form on admin
		 *
		 * @var     string     $title     The widget title.
		 */
        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('', 'wpcopyright');
		?>
		<p>
		<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:', 'wpcopyright'); ?></label> 
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save.
	 *
	 * @since     0.1
	 * @param     array     $new_instance     The new options.
	 * @param     array     $old_instance     The previous options.
	 */
	public function update($new_instance, $old_instance) {
		
		/**
		 * Processes widget options to be saved.
		 *
		 * @var     array     $instance     The widget title.
		 */
        $instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
	}
}
