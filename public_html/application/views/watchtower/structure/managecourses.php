<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php echo $submenu ?>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<fieldset>

<legend>
	<?php echo kohana::lang('structures.list_current_courses')?>
</legend>



<table>

	<tr>
		<th>Corso</td>
		<th>Descrizione</td>		
	</tr>
	
	<? 
		$i = 0;
		foreach ($availablecourses as $installedcourse ) { 
		($i % 2 == 0) ? $class = "alternaterow_1" : $class = "alternaterow_2";
	?>
	<tr class='<?=$class;?>'>
		<td class='center' width='20%'><?=kohana::lang('structures.course_' . $installedcourse . '_name'); ?></td>
		<td><?=kohana::lang('structures.course_' . $installedcourse . '_description'); ?></td>		
	</tr>
	<? 
		$i++;
		} 
	?>	
	
</table>
<br/>
</fieldset>


<br/>

<fieldset>
<legend>
	<?php echo kohana::lang('structures.add_course')?>
</legend>

<?
	
?>	
<div>
	
	<?
		if (count($installablecourses) > 0 )		
		{
	?>
		<table>
		<?	
			$i = 0;
			foreach ($installablecourses as $installablecourse )
			{
				($i % 2 == 0) ? $class = "alternaterow_1" : $class = "alternaterow_2";

	?>		
		<tr class='<?= $class; ?>'>
			<td class='center' width='20%'><?= kohana::lang('structures.course_' . $installablecourse . '_name'); ?></td>
			<td><?=kohana::lang('structures.course_' . $installablecourse . '_description'); ?></td>		
			<td>
			<?= form::open() ?>
			<?= form::hidden('course', $installablecourse) ?>
			<?= form::hidden('structure_id', $structure->id) ?>
			<?=	form::submit( array (
				'id' => 'submit', 
				'name' => 'installcourse',
				'class' => 'button button-medium',			
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ,
				'value' => kohana::lang('global.add')));	
			?>
			</tr>
	<?
		$i++;
		} ?>
	</table>
	<? }
		else
		 {
	?>
	<p class='center'><?= kohana::lang('structures.noinstallablecourses');?></p>	
<? 	 } ?>
</div>
</fieldset>
<br style='clear:both'/>
