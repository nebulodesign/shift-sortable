<?php if( !empty( $taxonomies ) ): ?>
<p>Choose a term from the dropdown list and then drag and drop the thumbnails to order them.</p>	
<form action="" method="post" id="save-posts-order">
	<p>
		<select name="term_id" id="terms" data-post-type="<?php echo $post_type; ?>">
			<option <?php selected( !isset( $_POST['term_id'] ) ); ?> disabled>&mdash; Choose a category to re-order &mdash;</option>
			<option <?php if( isset( $_POST['term_id'] ) ) selected( $_POST['term_id'], 'all' ); ?> value="all">All <?php echo $args->label; ?></option>
			<?php foreach( $taxonomies as $taxonomy ): ?>
			<?php $terms = array_filter( get_terms( $taxonomy->name, array( 'orderby' => 'term_order' ) ), function($term){ return ( $term->count > 1 ); } ); ?>
			<?php if( count( $terms ) > 0 ) : ?>
			<optgroup label="<?php echo $taxonomy->label; ?>" data-taxonomy="<?php echo $taxonomy->name; ?>">
				<?php foreach( $terms as $term ) : ?>
				<option <?php if( isset( $_POST['term_id'] ) ) selected( $_POST['term_id'], $term->term_id ); ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
				<?php endforeach; ?>
			</optgroup>
			<?php endif; endforeach; ?>
		</select>
	</p>
</form>
<ul class="sortable" id="posts-list">
</ul>
<p class="publishing-action" style="display: none">
	<input type="submit" name="save_posts_order" class="button button-primary" value="Save <?php echo $args->label; ?> Order" />
</p>

<?php else: ?>
<p>Drag and drop the thumbnails to order them.</p>	
<form action="" method="post" id="save-posts-order">
	<input type="hidden" name="term_id" id="terms" value="all" data-post-type="<?php echo $post_type; ?>" />
</form>
<ul class="sortable" id="posts-list">
	<?php	foreach( get_posts( array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	) ) as $post ) include 'post-li.php'; ?>
</ul>
<p class="publishing-action">
	<input type="submit" name="save_posts_order" class="button button-primary" value="Save <?php echo $args->label; ?> Order" />
</p>
<?php endif; ?>

<hr />