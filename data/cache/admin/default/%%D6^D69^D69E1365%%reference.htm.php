<?php /* Smarty version 2.6.18, created on 2014-08-26 03:48:10
         compiled from reference.htm */ ?>
<script>
	(function($) {
		$(window.parent.frames["topFrame"].document).find( "#website-options,#country-options").bind("change", function() {
			var frame_url = $("#frame_url").val() + $(this).val();
			window.location = frame_url;
		});
	})(jQuery);
</script>