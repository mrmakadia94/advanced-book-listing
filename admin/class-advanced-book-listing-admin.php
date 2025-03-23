<?php
/**
 * The admin-specific functionality of the plugin.
 * @package    Advanced_Book_Listing
 * @subpackage Advanced_Book_Listing/admin
 */

class Advanced_Book_Listing_Admin {
	private $plugin_name;
	private $version;

	/**
	 * Constructor for the admin class
	 *
	 * @param string $plugin_name The name of the plugin
	 * @param string $version     The current version of the plugin
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}


	/**
	 * Registers the custom post type for books
	 */
	public function register_book_post_type() {
		$labels = array(
			'name'               => _x( 'Books', 'post type general name', 'advanced-book-listing' ),
			'singular_name'      => _x( 'Book', 'post type singular name', 'advanced-book-listing' ),
			'menu_name'          => _x( 'Books', 'admin menu', 'advanced-book-listing' ),
			'add_new'            => _x( 'Add New', 'book', 'advanced-book-listing' ),
			'add_new_item'       => __( 'Add New Book', 'advanced-book-listing' ),
			'edit_item'          => __( 'Edit Book', 'advanced-book-listing' ),
			'new_item'           => __( 'New Book', 'advanced-book-listing' ),
			'view_item'          => __( 'View Book', 'advanced-book-listing' ),
			'search_items'       => __( 'Search Books', 'advanced-book-listing' ),
			'not_found'          => __( 'No books found', 'advanced-book-listing' ),
			'not_found_in_trash' => __( 'No books found in Trash', 'advanced-book-listing' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'book' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'book', $args );
	}

	/**
	 * Adds meta box for book details
	 */
	public function add_book_meta_boxes() {
		add_meta_box(
			'book_details',
			__( 'Book Details', 'advanced-book-listing' ),
			array( $this, 'render_book_meta_box' ),
			'book',
			'normal',
			'high'
		);
	}

	/**
	 * Renders the book meta box content
	 *
	 * @param WP_Post $post The post object being edited
	 */
	public function render_book_meta_box( $post ) {
		wp_nonce_field( 'book_meta_box', 'book_meta_box_nonce' );

		$author_name  = get_post_meta( $post->ID, '_book_author_name', true );
		$price        = get_post_meta( $post->ID, '_book_price', true );
		$publish_date = get_post_meta( $post->ID, '_book_publish_date', true );
		?>
        <div class="book-meta-box">
            <p>
                <label for="book_author_name"><?php _e( 'Author Name:', 'advanced-book-listing' ); ?></label>
                <input type="text" id="book_author_name" name="book_author_name"
                       value="<?php echo esc_attr( $author_name ); ?>" class="widefat">
            </p>
            <p>
                <label for="book_price"><?php _e( 'Price ($):', 'advanced-book-listing' ); ?></label>
                <input type="number" id="book_price" name="book_price" value="<?php echo esc_attr( $price ); ?>" min="0"
                       step="0.01" class="widefat">
            </p>
            <p>
                <label for="book_publish_date"><?php _e( 'Publish Date:', 'advanced-book-listing' ); ?></label>
                <input type="date" id="book_publish_date" name="book_publish_date"
                       value="<?php echo esc_attr( $publish_date ); ?>" class="widefat">
            </p>
        </div>
		<?php
	}

	/**
	 * Saves book metadata when post is saved
	 *
	 * @param int $post_id The ID of the post being saved
	 */
	public function save_book_meta( $post_id ) {
		if ( ! isset( $_POST['book_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['book_meta_box_nonce'], 'book_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'book_author_name',
			'book_price',
			'book_publish_date'
		);

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta(
					$post_id,
					'_' . $field,
					sanitize_text_field( $_POST[ $field ] )
				);
			}
		}

		// Delete transients when book is updated
		delete_transient( 'abl_books_cache' );
	}
}