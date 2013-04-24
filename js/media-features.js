/*
 * Add Bulk actions 
 * @since 0.2
 */

var attachCategories;
(function($){
	attachCategories = {
		af_val: '',
		open : function(af_name, af_val) {
			var st = document.documentElement.scrollTop || $(document).scrollTop();

			if ( af_name && af_val ) {
				$('#affected').attr('name', af_name).val(af_val);
			}
			$('#attach-categories').show().draggable({
				handle: '#attach-categories-head'
			}).css({'top':st + 50 + 'px','left':'50%','marginLeft':'-250px'});

			$('#attach-categories-input').focus().keyup(function(e){
				if (e.which == 27) { attachCategories.close(); } // close on Escape
			});
			
			attachCategories.send( af_val );
			
			attachCategories.af_val = af_val;

			return false;
		},

		close : function() {
			$('#attach-categories-inside').html('');
			$('#attach-categories').draggable('destroy').hide();
		},

		send : function( post_id ) {
			var post = {
				post_id: post_id,
				action: 'get_media_attach_categories',
				_ajax_nonce: $('#_ajax_nonce').val(),
				post_type: 'attachment'
			};

			$.ajax({
				type : 'POST',
				url : ajaxurl,
				data : post,
				success : function(x) { attachCategories.show(x); },
				error : function(r) { attachCategories.error(r); }
			});
		},

		show : function(x) {

			if ( typeof(x) == 'string' ) {
				this.error({'responseText': x});
				return;
			}

			var r = wpAjax.parseAjaxResponse(x);

			if ( r.errors ) {
				this.error({'responseText': wpAjax.broken});
			}
			r = r.responses[0];
			$('#attach-categories-inside').html(r.data);
			$('#taxonomy-media_category').tabs();
			
		},

		error : function(r) {
			var er = r.statusText;

			if ( r.responseText ) {
				er = r.responseText.replace( /<.[^<>]*?>/g, '' );
			}
			if ( er ) {
				$('#attach-categories-inside').html(er);
			}
		},
		
		get_selected_posts : function() {
			var post_ids = '';
			$('#the-list .check-column input:checked').each(function(){
				if ( '' == post_ids )
					post_ids = $(this).val();
				else
					post_ids = post_ids + ',' + $(this).val();
				
			});
			return post_ids;
		},
		
		get_selected_terms : function() {
			var term_ids = '';
			$('#taxonomy-media_category input:checked').each(function(){
				if ( '' == term_ids )
					term_ids = $(this).val();
				else
					term_ids = term_ids + ',' + $(this).val();
				
			});
			return term_ids;
		},
		
		save : function() {
			//console.log(attachCategories.af_val);
			if ( undefined == attachCategories.af_val )
				$('#media_category_save_categories_posts_ids').val( attachCategories.get_selected_posts );
			else
				$('#media_category_save_categories_posts_ids').val( attachCategories.af_val );
				
			$('#media_category_save_categories_terms_ids').val( attachCategories.get_selected_terms );
			return true;
			
		}
	};

	$(document).ready(function() {
		$('#attach-categories-submit').click(function(e) {
			if ( '' == $('#attach-categories-inside').html() )
				e.preventDefault();
			else
				return attachCategories.save();
		});
		$('#attach-categories-do').click( attachCategories.open );
		$( '#attach-categories-close' ).click( attachCategories.close );
		$('#doaction, #doaction2').click(function(e){
			$('select[name^="action"]').each(function(){
				if ( $(this).val() == 'attach' ) {
					e.preventDefault();
					attachCategories.open();
				}
			});
		});
	});
})(jQuery);

