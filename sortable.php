<?php 
/*
Plugin Name:	SHIFT - Sortable
Plugin URI:		https://github.com/nebulodesign/shift-sortable/
Description:	Allows taxonomy terms and posts to be given a custom order
Version:			1.1.2
Author:				Nebulo Design
Author URI:		http://nebulodesign.com
License:			GPL
*/

/**
 * Initiate custom plugin class - fetches updates from our own public GitHub repository.
 */
if( is_admin() && class_exists( 'Shift_Plugin_Updater' ) ) new Shift_Plugin_Updater( __FILE__ );


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

	if( isset($submenu[$parent_slug]) && empty( array_filter( $submenu[$parent_slug], function( $submenu_item ) use( $menu_slug ){ return in_array( $menu_slug, $submenu_item ); }) ) )
		add_submenu_page(
			$parent_slug,
			'Order '.$args->label,
			'Order '.$args->label . '<span class="dashicons dashicons-sort" style="float: right; font-size: 1em"></span>',
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
			$get_posts_args['orderby'] = 'term_' . $term_id . '_order';
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

	$post_ids = array_map( 'intval', $_POST['post_ids'] );
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

		global $wpdb;

		$mquery = new WP_Meta_Query( array(
			'relation' => 'OR',
			array( 'key' => 'term_order', 'compare' => 'EXISTS' ),
			array( 'key' => 'term_order', 'compare' => 'NOT EXISTS' ),
		) );

		$mq_sql = array_map( function( $clause ){

			global $wpdb;

			return str_replace(

				array(
					"LEFT JOIN {$wpdb->termmeta} ON",
					"{$wpdb->termmeta}.term_id",
					"{$wpdb->termmeta}.meta_key",
					"LEFT JOIN {$wpdb->termmeta} AS mt1 ON",
					"mt1.term_id",
					"mt1.meta_key",
				),

				array(
					"LEFT JOIN {$wpdb->termmeta} AS srtbl1 ON",
					"srtbl1.term_id",
					"srtbl1.meta_key",
					"LEFT JOIN {$wpdb->termmeta} AS srtbl2 ON",
					"srtbl2.term_id",
					"srtbl2.meta_key",
				),

				$clause

			);

		}, $mquery->get_sql( 'term', 't', 'term_id' ) );

		$clauses['join'] .= $mq_sql['join'];

		$clauses['where'] .= $mq_sql['where'];

		$clauses['fields'] .= ", IFNULL( srtbl2.meta_value, ( SELECT MAX(meta_value) FROM {$wpdb->termmeta} WHERE meta_key='term_order' ) + 1 ) AS term_order";

		$clauses['orderby'] = str_replace( 'ORDER BY', 'ORDER BY term_order ASC, ', $clauses['orderby'] );

	}

	return $clauses;

}, 10, 3 );


// add ability to use 'term_posts_order' and 'term_$i_order' (for example) as 'orderby' arguements for get_posts()
add_action( 'pre_get_posts', function( $query ){

	if( isset( $query->query['orderby'] ) && $query->query['orderby'] === 'term_posts_order' && is_tax() && is_a( get_queried_object(), 'WP_Term' ) )

		$query->set( 'orderby', 'term_' . get_queried_object()->term_id . '_order' );

	if( isset( $query->query_vars['orderby'] ) && preg_match( '/^term_(\d+)_order$/', $query->query_vars['orderby'] ) ) {

		$query->set( 'suppress_filters', false );

		$orderby = $query->query_vars['orderby'];

		add_filter( 'posts_clauses', function( $clauses ) use( $orderby ){

			global $wpdb;

			$mquery = new WP_Meta_Query( array(
				'relation' => 'OR',
				array( 'key' => $orderby, 'compare' => 'EXISTS' ),
				array( 'key' => $orderby, 'compare' => 'NOT EXISTS' ),
			) );

			$mq_sql = array_map( function( $clause ){

				global $wpdb;

				return str_replace(

					array(
						"LEFT JOIN {$wpdb->postmeta} ON",
						"{$wpdb->postmeta}.post_id",
						"{$wpdb->postmeta}.meta_key",
						"LEFT JOIN {$wpdb->postmeta} AS mt1 ON",
						"mt1.post_id",
						"mt1.meta_key",
					),

					array(
						"LEFT JOIN {$wpdb->postmeta} AS srtbl1 ON",
						"srtbl1.post_id",
						"srtbl1.meta_key",
						"LEFT JOIN {$wpdb->postmeta} AS srtbl2 ON",
						"srtbl2.post_id",
						"srtbl2.meta_key",
					),

					$clause

				);

			}, $mquery->get_sql( 'post', $wpdb->prefix.'posts', 'ID' ) );

			$clauses['join'] .= $mq_sql['join'];

			$clauses['where'] .= $mq_sql['where'];

			$clauses['fields'] .= ", IFNULL( srtbl2.meta_value, ( SELECT MAX(meta_value) FROM {$wpdb->postmeta} WHERE meta_key='{$orderby}' ) + 1 ) AS {$orderby}";

			$clauses['orderby'] = $orderby . ' ASC, ' . $clauses['orderby'];

			return $clauses;

		});

	}

}, 9999 );
