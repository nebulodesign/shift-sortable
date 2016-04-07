<?php 
/*
Plugin Name:	SHIFT - Sortable
Plugin URI:		https://github.com/nebulodesign/shift-sortable/
Description:	Allows taxonomy terms and posts to be given a custom order
Version:			0.1
Author:				Nebulo Design
Author URI:		http://nebulodesign.com
License:			GPL
*/

/**
 * Initiate custom plugin class - fetches updates from our own public GitHub repository.
 *
 * @param  string $pluginFile always use __FILE__
 * @param  string $gitHubUsername use 'nebulodesign'
 * @param  string $gitHubProjectName use name of plugin's repository
 * @param  string $accessToken optional
 * @return null
 */
if( is_admin() ) new Shift_Plugin_Updater( __FILE__, 'nebulodesign', 'shift-sortable' );


// Add sortable to post type
add_action( 'registered_post_type', function( $post_type, $args ){
	if( isset( $args->sortable ) && $args->sortable == true ) {

		add_action( 'admin_menu', function() use( $post_type, $args ){
			do_action( 'sortable/add_submenu_page', $post_type, $args );
		});

		add_action( 'admin_init', function() use( $post_type, $args ){

			// Get sortable taxonomies for this post type
			$sortable_taxonomies = array_values( array_filter(
				array_map( function( $taxonomy ){ return get_taxonomy( $taxonomy ); }, $args->taxonomies ),
				function( $args ) {
					return ( isset( $args->sortable ) && $args->sortable === true );
				}
			) );

			// Filter taxonomies
			$taxonomies = array_values( array_filter(
				$sortable_taxonomies,
				function( $args ){
					return !empty( get_terms( $args->name, array( 'hide_empty' => true ) ) );
				}
			) );

			do_action( 'sortable/add_posts_section', $post_type, $args, $taxonomies );					

			if( !empty( $sortable_taxonomies ) ) {

				// Filter taxonomies
				$taxonomies = array_values( array_filter( $sortable_taxonomies, function( $taxonomy ){
					return ( count( get_terms( $taxonomy->name, array( 'hide_empty' => false ) ) ) > 1 );
				}) );

				if( !empty( $taxonomies ) ) {
					do_action( 'sortable/add_terms_section', $post_type, $args, $taxonomies );					
				}
			}

				// Order posts filtered by term
//				add_settings_section(
//					'order_' . $taxonomies[0]->name . '_section',
//					$taxonomies[0]->label . ' Ordering',
//					function() use( $taxonomies ){ /*include 'inc/order-terms.php';*/ },
//					'order-' . $post_type
//				);

		});

		add_action( 'admin_enqueue_scripts', function() use( $post_type ){
			if( strpos( get_current_screen()->id, '_page_order-' . $post_type ) !== false )
				do_action( 'sortable/enqueue_scripts' );
		});

	}
}, 10, 2 );


// Add sortable to taxonomy
add_action( 'registered_taxonomy', function( $taxonomy, $object_type, $args ){
	if( isset( $args['sortable'] ) && $args['sortable'] === true ) {

		add_action( 'admin_menu', function() use( $taxonomy ){

			$post_types = array_filter( get_post_types( array(), 'objects' ), function( $post_type ) use( $taxonomy ){
				return ( ( !isset( $post_type->sortable ) || $post_type->sortable !== true ) && in_array( $taxonomy, $post_type->taxonomies ) ); }
			);

			foreach( $post_types as $post_type ) {

				list( $post_type, $args ) = array( $post_type->name, $post_type );

				do_action( 'sortable/add_submenu_page', $post_type, $args );
			}
		});

		add_action( 'admin_init', function() use( $taxonomy, $args ){

			$post_types = array_filter( get_post_types( array(), 'objects' ), function( $post_type ) use( $taxonomy ){
				return ( ( !isset( $post_type->sortable ) || $post_type->sortable !== true ) && in_array( $taxonomy, $post_type->taxonomies ) ); }
			);

			if( count( get_terms( $taxonomy, array( 'hide_empty' => false ) ) ) > 1 ) {

				foreach( $post_types as $post_type ) {

					list( $post_type, $args ) = array( $post_type->name, $post_type );

					// Get sortable taxonomies for this post type
					$taxonomies = array_values( array_filter(
						array_map( function( $taxonomy ) use( $args ){ return get_taxonomy( $taxonomy ); }, $args->taxonomies ),
						function( $args ) {
							return ( isset( $args->sortable ) && $args->sortable === true );
						}
					) );

					do_action( 'sortable/add_terms_section', $post_type, $args, $taxonomies );					
				}
			}
		});

		add_action( 'admin_enqueue_scripts', function() use( $taxonomy ){

			$post_types = array_filter( get_post_types( array(), 'objects' ), function( $post_type ) use( $taxonomy ){
				return ( ( !isset( $post_type->sortable ) || $post_type->sortable !== true ) && in_array( $taxonomy, $post_type->taxonomies ) && strpos( get_current_screen()->id, '_page_order-' . $post_type->name ) !== false ); }
			);

			if( !empty( $post_types ) )
				do_action( 'sortable/enqueue_scripts' );
		});
	}
}, 10, 3 );


