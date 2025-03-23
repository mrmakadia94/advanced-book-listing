<?php
/**
 * The core plugin class.
 * @package    Advanced_Book_Listing
 * @subpackage Advanced_Book_Listing/includes
 */

class Advanced_Book_Listing {
	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		if ( defined( 'ADVANCED_BOOK_LISTING_VERSION' ) ) {
			$this->version = ADVANCED_BOOK_LISTING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'advanced-book-listing';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-advanced-book-listing-loader.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-advanced-book-listing-i18n.php';
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-advanced-book-listing-admin.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-advanced-book-listing-public.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-advanced-book-listing-rest-api.php';

		$this->loader = new Advanced_Book_Listing_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new Advanced_Book_Listing_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks() {
		$plugin_admin = new Advanced_Book_Listing_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_admin, 'register_book_post_type' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_book_meta_boxes' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_book_meta' );
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}

	private function define_public_hooks() {
		$plugin_public = new Advanced_Book_Listing_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_public_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_public_scripts' );
		$this->loader->add_action( 'wp_ajax_load_more_books', $plugin_public, 'load_more_books' );
		$this->loader->add_action( 'wp_ajax_nopriv_load_more_books', $plugin_public, 'load_more_books' );

		// Initialize REST API
		new Advanced_Book_Listing_REST_API();
	}

	public function run() {
		$this->loader->run();
	}

	public function get_loader() {
		return $this->loader;
	}
}