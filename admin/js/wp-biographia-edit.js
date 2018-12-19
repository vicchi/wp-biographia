(function($) {
	$().ready(function() {
		$('#wp-biographia-admin-meta-biography-override').click(function () {
			$('#wp-biographia-admin-bio-override').toggle ('slow');
			$('#wp-biographia-admin-meta-biography').attr('disabled', $('#wp-biographia-admin-bio-override').attr('checked'));
		});
		
		$('#wp-biographia-admin-meta-title-override').click(function () {
			$('#wp-biographia-admin-title-override').toggle ('slow');
		});
		
		$('#wp-biographia-admin-reload-biography').click(function () {
			$('#wp-biographia-admin-meta-biography').val ($('#wp-biographia-admin-meta-profile-bio').val ());
		});
	});
})(jQuery);