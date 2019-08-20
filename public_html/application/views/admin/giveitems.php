<head>
<script>

$(document).ready(function()
{		
$("#to_username").autocomplete({
		source: "index.php/jqcallback/listallchars",
		minLength: 2
	});	
});
</script>
</head>

<div class="pagetitle"><?php echo kohana::lang('admin.giveitems')?></div>

<?php echo $submenu ?>

<div id='helper'>
E' possibile da questa pagina assegnare (o togliere, se si mette una quantit&egrave; negativa) oggetti ad un utente, specificando il nome del personaggio.
</div>
<?php echo form::open('/admin/giveitems'); ?>
<?php echo form::hidden('to_username', $form['to_username']) ?>

Personaggio
<?php	echo form::input( array( 'id'=>'to_username', 'name' => 'to_username', 'value' =>  $form['to_username'], 'style'=>'width:300px') ); ?>
<br/>
Oggetto <?php echo form::dropdown('item', $cbitems );?>
<br/>
Quantit&agrave; <?php	echo form::input( array( 'id'=>'quantity', 'name' => 'quantity', 'value' =>  $form['quantity'], 'style'=>'width:40px;text-align:right') ); ?>
<br/>
Causale <?php	echo form::input( array( 'id'=>'reason', 'name' => 'reason', 'value' =>  $form['reason'], 'style'=>'width:500px;') ); ?>
<br/><br/>

<center>
<?php echo form::submit( 
	array( 
	'id' => 'submit', 
	'class' => 'button button-medium', 
	'onclick' => 
	'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.edit_submit')));?>
</center>
<?php echo form::close() ?>

<br style="clear:both;" />
