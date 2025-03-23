<?php
/**
 * Plugin Name:       Advanced Book Listing
 * Plugin URI:        https://example.com/advanced-book-listing
 * Description:       Advanced Book Listing with custom filters and AJAX pagination
 * Version:          1.0.0
 * Author:           Meet Makadia
 * Author URI:       https://wpmeet.in/
 * License:          GPL-2.0+
 * License URI:      http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:      advanced-book-listing
 * Domain Path:      /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ADVANCED_BOOK_LISTING_VERSION', '1.0.0' );

function activate_advanced_book_listing() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-advanced-book-listing-activator.php';
	Advanced_Book_Listing_Activator::activate();
}

function deactivate_advanced_book_listing() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-advanced-book-listing-deactivator.php';
	Advanced_Book_Listing_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_advanced_book_listing' );
register_deactivation_hook( __FILE__, 'deactivate_advanced_book_listing' );

require plugin_dir_path( __FILE__ ) . 'includes/class-advanced-book-listing.php';

function run_advanced_book_listing() {
	$plugin = new Advanced_Book_Listing();
	$plugin->run();
}

run_advanced_book_listing();