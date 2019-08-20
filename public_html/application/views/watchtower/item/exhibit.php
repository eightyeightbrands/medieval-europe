<head>
<script>
 $(document).ready(function()
 {	
	$("#to").autocomplete( {
	source: "index.php/jqcallback/listallchars/inregion",
	minLength: 2,	
	});	 
 });
</script>
</head>

<div class='pagetitle'><?php echo kohana::lang('charactions.exhibit_scroll') ?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?= kohana::lang('charactions.exhibit_scroll_helper'); ?></div>
<br/>

<div class='center'>

<?php 
echo form::open('/item/exhibit/'.$item->id);

echo form::input( 
	array( 
		'id'=>'to', 
		'name' => 'to', 
		'value' =>  $form['to'],
		'class' => 'input-large')
);
		
if (!empty ($errors['to'])) 
	echo "<div class='error_msg'>".$errors['to']."</div>";
echo form::submit( array( 
	'id' => 'submit', 
	'class' => 'button-medium',
	'value' => Kohana::lang('global.exhibit')) );
echo form::close();
?>

</div>

<br style="clear:both;" />