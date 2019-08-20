<style>
div.chartcontainer
{
width:340px;
height:240px;
border:1px solid #999;
}
</style>


<div class='pagetitle'><?php echo kohana::lang('charts.statisticskingdom_pagetitle', 	kohana::lang($character -> region -> kingdom -> get_name()  ) ) ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<center><h3><?php echo kohana::lang('regionview.stats_kingdomownedregions')?></h3></center>

<br/>

<div>
	<div class='chartcontainer' style='float:left'>
	<?php echo $chartinfo['kingdomownedregions_w']['html']; ?>
	</div>

	<div class='chartcontainer' style='float:right'>
	<?php echo $chartinfo['kingdomownedregions_m']['html']; ?>
	</div>

	<div style='clear:both'></div>
</div>

<br/>

<center>

<div style='width:100%'>

<i><?php echo kohana::lang('regionview.stats_minkingdomownedregions', $chartinfo['kingdomownedregions_w']['min'][0] -> param1)?>

<?php 
if ( $chartinfo['kingdomownedregions_w']['min'][0]-> param3 != 0 )
	echo html::anchor( 'character/publicprofile/' . $chartinfo['kingdomownedregions_w']['min'][0]-> param3 , $chartinfo['kingdomownedregions_w']['min'][0]-> param2 );
else
	echo $chartinfo['kingdomownedregions_w']['min'][0]-> param2;
?> 
</b><?php echo kohana::lang('regionview.stats_reachedon')?><b> <?php echo Utility_Model::format_date ( $chartinfo['kingdomownedregions_w']['max'][0]-> period , "M" ) ?> </b><br/>

<i><?php echo kohana::lang('regionview.stats_maxkingdomownedregions', $chartinfo['kingdomownedregions_w']['max'][0] -> param1)?>

<?php 
if ( $chartinfo['kingdomownedregions_w']['max'][0]-> param3 != 0 )
	echo html::anchor( 'character/publicprofile/' . $chartinfo['kingdomownedregions_w']['max'][0]-> param3 , $chartinfo['kingdomownedregions_w']['max'][0]-> param2 );
else
	echo $chartinfo['kingdomownedregions_w']['max'][0]-> param2;
?> 
</b><?php echo kohana::lang('regionview.stats_reachedon')?><b> <?php echo Utility_Model::format_date ( $chartinfo['kingdomownedregions_w']['max'][0]-> period , "M" ) ?> </b><br/>
</i>
</div>
</center>

<br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<center><h3><?php echo kohana::lang('regionview.stats_kingdompopulation')?></h3></center>

<br/>

<div>
	<div class='chartcontainer' style='float:left'>
	<?php echo $chartinfo['kingdompopulation_w']['html']; ?>
	</div>

	<div class='chartcontainer' style='float:right'>
	<?php echo $chartinfo['kingdompopulation_m']['html']; ?>
	</div>

	<div style='clear:both'></div>

</div>

<br/>

<center>

<div style='width:100%'>

<i><?php echo kohana::lang('regionview.stats_minkingdompopulation', $chartinfo['kingdompopulation_w']['min'][0] -> param1)?>

<?php 
if ( $chartinfo['kingdompopulation_w']['min'][0]-> param3 != 0 )
	echo html::anchor( 'character/publicprofile/' . $chartinfo['kingdompopulation_w']['min'][0]-> param3 , $chartinfo['kingdompopulation_w']['min'][0]-> param2 );
else
	echo $chartinfo['kingdompopulation_w']['min'][0]-> param2;
?> 

</b><?php echo kohana::lang('regionview.stats_reachedon')?> <b> <?php echo Utility_Model::format_date ( $chartinfo['kingdompopulation_w']['max'][0]-> period , "M" ) ?> </b><br/>

<i><?php echo kohana::lang('regionview.stats_maxkingdompopulation', $chartinfo['kingdompopulation_w']['max'][0] -> param1)?>

<?php 
if ( $chartinfo['kingdompopulation_w']['max'][0]-> param3 != 0 )
	echo html::anchor( 'character/publicprofile/' . $chartinfo['kingdompopulation_w']['max'][0]-> param3 , $chartinfo['kingdompopulation_w']['max'][0]-> param2 );
else
	echo $chartinfo['kingdompopulation_w']['max'][0]-> param2;
?> 
</b><?php echo kohana::lang('regionview.stats_reachedon')?> <b> <?php echo Utility_Model::format_date ( $chartinfo['kingdompopulation_w']['max'][0]-> period , "M" ) ?> </b><br/>
</i>
</div>
</center>

