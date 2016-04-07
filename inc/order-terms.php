<?php if( count( $taxonomies ) > 1 ): ?>
<p>Choose a term from the dropdown list and then drag and drop the thumbnails to order them.</p>	
<form action="" method="post" id="save-terms-order">
	<p>
		<select name="taxonomy" id="taxonomies">
			<option <?php selected( !isset( $_POST['taxonomy'] ) ); ?> disabled>&mdash; Choose a set of terms to re-order &mdash;</option>
			<?php foreach( $taxonomies as $taxonomy ): ?>
			<option <?php if( isset( $_POST['taxonomy'] ) ) selected( $_POST['taxonomy'], $taxonomy->name ); ?> value="<?php echo $taxonomy->name; ?>">
				<?php echo $taxonomy->label; ?>
			</option>
			<?php endforeach; ?>
		</select>
	</p>
</form>
<ul class="sortable" id="terms-list">
</ul>
<p class="publishing-action" style="display: none">
	<input type="submit" name="save_terms_order" class="button button-primary" value="Save Categories Order" />
</p>


<?php else: ?>
<?php $taxonomy = $taxonomies[0]; ?>
<p>Drag and drop the thumbnails to order them.</p>
<form action="" method="post" id="save-terms-order">
	<input type="hidden" name="taxonomy" id="taxonomies" value="<?php echo $taxonomy->name; ?>" />
</form>
<ul class="sortable" id="terms-list">
	<?php	foreach( get_terms( $taxonomy->name, array(
		'hide_empty' => false,
		'orderby' => 'term_order',
	) ) as $term ) include 'term-li.php'; ?>

</ul>
<p class="publishing-action">
	<input type="submit" name="save_terms_order" class="button button-primary" value="Save <?php echo $taxonomy->label; ?> Order" />
</p>

<?php endif; ?>
<hr />