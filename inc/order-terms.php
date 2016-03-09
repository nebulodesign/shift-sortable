<p>Drag and drop the thumbnails to order them.</p>
<form action="" method="post" id="update-categories">
	<input type="hidden" name="save_categories_order" value="save_categories_order" />
</form>
<ul class="sortable">
<?php foreach( $terms as $term ) : ?>
	<?php $term_thumbnail_id = apply_filters( 'sortable/' . $taxonomy->name . '_term_thumbnail_id', null, $term->term_id ); ?>
	<li class="ui-state-default" data-term_id="<?php echo $term->term_id; ?>">
		<?php if( $term_thumbnail_id && is_int( $term_thumbnail_id ) && get_post_type( $term_thumbnail_id ) === 'attachment' ) : ?>
		<?php echo wp_get_attachment_image( $term_thumbnail_id, array(100,100) ); ?>
		<?php else: ?>
		<div class="no-image"><?php echo $term->name; ?></div>
		<?php endif; ?>
		<p class="wp-caption"><?php echo $term->name; ?></p>
	</li>
<?php endforeach; ?>
</ul>
<p class="publishing-action">
	<input type="submit" name="save_categories_order" class="button button-primary" value="Save <?php echo $taxonomy->label; ?>">
</p>
<hr />