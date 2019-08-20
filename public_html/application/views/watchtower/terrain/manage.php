<div class="pagetitle"><?php echo kohana::lang("structures_actions.global_info");?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?
if ( $structure -> attribute1 == 0 )
	$info = kohana::lang( 'structures_terrain.terrainisuncultivated');
		
if ( $structure -> attribute1 == 1 )
{
	$growaction = ORM::factory('character_action') ->
		where ( array( 
			'action' => 'growfield', 
			'param1' => $structure -> id,
			'status' => 'running' ) ) -> find();
			
	$info = kohana::lang( 'structures_terrain.terrainisgrowing', 	Utility_Model::countdown($growaction -> endtime ) ); 
}

if ( $structure -> attribute1 == 2 )
{
	$info = kohana::lang( 'structures_terrain.terrainisripe' );
}
?>

<div id='helperwithpic'>
	<div id='locationpic'>
	<?php echo html::image('media/images/template/locations/terrain.jpg') ?>
	</div>

	<div id='helper'>
	<?php echo kohana::lang('structures_terrain.manage_helper') ?>
	</div>
	
	<p class='center'>
		<b><?php echo $info ?></b>
	</p>
	
	<br style="clear:both;" />
</div>

<br/>

<?= $section_description; ?>

<br style="clear:both;" />
