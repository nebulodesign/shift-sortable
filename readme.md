# SHIFT - Sortable

Welcome to the official repository for SHIFT Sortable - a WordPress plugin for use with Nebulo Design's SHIFT theme.

-----------------------

## Features

Using the JavaScript Sortable library, this plugin gives administrators the ability to manually customise the order of posts and terms for displaying within the WordPress templates. Currently, it is possible to order:

- all posts of a designated post type;
- posts that have been assigned to terms/categories belonging to a designated taxonomy;
- all terms of a designated taxonomy.

## How to Use (for developers)

Add `'sortable' => true` when registering a post type or taxonomy. This will add a new submenu item underneath the affected post types. Then, update the `orderby` arguement to affect queries.

### Example 1: posts

Use `menu_order` to return posts in the custom order.

```
$args = array(
	'orderby' => 'menu_order',
);

$posts_array = get_posts( $args );
```

```
function custom_sort_posts( $query ) {
	$query->set( 'orderby', 'menu_order' );
}
add_action( 'pre_get_posts', 'custom_sort_posts' );
```

### Example 2: posts assigned to a term

Use `term_{$id}_order` to return posts in the custom order based on a particular term

```
foreach( get_terms( $taxonomy ) as $term ) {
	$args = array(
		'orderby' => 'term_' . $term->term_id . '_order',
	);

	$posts_array = get_posts( $args );
}
```

```
function custom_sort_posts( $query ) {
	if( is_tax() ) {

		$term = get_queried_object();

		$query->set( 'orderby', 'term_' . $term->term_id . '_order' );
	}
}
add_action( 'pre_get_posts', 'custom_sort_posts' );
```

### Example 3: terms

Use `term_order` to return terms in the custom order

```
$args = array(
	'orderby' => 'term_order',
);

$terms_array = get_terms( $taxonomy, $args );
```

# ROADMAP

[] Delete post term order (meta) data
[] Cater for hierarchical category/tag structures
[] Cater for sorting child posts
[*] DOCUMENTATION
