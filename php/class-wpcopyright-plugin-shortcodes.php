<?php

/**
 * Creates two shortcodes.
 * 
 * This class creates the two optional shortcodes.
 * The shortcodes can be included in the text field to assist
 * with the desired copyright display information.
 * 
 * @since    0.1
 * @package    WPCopyright
 * @subpackage WPCopyright/php
 * @author Brandon Snider <brandonjsnider@gmail.com>
 * @copyright Copyright 2017 Brandon Snider
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License version 2 (GPLv2)
 */
class WPCopyright_Shortcodes {
    
    public function __construct() {
        
    }
	
	/**
	* Creates cp-years shortcode.
	*
	* @since      0.1
	* @param      array       $atts         Optional. Attributes the user passed when using the shortcode.
	* @param      string      $content      Optional. Enclosed text.
	* @param      string      $tag          Optional. Name of the shortcode.
	* @return     string                    The shortcode.
	*/
	public function wpcopyright_years_shortcode($atts = [], $content = null, $tag = '') {
	   // Nullifies any passed attributes or enclosed content.
	   $atts[] = $content = null;
	   
	   // Creates $years var for later use.
	   $years = '';
	   
	   /**
		* Capture the post's publication year and the current year.
		*
		* @var      string      $postdate         The year the post was published.
		* @var      string      $currentyear      The current year.
		*/
		   $postdate = get_the_date('Y');
		   $currentyear = date('Y');
		   if ($postdate === $currentyear) {
				  $years = $currentyear;
		   } else {
				  $years = $postdate . ' &ndash; ' . $currentyear;
		   }
	
	   /* years should now be a string in this format: $pub - $cur,
		* where $pub is the year the post was published, and $cur is
		* the current year, or just $currentyear if both years are the same.
		*/
	   return $years;
	}
	
	/**
	* Creates blog-title shortcode.
	*
	* Creates a shortcode that displays the blog-title
	* for use in the copyright text.
	*
	* @since      0.1
	* @param      array       $atts         Optional. Attributes the user passed when using the shortcode.
	* @param      string      $content      Optional. Enclosed text.
	* @param      string      $tag          Optional. Name of the shortcode.
	* @return     string                    The shortcode.
	*/
	public function wpcopyright_blogtitle_shortcode($atts = [], $content = null, $tag = '') {
	   // Nullifies any passed attributes or enclosed content.
	   $atts[] = $content = null;
	   
	   /**
	    * Creates $blogtitle var and fills it with the WordPress function get_bloginfo('name').
		*
		* @var     string     $blogTitle     The title of the blog.
		*/
	   $blogTitle = get_bloginfo('name');
	   
	   /*
		* $blogTitle will now have the text entered in the
		* General Settings "Site Title" field.
		*/
	   return $blogTitle;
	}
}