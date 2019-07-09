<?php

/**
 * Interface for the callbacks.
 *
 * Defines the interface for the callbacks to be used on the settings page.
 *
 * @since    0.1
 * @package    WPCopyright
 * @subpackage WPCopyright/php
 * @author Brandon Snider <brandonjsnider@gmail.com>
 * @copyright Copyright 2017 Brandon Snider
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License version 2 (GPLv2)
 */
interface WPCopyright_Callbacks_Interface {
    
    /**
	 * Customize section callback.
	 *
	 * Used by the wpcopyright_copyright_custom section.
	 *
	 * @since    0.1
	 * @var      array      $args      The args sent to the callback.
	 */
    public function wpcopyright_section_customize_cb($args);
    
    /**
	 * Customize choice callback.
	 *
	 * Used to create sections to customize each
	 * text and description.
	 *
	 * @var      array      $args      The args sent to the callback.
	 */
    public function wpcopyright_customize_cb($args);
    
    /**
	 * Append post callback.
	 *
	 * Used to offer the choice to display the copyright
	 * text at the end of each post.
	 *
	 * @since    0.1
	 * @var      array      $args      The args sent to the callback.
	 */
    public function wpcopyright_append_post_cb($args);
    
    /**
	 * Section list callback.
	 *
	 * Used to create the section in which the list of
	 * choices is displayed.
	 *
	 * @since    0.1
	 * @var      array      $args      The args sent to the callback.
	 */
    public function wpcopyright_section_list_cb($args);
    
    /**
	 * Callback for the default choice
	 *
	 * Offers the user the choice of possible defaults.
	 *
	 * @since    0.1
	 * @var      array      $args      The args sent to the callback.
	 */
    public function wpcopyright_copyright_choice_cb($args);
    
    /**
	 * Applies default choice to all or removes choices from all.
	 *
	 * Offers the user the choice to either apply the default choice
	 * to all posts, or to remove any choices that have been made
	 * from all posts.
	 *
	 * @since    0.1
	 * @var      array      $args      The args sent to the callback.
	 */
    public function wpcopyright_apply_remove_cb($args);
    
    /**
	 * Generic textarea callback
	 *
	 * Used by the Settings script to generate the textareas
	 * for the Text and Description fields.
	 *
	 * @since    0.1
	 * @var      array      $args      The args sent to the callback.
	 */
    public function wpcopyright_textarea_cb($args);
    
    /**
	 * Callback for the options page
	 *
	 * @since    0.1
	 */
    public function wpcopyright_options_page_html();
}