<?php 
/*
Plugin Name:	SHIFT - Sortable
Plugin URI:		http://nebulodesign.com
Description:	Allows taxonomy terms and posts to be given a custom order
Version:			0.1
Author:				Wesley Burden & Richard Knight
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


register_activation_hook( __FILE__, function(){

	global $wpdb;

	if( !$wpdb->query( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $wpdb->terms . ' LIKE term_order' ) ) ) {
		$wpdb->query( $wpdb->prepare( 'ALTER TABLE ' . $wpdb->terms . ' ADD term_order INT(11) NOT NULL AFTER term_group' ) );
	}
});

add_action( 'registered_post_type', function( $post_type, $args ){

	if( isset( $args->sortable ) ) {
		if( $args->sortable === true ) {

			add_action( 'admin_menu', function() use( $post_type, $args ){
				add_submenu_page(
					'edit.php' . ( $post_type !== 'post' ? '?post_type=' . $post_type : null ),
					'Order ' . $args->label,
					'Order ' . $args->label,
					'edit_others_posts',
					'order-' . $post_type,
					function() use( $post_type, $args ){
						echo '<div class="wrap">';
						echo '<h1>Order ' . $args->label . '</h1>';
						do_action( 'admin_notices-sortable' );
						do_settings_sections( 'order-' . $post_type );
						echo '</div>';
					}
				);
			});


			add_action( 'admin_init', function() use( $post_type, $args ){

				$taxonomies = apply_filters( 'sortable/post_type_taxonomies', get_object_taxonomies( $post_type, 'objects' ), $post_type );

				foreach( $taxonomies as $taxonomy ) {

					$terms = get_terms( $taxonomy->name, array( 'orderby' => 'term_order' ) );

//					if( get_option( $taxonomy->name . '_order' ) === 'custom' ) {

						if( count( $terms ) > 1 ) {

							add_settings_section(
								'order_' . $taxonomy->name . '_section',
								$taxonomy->label . ' Ordering',
								function() use( $taxonomy, $terms ){ include 'inc/order-terms.php'; },
								'order-' . $post_type
							);
						}
/*
					} else {

						add_settings_section(
							'order_' . $taxonomy->name . '_section',
							$taxonomy->label . ' Ordering',
							function() use( $taxonomy, $terms ){ include 'inc/order-terms.php'; },
							'order-' . $post_type
						);
					}
*/
				}

				$sort_posts = apply_filters( 'sortable/sort_posts', true, $post_type );

				if( $sort_posts === true ) {

					add_settings_section(
						'order_' . $post_type . '_section',
						$args->label . ' Ordering',
						function() use( $post_type, $args ){ include 'inc/order-posts.php'; },
						'order-' . $post_type
					);
				}


			});


			add_action( 'admin_enqueue_scripts', function() use( $post_type, $args ){
				if( get_current_screen()->id === $post_type . ( $post_type !== 'post' ? null : 's' ) . '_page_order-' . $post_type ) {

					wp_enqueue_script( 'jquery-ui-sortable' );
					wp_enqueue_style( 'sortable-style', plugins_url( 'style.css', __FILE__ ) );
					wp_enqueue_script( 'sortable-init', plugins_url( 'sortable-init.js', __FILE__ ) );
					wp_localize_script( 'sortable-init', 'admin_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
				}
			});


			add_action( 'admin_notices-sortable', function() use( $post_type, $args ){
				if( get_current_screen()->id === $post_type . '_page_order-' . $post_type ) {

					if( isset( $_POST['category'] ) )
						$message = $args->label . ' order successfully saved';

					if( isset( $_POST['save_categories_order'] ) )
						$message = 'Order successfully saved';

					echo isset( $message ) ? '<div id="message" class="updated notice notice-success below-h2"><p>' . $message . '</p></div>' : null;
				}
			});


			/** AJAX functions */

			add_action( 'wp_ajax_select_sortable_terms', function(){ // show posts for selected term

				$term_id = $_POST['term_id'];
				$taxonomy = $_POST['taxonomy'];
				$post_type = $_POST['post_type'];

				$get_posts_args = array(
						'post_type' => $post_type,
						'posts_per_page' => -1,

						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'field' => 'id',
								'terms' => $term_id
							)
						), // end tax_query
						'orderby' => 'menu_order',
						'order' => 'ASC',
					);

				foreach( get_posts( $get_posts_args ) as $post ) {
					include 'inc/post-li.php';
				}
				exit;
			});

			add_action( 'wp_ajax_update_sortable_posts', function(){ // update order of posts

				$post_ids = $_POST['post_ids'];

				$i = 1;
				foreach( $post_ids as $post_id ) {
					wp_update_post( array( 'ID' => $post_id, 'menu_order' => $i++ ) );
				}

				echo 'success';
				exit;
			});

			add_action( 'wp_ajax_update_sortable_terms', function(){ // update order of terms

				global $wpdb;
				$term_ids = $_POST['term_ids'];

				$i = 1;
				foreach( $term_ids as $term_id ) {
					$wpdb->update( $wpdb->terms, array( 'term_order' => $i++ ), array( 'term_id' => $term_id ) );
				}

				exit;
			});

		}
	}

}, 10, 2 );



// add ability to use 'term_order' (for example) as an 'orderby' arguement for get_terms()
add_filter( 'terms_clauses', function( $clauses, $taxonomy, $args ){

	global $wpdb;

	$args['orderby'] = !is_admin() ? $args['orderby'] : 'term_order';

	$default_orderby = array( 'id', 'count', 'name', 'slug', 'term_group', 'none' );

	if( !in_array( $args['orderby'], $default_orderby ) && $wpdb->query( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $wpdb->terms . ' LIKE %s', $args['orderby'] ) ) ) {

		$clauses['orderby'] = 'ORDER BY t.' . $args['orderby'];
	}

	return $clauses;

}, 10, 3 );
