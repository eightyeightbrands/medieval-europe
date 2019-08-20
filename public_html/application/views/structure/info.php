<div class="pagetitle">

<?= $info['structurename']; ?>
	
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<!-- Immagine -->

<div>
<? 

if (!empty($structure -> image)) {  ?>
<div style='float:left;margin-right:1%;width:28%'>
	<?=html::image(
		$structure -> image .'?r='.time(), 
		array(
			'class' => 'border',
			'style' => 'width:185px;height:152px;',
		)); ?>
</div>
<? } 
else
{ 
?>

	<div style='float:left;margin-right:1%;width:28%'>	
		<?
			if ($structure -> structure_type -> subtype == 'church' )
				echo html::image(
					'media/images/template/locations/' . $structure -> structure_type -> supertype . '_' . $structure -> structure_type -> church -> name . '.jpg',
					array('class' => 'border')); 
			else
				echo html::image(
					'media/images/template/locations/' . $structure -> structure_type -> supertype .'.jpg',
					array( 'class' => 'border'));
		?>
	</div>

<? } ?>
	
<div style='float:left;width:70%'>
	
	
<? echo $structure -> getDescription(); ?>	
</div>
<br style='clear:both'/>
</div>

<!-- General Info -->
<br/>

<h5><?= kohana::lang('character.general_stats'); ?></h5>
<div>
<?php 
	echo kohana::lang('global.owner') . ': ';
	if ( $info['obj'] -> character -> loaded == false )
		echo kohana::lang('global.noone' );
	else
	{
		echo Character_Model::create_publicprofilelink( $info['obj'] -> character -> id, $info['obj'] -> character  -> name );	
		echo ' - ' . $info['obj'] -> character -> get_rolename(true ); 
	}
?>
<br/>
<?= kohana::lang('global.condition');?>: <span class='value'><?= $structure -> state; ?></span>%

<br/>

<!-- Messaggio Informativo -->
<br/>

<? if (!empty($structure -> message)) { ?>
	<h5><?= kohana::lang('structures.informativemessage'); ?></h5>
	<?= $structure -> message; ?>
<? } ?>

<!-- Storia --> 
<br/>
<br/>

<!-- Storia del progetto -->


<? if ( isset( $info['kingdomproject']) and $info['kingdomproject']['status'] == 'completed' )
{ 
?>
<h5><?= kohana::lang('kingdomprojects.projecthistoryheader');?></h3>
<?

	kohana::lang('kingdomprojects.projecthistory', 		 
		My_I18n_Model::translate(   $info['kingdomproject']['project'] -> startedby ),
		Utility_Model::format_date( $info['kingdomproject']['project'] -> start ) );

	$elapsed = $info['kingdomproject']['project'] -> end - $info['kingdomproject']['project'] -> start;	
	$elapsedtime = Utility_Model::secs2hmstostring($elapsed);
	
	echo kohana::lang('kingdomprojects.projecthistory2',
		Utility_Model::format_date( $info['kingdomproject']['project'] -> end ),
		$elapsedtime );			
	
}
?>
<? if ( isset( $info['kingdomproject']) ) 
{
?>
&nbsp;
<?php echo kohana::lang('structures.contributors')?>
<br/>
<br/>

<table style='width:50%;margin:auto'>
<?php		
	
	$stats = $info['obj'] -> get_stats( 'workedhours' ); 	
	
	$r = 0;
	if ( count( $stats ) > 0 )
		foreach ( $stats as $stat )		
		{
			$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2' ; 
			echo "<tr class='$class'><td>" . Character_Model::create_publicprofilelink(null, $stat -> spare2) . "</td><td class='right'>"  . $stat -> value . ' ' . kohana::lang('global.hours') .  '</td></tr>';
			$r++;
		}
?>
</table>
<? } ?>

<!-- Info su risorsa -->

<?php
if ( $info['resources']['structuresize'] != '' )
{
?>
<h5>Risorse</h5>
<?= kohana::lang('items.size') ?>: <span class='value'><?= kohana::lang('global.' . $info['resources']['structuresize'] ); ?></span>
<br/>
<?
}
?>
<?php 
if ( isset( $info['resources']['availability'] ) )
	foreach (  $info['resources']['availability'] as $key => $value )
	{
	$status = Structure_Model::get_descriptiveresourcestatus ( $value );
	echo 	
		kohana::lang('items.' . $key . '_name' ) . ' ' . 
			kohana::lang('global.availability') . ": <span class='value'>" . kohana::lang( $status['desc'] ) . '</span><br/>';		
	}

?>
</div>

<br style="clear:both;" />