add_action( 'sortable/add_submenu_page', function( $post_type, $args ){

	global $submenu;

	$parent_slug = add_query_arg( 'post_type', $post_type, 'edit.php' );
	$menu_slug = 'order-' . $post_type;

	if( empty( array_filter( $submenu[$parent_slug], function( $submenu_item ) use( $menu_slug ){ return in_array( $menu_slug, $submenu_item ); }) ) )
		add_submenu_page(
			$parent_slug,
			'Order '.$args->label,
			'Order '.$args->label,
			'edit_pages',
			$menu_slug,
			function() use( $post_type, $args ){
				echo '<div class="wrap">';
				echo '<h1>Order ' . $args->label . '</h1>';
				do_action( 'sortable/admin_notices' );
				do_settings_sections( 'order-' . $post_type );
				echo '</div>';
			}
		);
}, 10, 2 );

add_action( 'sortable/add_posts_section', function( $post_type, $args, $taxonomies ){
	add_settings_section(
		'order_' . $post_type . '_posts_section',
		$args->label . ' Ordering',
		function() use( $post_type, $args, $taxonomies ){ include 'inc/order-posts.php'; },
		'order-' . $post_type
	);
}, 10, 3 );

add_action( 'sortable/add_terms_section', function( $post_type, $args, $taxonomies ){

	global $wp_settings_sections;

	$section_id = 'order_' . $post_type . '_terms_section';
	$page_slug = 'order-' . $post_type;

	if( empty( $wp_settings_sections ) || ( in_array( $page_slug, array_keys( $wp_settings_sections ) ) && empty( array_filter( $wp_settings_sections[$page_slug], function( $page_section ) use( $section_id ){ return ( $page_section['id'] === $section_id ); }) ) ) )
		add_settings_section(
			$section_id,
			count( $taxonomies ) > 1 ? $args->label . ' Categories Ordering' : $taxonomies[0]->label . ' Ordering',
			function() use( $taxonomies ){ include 'inc/order-terms.php'; },
			$page_slug
		);
}, 10, 3 );


/** Enqueue scripts and styles */

add_action( 'sortable/enqueue_scripts', function(){
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_style( 'sortable-style', plugins_url( 'style.css', __FILE__ ) );
	wp_enqueue_script( 'sortable-init', plugins_url( 'sortable-init.js', __FILE__ ) );
	wp_localize_script( 'sortable-init', 'admin_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
});


/** AJAX functions */

add_action( 'wp_ajax_sortable/select_posts', function(){ // show posts for selected term

	$term_id = isset( $_POST['term_id'] ) ? $_POST['term_id'] : null;
	$taxonomy = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : null;
	$post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : null;

	if( $term_id && $post_type ) {

		$get_posts_args = array(
			'post_type' => $post_type,
			'posts_per_page' => -1,
			'order' => 'ASC',
		);

		if( $taxonomy && $term_id !== 'all' ) {
			$get_posts_args['tax_query'] = array(
				array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => $term_id
				)
			);
			$get_posts_args['meta_key'] = 'term_' . $term_id . '_order';
			$get_posts_args['orderby'] = 'meta_value_num';
		}
		elseif( $term_id === 'all' ) {
			$get_posts_args['orderby'] = 'menu_order';
		}

		foreach( get_posts( $get_posts_args ) as $post ) {
			include 'inc/post-li.php';
		}
	}
	exit;
});


add_action( 'wp_ajax_sortable/save_posts_order', function(){ // update order of posts

	$post_ids = $_POST['post_ids'];
	$term_id = $_POST['term_id'];

	$i = 1;
	foreach( $post_ids as $post_id ) {
		if( $term_id === 'all' ) {
			wp_update_post( array( 'ID' => $post_id, 'menu_order' => $i++ ) );
		}
		else {
			update_post_meta( $post_id, 'term_' . $term_id . '_order', $i++ );
		}
	}

	echo 'success';
	exit;
});


add_action( 'wp_ajax_sortable/select_terms', function(){ // show posts for selected term

	$taxonomy = isset( $_POST['taxonomy'] ) ? get_taxonomy( $_POST['taxonomy'] ) : null;

	if( $taxonomy ) {

		foreach( get_terms( $taxonomy->name, array( 'hide_empty' => false, 'orderby' => 'term_order' ) ) as $term ) {
			include 'inc/term-li.php';
		}
	}
	exit;
});


add_action( 'wp_ajax_sortable/save_terms_order', function(){ // update order of posts

	$term_ids = $_POST['term_ids'];

	$i = 1;
	foreach( $term_ids as $term_id )
		update_term_meta( $term_id, 'term_order', $i++ );

	exit;
});


// add ability to use 'term_order' (for example) as an 'orderby' arguement for get_terms()
add_filter( 'terms_clauses', function( $clauses, $taxonomy, $args ){

	$taxonomy_is_sortable = !empty( array_filter( $taxonomy, function( $taxonomy ){
		$taxonomy = get_taxonomy( $taxonomy );
		return ( isset( $taxonomy->sortable ) && $taxonomy->sortable === true );
	} ) );

	if( $taxonomy_is_sortable === true && $args['orderby'] === 'term_order' ) {

		if( empty( $args['meta_query'] ) || empty( array_filter( $args['meta_query'], function( $mq ){ return ( is_array( $mq ) && in_array( 'term_order', $mq ) ); }) ) ) {

			$mquery = new WP_Meta_Query( array( array( 'key' => 'term_order' ) ) );
		  $mq_sql = $mquery->get_sql( 'term', 't', 'term_id' );

			$clauses['where'] .= $mq_sql['where'];
			$clauses['join'] = $mq_sql['join'] . $clauses['join'];
		}

		global $wpdb;
		$clauses['orderby'] = "ORDER BY {$wpdb->termmeta}.meta_value";
	}

	return $clauses;

}, 10, 3 );
