<div class="pagetitle"><?php echo Kohana::lang('structures_castle.addlaw_pagetitle') ?></div>

<?php echo $submenu ?>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class='submenu'>
<?php echo html::anchor('/castle/laws/'.$structure->id, kohana::lang('structures_castle.viewlaws'))?>
&nbsp;&nbsp;
<?php echo html::anchor('/castle/addlaw/'.$structure->id, kohana::lang('structures.region_addlaw'))?>
</div>

<br/>

<?php echo form::open('castle/addlaw') ?>
<?php echo form::hidden('structure_id', $structure->id) ?>
<div><?php echo form::label('law_name', kohana::lang('global.name')) ?></div>
<div><?php echo form::input('law_name', ($form['law_name']),array('style' => 'width:40px'));?></div>
<?php if (!empty ($errors['law_name'])) echo "<div class='error_msg'>".$errors['law_name']."</div>";?>
<br/>
<div><?php echo form::label('law_desc', kohana::lang('global.description')) ?></div>
<div><?php echo form::textarea(array( 'id' => 'law_desc', 'name' => 'law_desc', 'rows' => 10, 'cols' => 80), empty( $form['law_desc']) ? '' : 	$form['law_desc'] )?></div>
<?php if (!empty ($errors['law_desc'])) echo "<div class='error_msg'>".$errors['law_desc']."</div>";?>
<br/>
<?php echo form::submit(	array ('id'=>'submit', 'class' => 'submit', 'name'=>'add', 'value'=> kohana::lang('global.proclaim')))		?>
<?php echo form::close() ?>
