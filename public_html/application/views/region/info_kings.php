<?php echo $submenu ?>

<style>
div.kingpic { float:left; }
div.kingpic img { padding: 2px; border: 1px solid #999 }
div.kingdescription { float:left; margin-left:3px; border:0px solid; }
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
<h3>Reggenti - <?php echo kohana::lang($region -> kingdom -> get_name() ) ?> </h3>
<br/>

<?php
foreach ($kings as $king)
{
?>
<div>
<div id='frame_s' style='float:left'>
<?php 
$file = "media/images/characters/".$king->id."_l.jpg";
if ( !file_exists( $file) )
	echo html::image('media/images/characters/aspect/noimage_s.jpg', array('class'=>'charpic_s'));
else
	echo html::image('media/images/characters/' . $king -> id . '_l.jpg', array('class'=>'charpic_s'));
?>
</div>
<div class='kingdescription'>
<b>
<?php 
echo 
kohana::lang('regionview.kingdescription', 
	html::anchor('character/publicprofile/' . $king -> id, $king -> name), 
	Utility_Model::format_date($king -> begin),
	is_null ($king -> end) ? '-' : Utility_Model::format_date($king -> end));
?>
</b>	
<br/>
<?php echo kohana::lang('character.slogan')?>: <i><?php echo $king -> slogan ?></i>
<br/>
<?php
if ( $king->lifestatus == 'dead' ) 
echo 
kohana::lang('regionview.kingdescription2', 		
	Utility_Model::format_date($king -> birthdate),
	is_null ($king -> deathdate) ? kohana::lang('character.unknowndeathdate') : Utility_Model::format_date($king -> deathdate));
else
echo 
kohana::lang('regionview.kingdescription3', Utility_Model::format_date($king -> birthdate)); 	

?>
<br/>
</div>
<div style='clear:both'></div>
</div>
<?php } ?>
