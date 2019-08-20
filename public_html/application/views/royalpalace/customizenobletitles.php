<div class="pagetitle"><?php echo kohana::lang('structures_royalpalace.customizenobletitles_pagetitle') ?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
<?php echo html::anchor('/royalpalace/assign_rolerp/' . $structure -> id, kohana::lang('structures_royalpalace.assignrolerp'),
	array( 'class' => 'selected' )); ?>
&nbsp;
<?php echo html::anchor('/structure/list_roletitles/' . $structure -> id, kohana::lang('structures_royalpalace.listroletitles')); ?>
&nbsp;
<?php echo html::anchor('/royalpalace/customizenobletitles/' . $structure -> id, kohana::lang('structures_royalpalace.customizenobletitles_pagetitle')); ?>
</div>
<br/>

<p><?= kohana::lang('structures_royalpalace.customizenobletitles_helper') ?></p>

<?php
// Elenco dei titoli nobiliari da visualizzare

?>

<table>
	<tr>
		<th><?= kohana::lang('ca_customizenobletitles.original_title') ?></th>
		<th><?= kohana::lang('ca_customizenobletitles.custom_title_m') ?></th>
		<th><?= kohana::lang('ca_customizenobletitles.custom_title_f') ?></th>
		<th><?= kohana::lang('ca_customizenobletitles.image') ?></th>
		<th><?= kohana::lang('ca_customizenobletitles.actions') ?></th>
	</tr>
	
	<? 
	$k = 0;
	foreach ($originaltitles as $originaltitle)
	{ 
		echo form::open_multipart(url::current());
		echo form::hidden('region_id', $structure->region->id );
		echo form::hidden('structure_id', $structure -> id );
		echo form::hidden('originaltitle', $originaltitle );
		
		$class = ($k % 2 == 0) ? 'alternaterow_1' : '';
	?>
	<tr class="<?= $class ?>">
		<td>
			<?= kohana::lang('global.'.$originaltitle.'_m') ?>
		</td>
		
		<td align=center>
			<?
			// Se esiste nell'array dei titoli modificati visualizzo 
			// il titolo modificato altrimenti visualizzo il titolo originale
			if (! empty($modifiedtitles[$originaltitle]['customisedtitle_m']) ) 
			{ 
				$customisedtitle_m_value = $modifiedtitles[$originaltitle]['customisedtitle_m'];
			}
			else
			{
				$customisedtitle_m_value = kohana::lang('global.'.$originaltitle.'_m');
			}
			
			// Visualizzo il campo
			echo form::input
			(
				array
				( 
					'id'=>'customisedtitle_m', 
					'name' => 'customisedtitle_m', 
					'value' => $customisedtitle_m_value,
					'class' => 'input'
				)
			);
			?>
		</td>
		
		<td align=center>
			<?
			// Se esiste nell'array dei titoli modificati visualizzo 
			// il titolo modificato altrimenti visualizzo il titolo originale
			if (! empty($modifiedtitles[$originaltitle]['customisedtitle_f']) ) 
			{ 
				$customisedtitle_f_value = $modifiedtitles[$originaltitle]['customisedtitle_f'];
			}
			else
			{
				$customisedtitle_f_value = kohana::lang('global.'.$originaltitle.'_f');
			}
			
			// Visualizzo il campo
			echo form::input
			(
				array
				( 
					'id'=>'customisedtitle_f',
					'name' => 'customisedtitle_f', 
					'value' => $customisedtitle_f_value,
					'class' => 'input'
				)
			);
			?>
		</td>
		
		<td align=center>
			<?php 
			$role = new Character_Role_Model;
			$role->tag = $originaltitle;
			$role->kingdom_id = $structure->region->kingdom_id;
			echo $role->get_title_image(array('style'=>'margin:5px 0 0 0; width:50px')); ?>
			<div title='<?= kohana::lang('ca_customizenobletitles.uploadhelper')?>' class='upload-file-customnobletitle'>
				<input id='upload_custom_title_img' type='file' name='custom_title_image' />
			</div>
		</td>

		<td>
			<?php 
			echo form::submit( array (
						'id' => 'submit', 
						'class' => 'button-small' , 			
						'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'));
			
			?>
		</td>
	</tr>
	<? echo form::close();
	$k++;
	} ?>
</table>

