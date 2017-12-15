<?php
/**
 * Options Framework
 *
 * @package   Options Framework
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2013 WP Theming
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Don't load if optionsframework_init is already defined
if (is_admin() && ! function_exists( 'optionsframework_init' ) ) :

function optionsframework_init() {

	//  If user can't edit theme options, exit
	if ( ! current_user_can( 'edit_theme_options' ) )
		return;

	// Loads the required Options Framework classes.
	require_once dirname( __FILE__ ) . '/includes/class-options-framework.php';
	require_once dirname( __FILE__ ) . '/includes/class-options-framework-admin.php';
	require_once dirname( __FILE__ ) . '/includes/class-options-interface.php';
	require_once dirname( __FILE__ ) . '/includes/class-options-media-uploader.php';
	require_once dirname( __FILE__ ) . '/includes/class-options-sanitization.php';

	// Instantiate the main class.
	$options_framework = new Options_Framework;
	$options_framework->init();

	// Instantiate the options page.
	$options_framework_admin = new Options_Framework_Admin;
	$options_framework_admin->init();

	// Instantiate the media uploader class
	$options_framework_media_uploader = new Options_Framework_Media_Uploader;
	$options_framework_media_uploader->init();

}

add_action( 'init', 'optionsframework_init', 20 );

endif;


/**
 * Helper function to return the theme option value.
 * If no value has been saved, it returns $default.
 * Needed because options are saved as serialized strings.
 *
 * Not in a class to support backwards compatibility in themes.
 */
if ( ! function_exists( 'vpanel_options' ) ) :
	function vpanel_options( $name, $default = false ) {
		global $themename;
		$options = get_option(vpanel_options);
		if ( isset( $options[$name] ) ) {
			return $options[$name];
		}
		return $default;
	}
endif;