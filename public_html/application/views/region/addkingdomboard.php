<div class="pagetitle"><?php echo kohana::lang('kingdomforum.addboard')?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>
<div id='breadcrumb'>
<?php echo html::anchor('region/kingdomboards/' . $kingdom -> name, 
	kohana::lang('kingdomforum.forumtitle', kohana::lang($kingdom -> name))) . ' > ' . kohana::lang('kingdomforum.addboard');?>
</div>
<br/>

<?php echo form::open();?>
<?php echo form::hidden('kingdom_id', $kingdom -> id); ?>
<div><?php echo form::label('name', kohana::lang('global.name')) ?> &nbsp; <?php 
	echo form::input(
	array( 
		'id' => 'name', 
		'name' => 'name', 
		'value' => $form['name'], 
		'class' => 'input-xxlarge',
		),
	(empty( $form['name']) ? '' : $form['name'])
	);?>
</div>
<?php if (!empty ($errors['name'])) echo "<div class='error_msg'>".$errors['name']."</div>";?>
	
<div><?php echo form::label('boarddescription', kohana::lang('global.description')) ?>
	<?php 
	echo form::textarea(array( 
		'id' => 'boarddescription', 
		'name' => 'boarddescription', 
		'rows' => 3, 
		'cols' => 90),
		(empty( $form['boarddescription']) ? '' : $form['boarddescription']));	
	?>
	<?php if (!empty ($errors['boarddescription'])) echo "<div class='error_msg'>".$errors['boarddescription']."</div>";?>
</div>

<?php if (!empty ($errors['name'])) echo "<div class='error_msg'>".$errors['name']."</div>";?>

<br/>
<div class='center'>
	<?php echo form::submit( 
		array( 
			'id' => 'submit',  
			'name' => 'save', 
			'class' => 'button button-small', 		
			'value' => kohana::lang('global.save') )) ;
	?>	
</div>

<?php echo form::close() ?>

<br style='clear:both'/>
