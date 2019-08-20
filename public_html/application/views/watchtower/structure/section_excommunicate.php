<head>
 <script> 
 $(document).ready(function()
 {		
	$("#character").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
  });
 </script>
 </head>
<fieldset>
<legend><?php echo kohana::lang('religion.excommunicateplayer')?></legend>
<div class='center'>
<?php echo form::open() ?>
<?php echo form::hidden('structure_id', $structure -> id) ?>
<div id='helper'><?php echo kohana::lang('religion.excommunicateplayer_helper') ?></div>
<?php echo kohana::lang('religion.excommunicateplayer') ?>&nbsp;
<?php echo form::input( array( 'id' => 'character', 'name'=>'character', 'value' => $form['character'], 'style' => 'width:200px;text-align:left' ) );?>
<br/>
<?php echo kohana::lang('global.reason') ?>
<br/>
<?php echo form::textarea( array( 'name'=>'reason', 'value' => $form['reason'], 'style' => 'width:75%;height:100px;' ) ); ?>
<br/>
<?php echo form::submit( array (
			'id' => 'submit', 
			'name' => 'excommunicate', 
			'class' => 'button button-medium', 	
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('religion.excommunicate'));
?>
<?php echo form::close() ?>
</div>
</fieldset>