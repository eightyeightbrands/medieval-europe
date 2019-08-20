<script type="text/javascript">
screen.orientation.lock('landscape');
</script>

<?= $templateheader; ?>

<div id="background">	  
	<div id="bodyWrapper">
		<div id="content">	
			<?php $message = Session::instance()->get('user_message'); echo $message ?>			
			<?= $content ; ?>			
	
		</div>		
		
	</div>	
</div>

<?= $templatefooter; ?>