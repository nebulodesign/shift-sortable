<?php $selected_category = !isset( $_POST['category'] ) ? null : $_POST['category']; ?>

<p>Choose a gallery from the dropdown list and then drag and drop the thumbnails to order them.</p>	
<form action="" method="post" id="update-category">
	<p>
		<select name="category" id="categories">
			<option <?php echo !$selected_category ? 'selected' : null; ?> disabled>&mdash; Choose a category to re-order &mdash;</option>
			<?php foreach( get_object_taxonomies( $post_type, 'objects' ) as $taxonomy ) : ?>
			<?php $terms = array_filter( array_map( function($term){ return $term->count > 1 ? $term : null; }, get_terms( $taxonomy->name, array( 'orderby' => 'term_order' ) ) ) ); ?>
			<?php if( count( $terms ) > 0 ) : ?>
			<optgroup label="<?php echo $taxonomy->label; ?>" data-taxonomy="<?php echo $taxonomy->name; ?>" data-post-type="<?php echo $post_type; ?>">
				<?php foreach( $terms as $term ) : ?>
				<option <?php echo $selected_category == $term->term_id ? 'selected' : null; ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
				<?php endforeach; ?>
			</optgroup>
			<?php endif; endforeach; ?>
		</select>
	</p>
</form>

<ul class="sortable" id="posts-list">
</ul>

<p class="publishing-action" style="display: none">
	<input type="submit" name="save_category_posts_order" class="button button-primary" value="Save <?php echo $args->label; ?> Order" />
</p>