jQuery(function($) {

	$( '.sortable' ).sortable({
		placeholder: "ui-state-highlight"
	});
	$( '.sortable' ).disableSelection();



	$( 'select#terms' ).on( 'change', function(){

		var term_id = $(this).val();
		var taxonomy = $(this).find( ':selected' ).parent( 'optgroup' ).data( 'taxonomy' );
		var post_type = $(this).find( ':selected' ).parents( 'select' ).data( 'post-type' );

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'sortable/select_posts', term_id : term_id, taxonomy : taxonomy, post_type : post_type }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( '#posts-list' ).html( response );
				$( '#posts-list' ).next( '.publishing-action' ).show();
			},

			error: function() {

			},

			complete: function() {

			}

		}); // end ajax call

	});

	$( 'select#terms option[value]:selected' ).each(function(){

		var term_id = $(this).val();
		var taxonomy = $(this).parent( 'optgroup' ).data( 'taxonomy' );
		var post_type = $(this).parents( 'select' ).data( 'post-type' );

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'sortable/select_posts', term_id : term_id, taxonomy : taxonomy, post_type : post_type }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( '#posts-list' ).html( response );
				$( '#posts-list' ).next( '.publishing-action' ).show();
			},

			error: function() {

			},

			complete: function() {

			}

		}); // end ajax call

	});

	$( 'input.button[name="save_posts_order"]' ).on( 'click', function(e){

		var post_ids = new Array();
		$.each( $( '[data-post_id]' ), function(){
			post_ids.push( $(this).data( 'post_id' ) );
		});

		var term_id = $( 'form#save-posts-order select#terms, form#save-posts-order input#terms' ).val();

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'sortable/save_posts_order', post_ids : post_ids, term_id : term_id }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( 'form#save-posts-order' ).submit();
			},

			error: function() {
				alert('error');
			},

			complete: function() {

			}

		}); // end ajax call

	});

	$( 'select#taxonomies' ).on( 'change', function(){

		var taxonomy = $(this).val();

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'sortable/select_terms', taxonomy : taxonomy }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( '#terms-list' ).html( response );
				$( '#terms-list' ).next( '.publishing-action' ).show();
			},

			error: function() {

			},

			complete: function() {

			}

		}); // end ajax call

	});


	$( 'select#taxonomies option[value]:selected' ).each(function(){

		var taxonomy = $(this).val();

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'sortable/select_terms', taxonomy : taxonomy }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( '#terms-list' ).html( response );
				$( '#terms-list' ).next( '.publishing-action' ).show();
			},

			error: function() {

			},

			complete: function() {

			}

		}); // end ajax call

	});


	$( 'input.button[name="save_terms_order"]' ).on( 'click', function(e){

		var term_ids = new Array();
		$.each( $( '[data-term_id]' ), function(){
			term_ids.push( $(this).data( 'term_id' ) );
		});

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'sortable/save_terms_order', term_ids : term_ids }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( 'form#save-terms-order' ).submit();
			},

			error: function() {

			},

			complete: function() {

			}

		}); // end ajax call


	});


});