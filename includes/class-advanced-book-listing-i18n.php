<?php
/**
 * Define the internationalization functionality.
 *
 * @package    Advanced_Book_Listing
 * @subpackage Advanced_Book_Listing/includes
 */

class Advanced_Book_Listing_i18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'advanced-book-listing',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}