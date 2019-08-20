<script>

 $(document).ready(function()
 {		
	
	$("#to").autocomplete({
		source: "index.php/jqcallback/listallchars",
		minLength: 2,		
	});		
		
 });
</script>
<div class= 'pagetitle'><?php echo kohana::lang('charactions.senddoubloons'); ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<fieldset class='center'>
<?php 
	
	echo form::open('/item/senddoubloons');
	
	echo kohana::lang('charactions.senditem_totalitemsmessage', $doubloons, kohana::lang('items.doubloon_name'));
	echo '<br/><br/>';
	echo kohana::lang('charactions.senditem_sendnormalitem');
	
	echo form::input( array( 
		'id' => 'quantity', 
		'name' => 'quantity', 
		'value' => 0,
		'class' => 'input-xsmall',
		'style'=>'text-align:right') );	
	
	echo '&nbsp;' . kohana::lang('items.doubloon_name');
	
	if (!empty ($errors['quantity'])) 
		echo "<div class='error_msg'>".$errors['quantity']."</div>";
	
	echo '&nbsp;' . kohana::lang('global.to') . '&nbsp;' ;
	echo form::hidden('recipient_id', $recipient -> id );
	echo "<span class='value'>" . $recipient -> name . '</span>';
		
	echo '<br/><br/>';		
	
	echo 
		"<div style='text-align:center'>" .
		form::submit( array( 'id' => 'senditem', 'class' => 'button button-small',  'value' => Kohana::lang('global.send')) )."</div>";
	
	echo form::close();
?>
</fieldset>
<br style='clear:both'/>	
