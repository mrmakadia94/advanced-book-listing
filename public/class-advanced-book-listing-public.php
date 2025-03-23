<?php
/**
 * The public-facing functionality of the plugin.
 * @package    Advanced_Book_Listing
 * @subpackage Advanced_Book_Listing/public
 */

class Advanced_Book_Listing_Public {
	private $plugin_name;
	private $version;

	/**
	 * Constructor - Initializes the plugin name and version, and registers the shortcode
	 *
	 * @param string $plugin_name The name of the plugin
	 * @param string $version     The current version of the plugin
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_shortcode( 'advanced_books', array( $this, 'render_books_listing' ) );
	}

	/**
	 * Enqueues public-facing stylesheets
	 */
	public function enqueue_public_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/advanced-book-listing-public.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Enqueues public-facing JavaScript and localizes AJAX parameters
	 */
	public function enqueue_public_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/advanced-book-listing-public.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_localize_script(
			$this->plugin_name,
			'ablAjax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'abl_nonce' )
			)
		);
	}

	/**
	 * Renders the books listing shortcode output with caching support
	 *
	 * @param array $atts Shortcode attributes
	 *
	 * @return string HTML output of the books listing
	 */
	public function render_books_listing( $atts ) {
		// Create a unique cache key based on sanitized attributes
		$cache_key = 'abl_books_cache_' . md5( serialize( $atts ) );
		$output    = get_transient( $cache_key );

		if ( false === $output ) {
			ob_start();
			?>
            <div class="advanced-books-listing">
                <div class="books-filters">
                    <select class="author-filter">
                        <option value=""><?php esc_html_e( 'Filter by Author', 'advanced-book-listing' ); ?></option>
						<?php
						foreach ( range( 'A', 'Z' ) as $letter ) {
							printf(
								'<option value="%1$s">%1$s</option>',
								esc_attr( $letter )
							);
						}
						?>
                    </select>

                    <select class="price-filter">
                        <option value=""><?php esc_html_e( 'Filter by Price', 'advanced-book-listing' ); ?></option>
                        <option value="50-100"><?php esc_html_e( '$50 - $100', 'advanced-book-listing' ); ?></option>
                        <option value="100-150"><?php esc_html_e( '$100 - $150', 'advanced-book-listing' ); ?></option>
                        <option value="150-200"><?php esc_html_e( '$150 - $200', 'advanced-book-listing' ); ?></option>
                    </select>

                    <select class="sort-filter">
                        <option value="newest"><?php esc_html_e( 'Newest First', 'advanced-book-listing' ); ?></option>
                        <option value="oldest"><?php esc_html_e( 'Oldest First', 'advanced-book-listing' ); ?></option>
                    </select>
                </div>

                <div class="books-grid">
					<?php
					$books_query = $this->get_books_query();
					if ( $books_query->have_posts() ) {
						while ( $books_query->have_posts() ) {
							$books_query->the_post();
							$this->render_book_card( get_the_ID() );
						}
					} else {
						echo '<p class="no-books">' . esc_html__( 'No books found.', 'advanced-book-listing' ) . '</p>';
					}
					wp_reset_postdata();
					?>
                </div>

				<?php if ( $books_query->max_num_pages > 1 ) : ?>
                    <div class="load-more-container">
                        <button class="load-more-books" data-page="1"
                                data-max="<?php echo $books_query->max_num_pages; ?>">
							<?php _e( 'Load More', 'advanced-book-listing' ); ?>
                        </button>
                    </div>
				<?php endif; ?>
            </div>
			<?php
			$output = ob_get_clean();

			// Cache the output for better performance
			set_transient( $cache_key, $output, HOUR_IN_SECONDS );
		}

		return $output;
	}

	/**
	 * Creates and returns a custom WP_Query for books with filtering and sorting
	 *
	 * @param array $args Query arguments including filters and pagination
	 *
	 * @return WP_Query The customized books query
	 */
	public function get_books_query( $args = array() ) {
		$default_args = array(
			'post_type'      => 'book',
			'posts_per_page' => 10,
			'paged'          => 1,
			'author_filter'  => '',
			'price_range'    => '',
			'sort_by'        => 'newest'
		);

		$args       = wp_parse_args( $args, $default_args );
		$meta_query = array( 'relation' => 'AND' );

		// Author filter
		if ( ! empty( $args['author_filter'] ) ) {
			$meta_query[] = array(
				'key'     => '_book_author_name',
				'value'   => $args['author_filter'],
				'compare' => 'LIKE',
			);
		}

		// Price range filter
		if ( ! empty( $args['price_range'] ) ) {
			$price_range = explode( '-', $args['price_range'] );
			if ( count( $price_range ) === 2 ) {
				$meta_query[] = array(
					'key'     => '_book_price',
					'value'   => array( $price_range[0], $price_range[1] ),
					'type'    => 'NUMERIC',
					'compare' => 'BETWEEN',
				);
			}
		}

		$query_args = array(
			'post_type'      => 'book',
			'posts_per_page' => $args['posts_per_page'],
			'paged'          => $args['paged'],
			'meta_query'     => $meta_query,
		);

		// Sort order
		if ( $args['sort_by'] === 'oldest' ) {
			$query_args['orderby']  = 'meta_value';
			$query_args['meta_key'] = '_book_publish_date';
			$query_args['order']    = 'ASC';
		} else {
			$query_args['orderby']  = 'meta_value';
			$query_args['meta_key'] = '_book_publish_date';
			$query_args['order']    = 'DESC';
		}

		return new WP_Query( $query_args );
	}

	/**
	 * Renders individual book card HTML with meta information
	 *
	 * @param int $post_id The ID of the book post to display
	 */
	public function render_book_card( $post_id ) {
		// Sanitize the post ID to ensure it's a valid integer
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return;
		}
		$author_name  = get_post_meta( $post_id, '_book_author_name', true );
		$price        = get_post_meta( $post_id, '_book_price', true );
		$publish_date = get_post_meta( $post_id, '_book_publish_date', true );
		?>
        <div class="book-card">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
                <div class="book-thumbnail">
					<?php echo wp_kses_post( get_the_post_thumbnail( $post_id, 'medium' ) ); ?>
                </div>
			<?php endif; ?>
            <div class="book-details">
                <h3 class="book-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
                <p class="book-author"><?php echo esc_html( $author_name ); ?></p>
                <p class="book-price"><?php echo esc_html( '$' . number_format( (float) $price, 2 ) ); ?></p>
                <p class="book-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $publish_date ) ) ); ?></p>
            </div>
        </div>
		<?php
	}

	/**
	 * Handles AJAX requests for loading more books with current filters
	 * Outputs JSON response with HTML content and pagination status
	 */
	public function load_more_books() {
		check_ajax_referer( 'abl_nonce', 'nonce' );

		$page          = isset( $_POST['page'] ) ? (int) $_POST['page'] : 1;
		$author_filter = isset( $_POST['author'] ) ? sanitize_text_field( $_POST['author'] ) : '';
		$price_range   = isset( $_POST['price_range'] ) ? sanitize_text_field( $_POST['price_range'] ) : '';
		$sort_by       = isset( $_POST['sort_by'] ) ? sanitize_text_field( $_POST['sort_by'] ) : 'newest';

		$args = array(
			'paged'         => $page,
			'author_filter' => $author_filter,
			'price_range'   => $price_range,
			'sort_by'       => $sort_by
		);

		$books_query = $this->get_books_query( $args );

		ob_start();
		if ( $books_query->have_posts() ) {
			while ( $books_query->have_posts() ) {
				$books_query->the_post();
				$this->render_book_card( get_the_ID() );
			}
		}
		wp_reset_postdata();

		$response = array(
			'html'     => ob_get_clean(),
			'has_more' => $page < $books_query->max_num_pages
		);

		wp_send_json_success( $response );
	}
}