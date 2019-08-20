<?php echo $submenu ?>

<style>
div.vassalpic { float:left; }
div.vassalpic img { padding: 2px; border: 1px solid #999 }
div.vassaldescription { float:left; margin-left:3px; border:0px solid; }
</style>

<?php 
if( $region -> capital )
	echo html::anchor('/region/history/kings/', 'Reggenti') . 	'&nbsp;&nbsp;';
echo html::anchor('/region/history/vassals/', 'Vassalli');
?>
		
<div id='helper'>
<?php echo kohana::lang('regionview.history_helper') ?>
</div>		
<br/>
<h3>Vassalli - Regione <?php echo kohana::lang($region -> name) ?> </h3>
<br/>

<?php
foreach ($vassals as $vassal)
{
?>
<div>
<div id='frame_s' style='float:left'>
<?php 
$file = "media/images/characters/".$vassal->id."_l.jpg";
if ( !file_exists( $file) )
	echo html::image('media/images/characters/aspect/noimage_s.jpg', array('class'=>'charpic_s'));
else
	echo html::image('media/images/characters/' . $vassal -> id . '_l.jpg', array('class'=>'charpic_s'));
?>
</div>
<div class='vassaldescription'>
<b>
<?php 
echo 
kohana::lang('regionview.vassaldescription', 
	html::anchor('character/publicprofile/' . $vassal -> id, $vassal -> name), 
	Utility_Model::format_date($vassal -> begin),
	is_null ($vassal -> end) ? '-' : Utility_Model::format_date($vassal -> end));
?>
</b>	
<br/>
<?php echo kohana::lang('character.slogan')?>: <i><?php echo $vassal -> slogan ?></i>
<br/>
<?php
if ( $vassal->lifestatus == 'dead' ) 
echo 
kohana::lang('regionview.vassaldescription2', 		
	Utility_Model::format_date($vassal -> birthdate),
	is_null ($vassal -> deathdate) ? kohana::lang('character.unknowndeathdate') : Utility_Model::format_date($vassal -> deathdate));
else
echo 
kohana::lang('regionview.vassaldescription3', Utility_Model::format_date($vassal -> birthdate)); 	

?>
<br/>
</div>
<div style='clear:both'></div>
</div>
<?php } ?>
