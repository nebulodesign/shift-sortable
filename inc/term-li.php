<?php $term_thumbnail_id = apply_filters(
	'sortable/' . $taxonomy->name . '_term_thumbnail_id',
	null,
	$term->term_id ); ?>

<li class="ui-state-default" data-term_id="<?php echo $term->term_id; ?>">
	<?php if( $term_thumbnail_id && is_int( $term_thumbnail_id ) && get_post_type( $term_thumbnail_id ) === 'attachment' ) : ?>
	<?php echo wp_get_attachment_image( $term_thumbnail_id, array(100,100) ); ?>
	<?php else: ?>
	<div class="no-image">no image</div>
	<?php endif; ?>
	<p class="wp-caption"><?php echo $term->name; ?></p>		
</li>