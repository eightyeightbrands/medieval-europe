<script type="text/javascript">
$(document).ready(function() 
{ 	
	$('#region').change( function() 
	{	
		if ( this.value == 0 ) 
		{
			alert('Please select a region to watch.');
			return false;
		}	
		this.form.submit();
	})		
});
</script>


<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php echo $submenu ?>

<div id='helperwithpic'>
<div id='locationpic'>
<?php echo html::image('media/images/template/locations/rest_' . $structure -> structure_type -> supertype . '.jpg' ) ?>
</div>
<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('ca_watcharea.helper');?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Watchtower',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
</div>
<div style='clear:both'></div>
</div>

<?php echo html::image('media/images/template/hruler.png')	; ?>

<br/>
<br/>

<?php if ( $iswatchingarea == true ) 
{
?>
<div>
<?php echo form::open(); ?>

<?php echo kohana::lang('ca_watcharea.regiontowatch')?>&nbsp;
<?php echo form::hidden('structure_id', $structure -> id); ?>
<?php 
	$_adjacentregions = array( 0 => 'Select a value' );
	$_adjacentregions += $adjacentregions ;
	echo form::dropdown( 
	array( 	
	'id' => 'region',
	'name' => 'region_id'),
	$_adjacentregions,
	$currentwatchedregion -> id
	);
?>
<?php echo form::close(); ?>
<br/>
<p class='center'><?php echo kohana::lang('ca_watcharea.lookingregion', kohana::lang($currentwatchedregion -> name) ); ?></p>

<?php if ( count($presentchars) == 0 ) 
{
?>
	<br/>
	<p class='center'><?php echo kohana::lang('ca_watcharea.nopeopleinregion');?></p>
<?php 
} 
else 
{
?>
	<br/>
	<table>	
	<th><?php echo kohana::lang('global.name')?></th>
	<th><?php echo kohana::lang('global.kingdom')?></th>
	<th><?php echo kohana::lang('global.actual_actions')?></th>
	<th><?php echo kohana::lang('global.since')?></th>

	<?php 
	$k = 0;

	foreach ( $presentchars as $presentchar ) 
	{
		$class = ( $k %2 == 0 ) ? 'alternaterow_1' : '' ;	
?>

	<tr class='<?php echo $class;?>'>
		<td class='center'><?php echo Character_Model::create_publicprofilelink( null, $presentchar['character_name'])?></td>
		<td class='center'><?php echo kohana::lang($presentchar['kingdom_name'])?></td>
		<td class='center'><?php
			if ( $presentchar['activity'] == 'NOACTION' ) 
				echo kohana::lang('regionview.noactivity'); 
			else
					echo kohana::lang('regionview.' .$presentchar['activity']['action'] .'_descriptivemessage' ); 
			?>
			</td>
		<td class='center'>
		<?php
			if ( $presentchar['activity'] == 'NOACTION' ) 
				echo "-";
			else
				echo Utility_Model::secs2hmstostring(time() - $presentchar['activity']['starttime']);
		?>
		
	</tr>

<?php 
		$k++;
	} 
	?>
	</table>
<?php
}
?>
</div>

<?php
}
else
{
?>
<div class='center'>
<?php
	echo form::open();
	echo form::hidden('structure_id', $structure -> id);
	echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium', 
			'name' => 'startwatch', 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.watch'));
	echo form::close();
?>
</div>
<?php
}
?>

<br style="clear:both;" />

