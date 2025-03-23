<?php
/**
 * REST API functionality for the plugin.
 * @package    Advanced_Book_Listing
 * @subpackage Advanced_Book_Listing/includes
 */

class Advanced_Book_Listing_REST_API {
	private $namespace = 'books/v1';

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/list',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_books' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'author'      => array(
						'required' => false,
						'type'     => 'string',
					),
					'price_range' => array(
						'required' => false,
						'type'     => 'string',
					),
					'sort_by'     => array(
						'required' => false,
						'type'     => 'string',
						'enum'     => array( 'newest', 'oldest' ),
					),
					'page'        => array(
						'required' => false,
						'type'     => 'integer',
						'default'  => 1,
					),
					'per_page'    => array(
						'required' => false,
						'type'     => 'integer',
						'default'  => 10,
					),
				),
			)
		);
	}

	public function get_books( $request ) {
		$args = array(
			'paged'          => $request->get_param( 'page' ),
			'posts_per_page' => $request->get_param( 'per_page' ),
			'author_filter'  => $request->get_param( 'author' ),
			'price_range'    => $request->get_param( 'price_range' ),
			'sort_by'        => $request->get_param( 'sort_by' ),
		);

		$books_query = new WP_Query( $this->get_query_args( $args ) );
		$books       = array();

		if ( $books_query->have_posts() ) {
			while ( $books_query->have_posts() ) {
				$books_query->the_post();
				$books[] = $this->prepare_book_for_response( get_post() );
			}
		}

		wp_reset_postdata();

		$response = array(
			'books'        => $books,
			'total_pages'  => $books_query->max_num_pages,
			'total_books'  => $books_query->found_posts,
			'current_page' => $args['paged'],
		);

		return rest_ensure_response( $response );
	}

	private function get_query_args( $args ) {
		$query_args = array(
			'post_type'      => 'book',
			'posts_per_page' => $args['posts_per_page'],
			'paged'          => $args['paged'],
			'meta_query'     => array( 'relation' => 'AND' ),
		);

		if ( ! empty( $args['author_filter'] ) ) {
			$query_args['meta_query'][] = array(
				'key'     => '_book_author_name',
				'value'   => $args['author_filter'],
				'compare' => 'LIKE',
			);
		}

		if ( ! empty( $args['price_range'] ) ) {
			$price_range = explode( '-', $args['price_range'] );
			if ( count( $price_range ) === 2 ) {
				$query_args['meta_query'][] = array(
					'key'     => '_book_price',
					'value'   => array( $price_range[0], $price_range[1] ),
					'type'    => 'NUMERIC',
					'compare' => 'BETWEEN',
				);
			}
		}

		if ( $args['sort_by'] === 'oldest' ) {
			$query_args['orderby']  = 'meta_value';
			$query_args['meta_key'] = '_book_publish_date';
			$query_args['order']    = 'ASC';
		} else {
			$query_args['orderby']  = 'meta_value';
			$query_args['meta_key'] = '_book_publish_date';
			$query_args['order']    = 'DESC';
		}

		return $query_args;
	}

	private function prepare_book_for_response( $post ) {
		return array(
			'id'           => $post->ID,
			'title'        => get_the_title( $post->ID ),
			'author'       => get_post_meta( $post->ID, '_book_author_name', true ),
			'price'        => (float) get_post_meta( $post->ID, '_book_price', true ),
			'publish_date' => get_post_meta( $post->ID, '_book_publish_date', true ),
			'thumbnail'    => get_the_post_thumbnail_url( $post->ID, 'medium' ),
			'excerpt'      => get_the_excerpt( $post->ID ),
			'link'         => get_permalink( $post->ID ),
		);
	}
}