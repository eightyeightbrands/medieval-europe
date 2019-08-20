<div class="pagetitle">
<?php echo kohana::lang('structures_castle.editlaw_pagetitle'); ?>
</div>

<?php echo $submenu ?>

<?php echo html::anchor('/castle/laws/'.$structure->id, kohana::lang('structures_castle.viewlaws'))?>
&nbsp;&nbsp;
<?php echo html::anchor('/castle/addlaw/'.$structure->id, kohana::lang('structures.region_addlaw'))?>
<hr/>
<br/>

<?php echo form::open('castle/editlaw') ?>
<?php echo form::hidden('structure_id', $structure->id) ?>
<?php echo form::hidden('law_id', $law->id) ?>
<div><?php echo form::label('law_name', kohana::lang('global.name')) ?></div>
<div><?php echo form::input('law_name', ($form['law_name']),array('style' => 'width:40px'));?></div>
<?php if (!empty ($errors['law_name'])) echo "<div class='error_msg'>".$errors['law_name']."</div>";?>

<br/>
<div><?php echo form::label('law_desc', kohana::lang('global.description')) ?></div>
<div><?php echo form::textarea(array( 'id' => 'law_desc', 'name' => 'law_desc', 'rows' => 10, 'cols' => 60), empty( $form['law_desc']) ? '' : 	$form['law_desc'] )?></div>
<?php if (!empty ($errors['law_desc'])) echo "<div class='error_msg'>".$errors['law_desc']."</div>";?>


<br/>
<?php echo form::submit(	array ('id'=>'submit', 'class' => 'submit', 'name'=>'edit', 'value'=> kohana::lang('global.edit')))		?>
<?php echo form::close() ?>
