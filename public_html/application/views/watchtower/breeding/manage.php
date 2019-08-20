<div class="pagetitle"><?php echo kohana::lang("structures_actions.global_info");?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helperwithpic'>
	<div id='locationpic'>
	<?php echo html::image('media/images/template/locations/' . $structure -> structure_type -> type . '.jpg') ?>
	</div>

	<div id='helper'>
	<?php echo kohana::lang('structures_' . $structure -> structure_type -> type .  's.manage_helper') ?>
	</div>
	<br/>
	<p>
	<?php 
		$object = Structure_Type_Model::factory( $structure -> structure_type -> type );
		echo $object::info_breeding( $structure ); 		
	?>
	</p>		
	<div style='clear:both'></div>
</div>

<br/>

<br style="clear:both;" />
