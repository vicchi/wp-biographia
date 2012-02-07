(function($) {
	$().ready(function() {  
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
		});

	});	
})(jQuery);

