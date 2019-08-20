<script type="text/javascript">

$(document).ready(function() 
{ 
	$(window).on("beforeunload", function() { 
		Cookies.set('chatvisible', 'none');	
	});
});
</script>

<?php $chat -> printChat(); ?>
<br style='clear:both' />

