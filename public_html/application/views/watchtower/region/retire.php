<div class="pagetitle"><?php echo kohana::lang('ca_retire.retire_pagetitle')?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('ca_retire.retire_helper')?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_Meditation',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<br/>

<?php echo form::open('region/confirm_retire') ?>
<div class="center top10">
	<?php echo kohana::lang( 'ca_retire.retirementdays') ?> &nbsp; 
	<?php echo form::input('days', null);?>
	<?php if (!empty ($errors['days'])) echo "<div class='error_msg'>".$errors['law_name']."</div>";?>
	<?php echo form::submit(array (
		'id'=>'submit',
		'class' => 'button button-small',  
		'name'=>'add', 
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 
		'value'=> kohana::lang('ca_retire.retire')))		?>
</div>
<?php echo form::close() ?>

<br style="clear:both;" />
