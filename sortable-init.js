jQuery(function($) {

	$( '.sortable' ).sortable({
		placeholder: "ui-state-highlight"
	});
	$( '.sortable' ).disableSelection();

	$( 'select#categories option[value]:selected' ).each(function(){

		var term_id = $(this).val();
		var taxonomy = $(this).parent( 'optgroup' ).data( 'taxonomy' );
		var post_type = $(this).parent( 'optgroup' ).data( 'post-type' );

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'select_sortable_terms', term_id : term_id, taxonomy : taxonomy, post_type : post_type }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( '#posts-list' ).html( response );
				$( '.publishing-action' ).show();
			},

			error: function() {

			},

			complete: function() {

			}

		}); // end ajax call

	});

	$( 'select#categories' ).on( 'change', function(){

		var term_id = $(this).val();
		var taxonomy = $(this).find( ':selected' ).parent( 'optgroup' ).data( 'taxonomy' );
		var post_type = $(this).find( ':selected' ).parent( 'optgroup' ).data( 'post-type' );

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'select_sortable_terms', term_id : term_id, taxonomy : taxonomy, post_type : post_type }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( '#posts-list' ).html( response );
				$( '.publishing-action' ).show();
			},

			error: function() {

			},

			complete: function() {

			}

		}); // end ajax call

	});

	$( 'input.button[name="save_category_posts_order"]' ).on( 'click', function(e){

		var post_ids = new Array();
		$.each( $( '[data-post_id]' ), function(){
			post_ids.push( $(this).data( 'post_id' ) );
		});

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'update_sortable_posts', post_ids : post_ids }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( 'form#update-category' ).submit();
			},

			error: function() {
				alert('error');
			},

			complete: function() {

			}

		}); // end ajax call


	});


	$( 'input.button[name="save_categories_order"]' ).on( 'click', function(e){

		var term_ids = new Array();
		$.each( $( '[data-term_id]' ), function(){
			term_ids.push( $(this).data( 'term_id' ) );
		});

		$.ajax({
			url: admin_ajax.url,

			data: ({ action : 'update_sortable_terms', term_ids : term_ids }),

			type: 'POST',

			beforeSend: function() {

			},

			success: function(response) {

				$( 'form#update-categories' ).submit();
			},

			error: function() {
				alert('error');
			},

			complete: function() {

			}

		}); // end ajax call


	});


});