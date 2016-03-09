<?php $post_thumbnail_id = apply_filters(
	'sortable/' . $post->post_type . '_post_thumbnail_id',
	has_post_thumbnail( $post->ID ) ? get_post_thumbnail_id( $post->ID ) : null,
	$post->ID ); ?>

<li class="ui-state-default" data-post_id="<?php echo $post->ID; ?>">
	<?php if( $post_thumbnail_id && is_int( $post_thumbnail_id ) && get_post_type( $post_thumbnail_id ) === 'attachment' ) : ?>
	<?php echo wp_get_attachment_image( $post_thumbnail_id, array(100,100) ); ?>
	<?php else: ?>
	<div class="no-image">no image</div>
	<?php endif; ?>
	<p class="wp-caption"><?php echo $post->post_title; ?></p>		
</li>