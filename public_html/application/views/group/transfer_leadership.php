<head>
<script>

$(document).ready(function()
{	
$("#group_charname").autocomplete( {
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
});
</script>
</head>

<div class="pagetitle"><?php echo Kohana::lang('groups.transfer_leadership_pagetitle') ?></div>

<?php echo $submenu ?>
<div class="submenu">
	<?= $secondarymenu; ?>
</div>
<br/>
<div id="helper">
<?php echo Kohana::lang('groups.transfer_leadership_helper', $group -> name); ?>
</div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class="center">
<?php echo form::open(); ?>
<?php echo form::hidden('group_id', $group -> id ); ?>
<?php echo form::label('group_charname', Kohana::lang('groups.newleader'));?>
<?php echo form::input(
	'group_charname',
	($form['group_charname']));?>
<?php if (!empty ($errors['group_charname'])) echo "<div class='error_msg'>".$errors['group_charname']."</div>";?>
<br/>
<br/>
<?php
echo form::submit( array (
	'id' => 'submit', 
	'class' => 'button button-medium', 			
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('groups.transfer'));
?>
<?php echo form::close(); ?>
</div>

<br style="clear:both;" />
