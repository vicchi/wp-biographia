(function($) {
	$().ready(function() {  
		$('#wp-biographia-user-role-add').click(function() {  
	  		return !$('#wp-biographia-enabled-user-roles option:selected').remove().appendTo('#wp-biographia-excluded-user-roles');  
	 	});  
	 	$('#wp-biographia-user-role-rem').click(function() {
	  		return !$('#wp-biographia-excluded-user-roles option:selected').remove().appendTo('#wp-biographia-enabled-user-roles');
	 	});  

		$('#wp-biographia-user-profile-add').click(function() {  
	  		return !$('#wp-biographia-visible-profiles option:selected').remove().appendTo('#wp-biographia-hidden-profiles');  
	 	});  
	 	$('#wp-biographia-user-profile-rem').click(function() {
	  		return !$('#wp-biographia-hidden-profiles option:selected').remove().appendTo('#wp-biographia-visible-profiles');
	 	});  

		$('#wp-biographia-user-post-add').click(function() {  
	  		return !$('#wp-biographia-enabled-post-users option:selected').remove().appendTo('#wp-biographia-suppressed-post-users');  
	 	});  

	 	$('#wp-biographia-user-post-rem').click(function() {  
	  		return !$('#wp-biographia-suppressed-post-users option:selected').remove().appendTo('#wp-biographia-enabled-post-users');  
	 	});  

	 	$('#wp-biographia-user-page-add').click(function() {  
	  		return !$('#wp-biographia-enabled-page-users option:selected').remove().appendTo('#wp-biographia-suppressed-page-users');  
	 	});  

	 	$('#wp-biographia-user-page-rem').click(function() {  
	  		return !$('#wp-biographia-suppressed-page-users option:selected').remove().appendTo('#wp-biographia-enabled-page-users');  
	 	});  

		$('#wp-biographia-category-add').click(function() {
			return !$('#wp-biographia-enabled-categories option:selected').remove().appendTo('#wp-biographia-excluded-categories');
		});

		$('#wp-biographia-category-rem').click(function() {
			return !$('#wp-biographia-excluded-categories option:selected').remove().appendTo('#wp-biographia-enabled-categories');
		});
		
		$('#wp-biographia-display-front-posts').click(function() {
			$('#wp-biographia-front-bio-wrapper').toggle('slow');
		});

		$('#wp-biographia-display-posts').click(function() {
			$('#wp-biographia-posts-bio-wrapper').toggle('slow');
		});
		
		$('#wp-biographia-display-archives-posts').click(function() {
			$('#wp-biographia-archives-bio-wrapper').toggle('slow');
		});

		$('#wp-biographia-display-author-archives-posts').click(function() {
			$('#wp-biographia-author-bio-wrapper').toggle('slow');
		});

		$('#wp-biographia-display-category-archives-posts').click(function() {
			$('#wp-biographia-category-bio-wrapper').toggle('slow');
		});

		$('#wp-biographia-display-date-archives-posts').click(function() {
			$('#wp-biographia-date-bio-wrapper').toggle('slow');
		});

		$('#wp-biographia-display-tag-archives-posts').click(function() {
			$('#wp-biographia-tag-bio-wrapper').toggle('slow');
		});

		$('[id^=wp-biographia-custom-display]').click(function() {
			var src_id = $(this).attr ('id');
			var post_type = src_id.split('wp-biographia-custom-display-').slice(1).join('');
			var dst_id = '#wp-biographia-custom-' + post_type + '-bio-wrapper';
			$(dst_id).toggle('slow');
		});

		$('#wp-biographia-display-pages').click(function() {
			$('#wp-biographia-pages-bio-wrapper').toggle('slow');
		});

		$('#wp-biographia-display-feed').click(function() {
			$('#wp-biographia-feed-bio-wrapper').toggle('slow');
		});

		$('#wp-biographia-display-archives-posts').click(function () {
			$('#wp-biographia-archive-posts-container').toggle('slow');
		});
		
		$('#wp-biographia-content-icons').click(function() {
			$('#wp-biographia-icon-container').toggle(this.checked);
		});

		$('#wp-biographia-content-alt-icons').click(function () {
			if (this.checked) {
				$('#wp-biographia-content-icon-url').removeAttr('disabled');
			}
			else {
				$('#wp-biographia-content-icon-url').attr('disabled', true);
			}
		});
		
		$('form').submit(function() {
			$('#wp-biographia-enabled-user-roles option').each(function(i) {  
				$(this).attr("selected", "selected");  
			});  
			$('#wp-biographia-excluded-user-roles option').each(function(i) {  
				$(this).attr("selected", "selected");  
			});  

			$('#wp-biographia-visible-profiles option').each(function(i) {  
				$(this).attr("selected", "selected");  
			});  
			$('#wp-biographia-hidden-profiles option').each(function(i) {  
				$(this).attr("selected", "selected");  
			});  

			$('#wp-biographia-enabled-post-users option').each(function(i) {  
				$(this).attr("selected", "selected");  
			});  
			$('#wp-biographia-suppressed-post-users option').each(function(i) {  
				$(this).attr("selected", "selected");  
			});  

			$('#wp-biographia-enabled-page-users option').each(function(i) {  
				$(this).attr("selected", "selected");  
			});  
			$('#wp-biographia-suppressed-page-users option').each(function(i) {  
				$(this).attr("selected", "selected");
			});  
			
			$('#wp-biographia-enabled-categories option').each(function(i) {
				$(this).attr("selected", "selected");
			})
			$('#wp-biographia-excluded-categories option').each(function(i) {
				$(this).attr("selected", "selected");
			})
		});
		
		$('#wp-biographia-pick-border-color,#wp-biographia-pick-background-color').click(function() {
			var src_id = $(this).attr ('id');
			switch (src_id) {
				case 'wp-biographia-pick-border-color':
					$('#wp-biographia-border-color-picker').show();
					break;
				case 'wp-biographia-pick-background-color':
					$('#wp-biographia-background-color-picker').show();
					break;
			}
			return false;
		});

		var background_div = $('#wp-biographia-background-color-picker')[0];
		var border_div = $('#wp-biographia-border-color-picker')[0];
		var background_picker;
		var border_picker;
		
		if (background_div) {
			background_picker = $.farbtastic('#wp-biographia-background-color-picker', function(color){
				color = color.toUpperCase ();
				background_picker.setColor(color);
				$('#wp-biographia-background-color').val(color);
			});
			background_picker.setColor($('#wp-biographia-background-color').val());
		}
		
		if (border_div) {
			border_picker = $.farbtastic('#wp-biographia-border-color-picker', function(color){
				color = color.toUpperCase ();
				border_picker.setColor(color);
				$('#wp-biographia-border-color').val(color);
			});
			border_picker.setColor($('#wp-biographia-border-color').val());
		}

		$(document).mousedown(function() {
			$('#wp-biographia-border-color-picker,#wp-biographia-background-color-picker').each(function () {
				var display = $(this).css('display');
				if (display == 'block') {
					$(this).fadeOut(2);
				}
			});
		});
	});	
})(jQuery);

