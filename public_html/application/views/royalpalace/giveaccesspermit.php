<head>
<script>

 $(document).ready(function()
 {	
	$("input#character").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
	
});	
</script>
</head>

<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?php echo kohana::lang('diplomacy.giveaccesspermit_helper') ?>
</div>

<div class='submenu'>
<?php echo html::anchor('royalpalace/diplomacy/' . $structure->id, kohana::lang('structures_royalpalace.submenu_diplomacy'));?>
&nbsp;
<?php echo html::anchor('royalpalace/giveaccesspermit/' . $structure->id, kohana::lang('structures_royalpalace.submenu_giveaccesspermit'), array('class' => 'selected' ))?>
</div>

<br/>	

<fieldset>
<div class='center'>
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>

<br/>

<?php
	echo form::label('character', Kohana::lang('global.name')) . '&nbsp;'; 
	echo form::input( array( 'id'=>'character', 'name' => 'character', 'value' =>  $form['character'], 'style'=>'width:250px') );
?>



<?php 	

	echo form::submit( array( 'id' => 'submit', 'name' => 'declare', 'class'=> 'button button-medium', 
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
	'value' => Kohana::lang('global.assign')));
	
	echo form::close(); 
?>

</div>
</fieldset>

<br style="clear:both;" />
