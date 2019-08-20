<div class="pagetitle"><?php echo Kohana::lang("character.char_changeattributes")?></div>

<?php echo $submenu ?>

<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('character.change_attributes_helper', $sum);?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_CharCreation',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
	
<br/>

<?php echo form::open(url::current()); ?>

<fieldset>
<legend><?php echo Kohana::lang('character.create_charstr')?></legend>
<div id='helper'><?php echo Kohana::lang('character.create_info_charstr');?></div>
<?php echo form::input('str', ($form['str']),'style="width:30px" ');?>
</fieldset>

<fieldset>
<legend><?php echo Kohana::lang('character.create_chardex')?></legend>
<div id='helper'><?php echo Kohana::lang('character.create_info_chardex');?></div>
<?php echo form::input('dex', ($form['dex']),'style="width:30px" ');?>
</fieldset>

<fieldset>
<legend><?php echo Kohana::lang('character.create_charintel')?></legend>
<div id='helper'><?php echo Kohana::lang('character.create_info_charint');?></div>
<?php echo form::input('intel', ($form['intel']),'style="width:30px" ');?>
</fieldset>

<fieldset>
<legend><?php echo Kohana::lang('character.create_charcost')?></legend>
<div id='helper'><?php echo Kohana::lang('character.create_info_charcost');?></div>
<?php echo form::input('cost', ($form['cost']),'style="width:30px" ');?>
</fieldset>

<fieldset>
<legend><?php echo Kohana::lang('character.create_charcar')?></legend>
<div id='helper'><?php echo Kohana::lang('character.create_info_charcar');?></div>
<?php echo form::input('car', ($form['car']),'style="width:30px" ');?>
</fieldset>

<br/>
<center>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'submit', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
<?php echo form::close(); ?>
</center>
<br style='clear:both'/>