<br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<center><h3><?php echo kohana::lang('regionview.stats_kingdomheritage')?></h3></center>

<br/>

<div>
	<div class='chartcontainer' style='float:left'>
	<?php echo $chartinfo['kingdomheritage_w']['html']; ?>
	</div>

	<div class='chartcontainer' style='float:right'>
	<?php echo $chartinfo['kingdomheritage_m']['html']; ?>
	</div>

	<div style='clear:both'></div>

</div>

<br/>

<center>

<div style='width:100%'>

<i><?php echo kohana::lang('regionview.stats_minkingdomheritage', $chartinfo['kingdomheritage_w']['max'][0] -> param1)?>


<?php 
if ( $chartinfo['kingdomheritage_w']['min'][0]-> param3 != 0 )
	echo html::anchor( 'character/publicprofile/' . $chartinfo['kingdomheritage_w']['min'][0]-> param3 , $chartinfo['kingdomheritage_w']['min'][0]-> param2 );
else
	echo $chartinfo['kingdomheritage_w']['min'][0]-> param2;
?> 
</b><?php echo kohana::lang('regionview.stats_reachedon')?> <b> <?php echo Utility_Model::format_date ( $chartinfo['kingdomheritage_w']['max'][0]-> period , "M" ) ?> </b><br/>
<i><?php echo kohana::lang('regionview.stats_minkingdomheritage', $chartinfo['kingdomheritage_w']['min'][0] -> param1)?>

<?php 
if ( $chartinfo['kingdomheritage_w']['max'][0]-> param3 != 0 )
	echo html::anchor( 'character/publicprofile/' . $chartinfo['kingdomheritage_w']['max'][0]-> param3 , $chartinfo['kingdomheritage_w']['max'][0]-> param2 );
else
	echo $chartinfo['kingdomheritage_w']['max'][0]-> param2;
?> 
</b><?php echo kohana::lang('regionview.stats_reachedon')?> <b> <?php echo Utility_Model::format_date ( $chartinfo['kingdomheritage_w']['max'][0]-> period , "M" ) ?> </b><br/>
</i>
</div>
</center>

<br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<center><h3><?php echo kohana::lang('regionview.stats_kingdomavgheritage')?></h3></center>

<br/>

<div>
	<div class='chartcontainer' style='float:left'>
	<?php echo $chartinfo['kingdomavgheritage_w']['html']; ?>
	</div>

	<div class='chartcontainer' style='float:right'>
	<?php echo $chartinfo['kingdomavgheritage_m']['html']; ?>
	</div>

	<div style='clear:both'></div>

</div>

<br/>

<center>

<div style='width:100%'>

<i><?php echo kohana::lang('regionview.stats_minkingdomavgheritage', $chartinfo['kingdomavgheritage_w']['min'][0] -> param1)?>
<?php 
if ( $chartinfo['kingdomavgheritage_w']['min'][0]-> param3 != 0 )
	echo html::anchor( 'character/publicprofile/' . $chartinfo['kingdomavgheritage_w']['min'][0]-> param3 , $chartinfo['kingdomavgheritage_w']['min'][0]-> param2 );
else
	echo $chartinfo['kingdomavgheritage_w']['min'][0]-> param2;
?> 
</b><?php echo kohana::lang('regionview.stats_reachedon')?> <b> <?php echo Utility_Model::format_date ( $chartinfo['kingdomavgheritage_w']['max'][0]-> period , "M" ) ?> </b><br/>

<i><?php echo kohana::lang('regionview.stats_maxkingdomavgheritage', $chartinfo['kingdomavgheritage_w']['max'][0] -> param1)?>
<?php 
if ( $chartinfo['kingdomavgheritage_w']['max'][0]-> param3 != 0 )
	echo html::anchor( 'character/publicprofile/' . $chartinfo['kingdomavgheritage_w']['max'][0]-> param3 , $chartinfo['kingdomavgheritage_w']['max'][0]-> param2 );
else
	echo $chartinfo['kingdomavgheritage_w']['max'][0]-> param2;
?> 
</b><?php echo kohana::lang('regionview.stats_reachedon')?> <b> <?php echo Utility_Model::format_date ( $chartinfo['kingdomavgheritage_w']['max'][0]-> period , "M" ) ?> </b><br/>
</i>
</div>
</center>


<br style='clear:both'/>
