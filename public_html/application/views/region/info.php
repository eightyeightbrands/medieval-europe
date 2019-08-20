<div class='pagetitle'><?php echo kohana::lang('regionview.regioninfo_pagetitle', kohana::lang($region -> name))?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo $submenu ?>

<center>
	
	<fieldset>
	<legend><h3><?php echo kohana::lang('regioninfo.kingdominfo')?></h3></legend>
	
	<br/>
		
	<h2><?php echo Kohana::lang($region -> kingdom -> get_name());?></h2>	
	<?php echo html::image('media/images/heraldry/' . $region -> kingdom -> get_image('large'));?>
	<br/>
	<i><?php echo $region -> kingdom -> slogan ?></i>
	
	<br/>
	<br/>
	
	<table width="75%">		
	
	<tr>
		<td class='left' width="50%"><?php echo Kohana::lang('regioninfo.kingdomaverageage'); ?></td>
		<td colspan='2' class='right' width="40%" style="text-align:right"><b><?php echo $kingdom_info['averageage'] . ' ' . kohana::lang('global.days') ?></b></td>
	</tr>
	<tr>
		<td class='left' width="50%"><?php echo Kohana::lang('regioninfo.kingdomtotalplayers'); ?></td>
		<td colspan='2' class='right' width="40%" style="text-align:right"><b><?php echo html::anchor ('/region/kingdomcitizens/' . 
			$region -> id, count($kingdom_info['citizens']) )?></b></td>
	</tr>
	
	<tr>
	<td  class='left'  width="50%"><?php echo Kohana::lang('global.regent'); ?></td>
	<td  class='right' width="50%"><b>
	<?php if ( !is_null( $king ) and $king -> loaded == true ) 
				echo Character_Model::create_publicprofilelink( 
					$king -> id, $king -> get_name() );
			else
				echo kohana::lang('global.vacant' );
	?>
	</b></td>
	</tr>	
	
	
	<tr>
		<td class='left' width="50%">
			<?php echo Kohana::lang('global.constable_m'); ?>
		</td>
		<td colspan='2' class='right' width="40%" style="text-align:right">
		<b>
		<?php 			
			if ( count($constables) > 0 ) 
				foreach ( $constables as $constable )
					echo Character_Model::create_publicprofilelink( 
						$constable -> id, $constable -> get_name() ) . '<br>';
			else
				echo kohana::lang('global.vacant' );
		?>				
		</b></td>
	</tr>
	<tr>
		<td class='left' width="50%">
			<?php echo Kohana::lang('global.chancellor_m'); ?>
		</td>
		<td colspan='2' class='right' width="40%" style="text-align:right">
		<b>
		<?php 			
			if ( count($chancellors) > 0 ) 
				foreach ( $chancellors as $chancellor )
					echo Character_Model::create_publicprofilelink( 
						$chancellor -> id, $chancellor -> get_name() ) . '<br>';
			else
				echo kohana::lang('global.vacant' );
		?>				
		</b></td>
	</tr>
	<tr>
		<td class='left' width="50%">
			<?php echo Kohana::lang('global.seneschal_m'); ?>
		</td>
		<td colspan='2' class='right' width="40%" style="text-align:right">
		<b>
		<?php 			
			if ( count($seneschals) > 0 ) 
				foreach ( $seneschals as $seneschal )
					echo Character_Model::create_publicprofilelink( 
						$seneschal -> id, $seneschal -> get_name() ) . '<br>';
			else
				echo kohana::lang('global.vacant' );
		?>				
		</b></td>
	</tr>
	<tr>
		<td class='left' width="50%">
			<?php echo Kohana::lang('global.chamberlain_m'); ?>
		</td>
		<td colspan='2' class='right' width="40%" style="text-align:right">
		<b>
		<?php 			
			if ( count($chamberlains) > 0 ) 
				foreach ( $chamberlains as $chamberlain )
					echo Character_Model::create_publicprofilelink( 
						$chamberlain -> id, $chamberlain -> get_name() ) . '<br>';
			else
				echo kohana::lang('global.vacant' );
		?>				
		</b></td>
	</tr>
	<tr>
		<td class='left' width="50%">
			<?php echo Kohana::lang('global.treasurer_m'); ?>
		</td>
		<td colspan='2' class='right' width="40%" style="text-align:right">
		<b>
		<?php 			
			if ( count($treasurers) > 0 ) 
				foreach ( $treasurers as $treasurer )
					echo Character_Model::create_publicprofilelink( 
						$treasurer -> id, $treasurer -> get_name() ) . '<br>';
			else
				echo kohana::lang('global.vacant' );
		?>				
		</b></td>
	</tr>
	<tr>
		<td class='left' width="50%">
			<?php echo Kohana::lang('global.ambassador_m'); ?>
		</td>
		<td colspan='2' class='right' width="40%" style="text-align:right">
		<b>
		<?php 			
			if ( count($ambassadors) > 0 ) 
				foreach ( $ambassadors as $ambassador )
					echo Character_Model::create_publicprofilelink( 
						$ambassador -> id, $ambassador -> get_name() ) . '<br>';
			else
				echo kohana::lang('global.vacant' );
		?>				
		</b></td>
	</tr>
	<tr>
		<td class='left' width="50%"><?php echo Kohana::lang('regioninfo.richestkingdom_position'); ?></td>
		<td colspan='2' class='right' width="40%" style="text-align:right"><b>
		<?php echo $kingdom_info['richestkingdomposition'] . ' ' . kohana::lang('global.on') . ' ' . $kingdom_info['totalkingdoms'] ?></b></td>
	</tr>
	<tr>
		<td class='left' width="50%"><?php echo Kohana::lang('regioninfo.populatedkingdom_position'); ?></td>
		<td colspan='2' class='right' width="40%" style="text-align:right"><b>
		<?php echo $kingdom_info['populatedkingdomposition'] . ' ' . kohana::lang('global.on') . ' ' . $kingdom_info['totalkingdoms'] ?></b></td>
	</tr>		
	<tr>
		<td class='left' width="50%"><?php echo Kohana::lang('regioninfo.activekingdom_position'); ?></td>
		<td colspan='2' class='right' width="40%" style="text-align:right"><b>
		<?php echo $kingdom_info['activekingdomposition'] . ' ' . kohana::lang('global.on') . ' ' . $kingdom_info['totalkingdoms'] ?></b></td>
	</tr>		
	
	<tr><td colspan='2'><h3 class='center'><?php echo kohana::lang('global.achievements')?></h3></td></tr>
	
	<tr>
	<td colspan='2'>
	<?php 
		foreach ( $region -> kingdom -> kingdom_title as $kingdom_title )
			if ($kingdom_title -> current == 'Y' )
				echo html::image( 
					'media/images/badges/kingdom/badge_' . $kingdom_title -> cfgachievement -> tag . '_' . 
						$kingdom_title -> cfgachievement -> level . '.png',
					array(
						'class' => 'badge', 
						'title' => kohana::lang('titles.' . $kingdom_title -> cfgachievement -> tag . '_' . 
						$kingdom_title -> cfgachievement -> level ) )); 
	?>
	</td>
	</tr>
			
	
	
	<?php if ( $char -> region -> kingdom_id != $region -> kingdom_id ) { ?>
	
	<tr><td class='center' colspan='2'></td></tr>	
	<tr><td class='center' colspan='2'><h3><?php echo kohana::lang('diplomacy.diplomacyrelations')?><h3></td></tr>	
	<tr><td class='center' colspan='2'></td></tr>	
	<tr>
		<td class='center' colspan='2'>
			<?php 
			
			echo kohana::lang( 'diplomacy.diplomacyrelationsourcedest', 
				kohana::lang('diplomacy.' . $diplomacyrelationsourcedest['type'] ) ) ?>
		</td>
	</tr>	
	
	<tr>
		<td class='center' colspan='2'>
			<?php echo html::anchor('/region/info_diplomacy/' . $region -> id, kohana::lang('diplomacy.diplomacyrelations')); ?>
		</td>	
	</tr>

	<?php } ?>
	
	
	
	</td>
	</tr>
	
	</table>
	</fieldset>
	
	<br/>
	
	<fieldset>
	<legend><h3><?php echo kohana::lang('regioninfo.churchinfo')?></h3></legend>
	
	<br/>
	
	<table width="75%">		
	
	<tr>
		<td><b><?php echo kohana::lang('religion.religion')?></b></td>
		<td><b><?php echo kohana::lang('religion.church')?></b></td>
		<td class='right'><b><?php echo kohana::lang('religion.followers')?></b></td>
	
	<tr>
		<td class='left' width="30%"><?php echo Kohana::lang('religion.religion-teological'); ?></td>
		<td class='left' width="40%"><?php echo Kohana::lang('religion.church-rome'); ?></td>		
		<td class='right' width="30%" style="text-align:right"><b><?php echo $kingdom_info['religion']['teological']['rome']['total'] . 
			' ( ' . $kingdom_info['religion']['teological']['rome']['percentage']  . '%)' ?></b></td>
		<td>
		<?php echo html::anchor( 
			'religion_1/viewinfo/' . $kingdom_info['religion']['teological']['rome']['id'], 
			kohana::lang('global.info'),
			array( 'target' => 'new' ) ) ?>
		</td>
	</tr>
	<tr>
		<td class='left'><?php echo Kohana::lang('religion.religion-pagan'); ?></td>
		<td class='left'><?php echo Kohana::lang('religion.church-turnu'); ?></td>		
		<td class='right' style="text-align:right"><b><?php echo $kingdom_info['religion']['pagan']['turnu']['total'] . 
			' ( ' . $kingdom_info['religion']['pagan']['turnu']['percentage']  . '%)' ?></b></td>
		<td>
		<?php echo html::anchor( 
			'religion_1/viewinfo/' . $kingdom_info['religion']['pagan']['turnu']['id'],
			kohana::lang('global.info'),
			array( 'target' => 'new' ) 
			) ?>
		</td>	
	</tr>
	<tr>
		<td class='left'><?php echo Kohana::lang('religion.religion-mystical'); ?></td>
		<td class='left'><?php echo Kohana::lang('religion.church-cairo'); ?></td>		
		<td class='right' style="text-align:right"><b><?php echo $kingdom_info['religion']['mystical']['cairo']['total'] . 
			' ( ' . $kingdom_info['religion']['mystical']['cairo']['percentage']  . '%)' ?></b></td>
		<td>
		<?php echo html::anchor( 
			'religion_1/viewinfo/' . $kingdom_info['religion']['mystical']['cairo']['id'],
			kohana::lang('global.info'),
			array( 'target' => 'new' ) 
			) ?>
		</td>	
	</tr>
	<tr>
		<td class='left'><?php echo Kohana::lang('religion.religion-patriarchal'); ?></td>
		<td class='left'><?php echo Kohana::lang('religion.church-kiev'); ?></td>		
		<td class='right' style="text-align:right"><b><?php echo $kingdom_info['religion']['patriarchal']['kiev']['total'] . 
			' ( ' . $kingdom_info['religion']['patriarchal']['kiev']['percentage']  . '%)' ?></b></td>
		<td>
		<?php echo html::anchor( 
			'religion_1/viewinfo/' . $kingdom_info['religion']['patriarchal']['kiev']['id'],
			kohana::lang('global.info'),
			array( 'target' => 'new' ) 
			) ?>
		</td>	
	</tr>
	<tr>
		<td class='left'><?php echo Kohana::lang('religion.religion-norse'); ?></td>
		<td class='left'><?php echo Kohana::lang('religion.church-norse'); ?></td>		
		<td class='right' style="text-align:right"><b><?php echo $kingdom_info['religion']['norse']['norse']['total'] . 
			' ( ' . $kingdom_info['religion']['norse']['norse']['percentage']  . '%)' ?></b></td>
		<td>
		<?php echo html::anchor( 
			'religion_1/viewinfo/' . $kingdom_info['religion']['norse']['norse']['id'],
			kohana::lang('global.info'),
			array( 'target' => 'new' ) 
			) ?>
		</td>	
	</tr>
	<tr>
		<td class='left'><?php echo Kohana::lang('religion.religion-atheism'); ?></td>
		<td class='left'><?php echo Kohana::lang('religion.church-nochurch'); ?></td>		
		<td class='right' style="text-align:right"><b><?php echo $kingdom_info['religion']['atheism']['nochurch']['total'] . 
			' ( ' . $kingdom_info['religion']['atheism']['nochurch']['percentage']  . '%)' ?></b></td>
		<td>
		<?php echo html::anchor( 
			'religion_1/viewinfo/' . $kingdom_info['religion']['atheism']['nochurch']['id'],
			kohana::lang('global.info'),
			array( 'target' => 'new' ) 
		) ?>
		</td>	
	</tr>
	</table>

	</fieldset>
	
	<br/>	
	
	<fieldset>
	<legend><h3><?php echo kohana::lang('regioninfo.regioninfo')?></h3></legend>
	
	<br/>	
	
	<table width="75%">	
			
	<tr>
		<td  class='left' width="80%"><?php echo Kohana::lang('regioninfo.regionresidentplayers'); ?></td>
		<td  class='right' width="20%">
			<b>
			<?php 
				echo $tot_region_residents; ?>
				</b>
			</td>
	</tr>
	<tr>
		<td  class='left' ><?php echo Kohana::lang('regioninfo.regionpresentplayers'); ?></td>
		<td  class='right' >
			<b>
			<?php									
				if ( $char -> position_id == $region -> id and $tot_players_present > 0 )
					echo html::anchor ('/region/regionpresentchars/' . $region -> id , $tot_players_present );
				else
					echo $tot_players_present				 
			?>
			</b>
		</td>
	</tr>
	<td  class='left'><?php echo Kohana::lang('global.vassal'); ?></td>
	<td  class='right'><b>
	
	<?php if ( !is_null( $vassal ) and $vassal -> loaded == true ) 
				echo Character_Model::create_publicprofilelink( 
					$vassal -> id, $vassal -> get_name() );
			else
				echo kohana::lang('global.vacant' );
	?>	
	</b></td>
	</tr>
	
	
	<tr>
	<td  class='left'><?php echo Kohana::lang('global.judge'); ?></td>
	<td style="text-align:right"><b>
	<?php if ( !is_null( $judge ) and $judge -> loaded == true ) 
				echo Character_Model::create_publicprofilelink( 
					$judge -> id, $judge -> get_name() );
			else
				echo kohana::lang('global.vacant' );
	?>
	</b></td>
	</tr>
	
	
	<tr>
	<td  class='left' ><?php echo Kohana::lang('global.sheriff'); ?></td>
	<td style="text-align:right"><b>
	<?php if ( !is_null( $sheriff ) and $sheriff -> loaded == true ) 
				echo Character_Model::create_publicprofilelink( 
					$sheriff -> id, $sheriff -> get_name() );
			else
				echo kohana::lang('global.vacant' );
	?>		
	</b></td>		
	</tr>
	
		
	
	<tr>
	<td  class='left' ><?php echo Kohana::lang('global.academydirector'); ?></td>
	<td  style="text-align:right"><b>
	

	<?php if ( !is_null( $academydirector ) and $academydirector -> loaded == true ) 
				echo Character_Model::create_publicprofilelink( 
					$academydirector -> id, $academydirector -> get_name() );
			else
				echo kohana::lang('global.vacant' );		
	?>
	</b></td>
	</tr>
	
	<tr>
	<td  class='left' ><?php echo Kohana::lang('global.drillmaster'); ?></td>
	<td  style="text-align:right"><b>
	<?php if ( !is_null( $drillmaster ) and $drillmaster -> loaded == true ) 
				echo Character_Model::create_publicprofilelink( 
					$drillmaster -> id, $drillmaster -> get_name() );
			else
				echo kohana::lang('global.vacant' );
	?>	
	</b></td>
	</tr>
	</table>
	
	<br/>
	
	<h3><?php echo Kohana::lang('regioninfo.regiontotalstructures'); ?></h3>
		
	<table width="75%" border="0">
	<?php 
		$i=0;
		foreach ($houselist as $house )
		{
			echo "<tr>";
			echo "<td  class='left'width='90%'>" . Kohana::lang( $houselist[$i]['house']->name ) . "</td>";
			echo "<td width='10%' style='text-align:right'><b>" . $houselist[$i]['tot'] . "</b></td>";
			echo "</tr>";
			$i++;
		}
	?>
		
	<tr>
		<td  class='left' ><?php echo kohana::lang('regioninfo.regiontakenterrains').":"?></td>
		<td  style='text-align:right'><b><?php echo $terrains_info['terrains_taken']; ?></b></td>
	</tr>
	<tr>
		<td  class='left' ><?php echo kohana::lang('regioninfo.regionfreeterrains')?></td>
		<td style='text-align:right'><b><?php echo $terrains_info['terrains_free']; ?></b></td>
	</tr>
	</table>
	
	</fieldset>
	
	<br/>
	
	<fieldset>
	<legend><h3><?php echo kohana::lang('regioninfo.regiontaxlist')?></h3></legend>
	
		
	<h3><?php echo kohana::lang('taxes.valueaddedtax_name')?></h3>
	
	<table>	
	<tr>
	<td class='center'><b><?php echo kohana::lang('taxes.citizenvalue') ?></b></td>
	<td class='center'><b><?php echo kohana::lang('taxes.neutralvalue') ?></b></td>
	<td class='center'><b><?php echo kohana::lang('taxes.friendlyvalue') ?></b></td>
	<td class='center'><b><?php echo kohana::lang('taxes.alliedvalue') ?></b></td>		
	<tr>
	<td class='center'><?php echo $vat -> citizen ?>%</td>
	<td class='center'><?php echo $vat -> neutral ?>%</td>
	<td class='center'><?php echo $vat -> friendly ?>%</td>
	<td class='center'><?php echo $vat -> allied ?>%</td>
	</tr>
	</table>
	
	<br/>
	
	</fieldset>
	
</center>	
	
<br style="clear:both;" />
