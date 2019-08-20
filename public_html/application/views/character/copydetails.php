<div class="row">
	<div class="col-xs-10 col-xs-offset-1" id="wrapper">
		
		<ul class="nav nav-tabs">		
		<?php echo $submenu ?>
		</ul>
		
		<div class="row" id="general-info">	
			
			<!-- avatar --> 
			
			<div class="col-xs-1">		
				<h3 class='text-center'>
					<?php echo $character->get_name() ?> (<?php echo strtoupper($character->sex) ?>)
				</h3>

				<br/>
				
				<div id='frame'>				
					<?php echo Character_Model::display_avatar( $character -> id, $size = 'l', $class = 'charpic' ) ?>		
				</div>
					
				<div id="links-avatar">
					<?php
						echo html::anchor('/character/change_avatar/', kohana::lang('character.change_avatar')).'<br/>';
						echo html::anchor('/wardrobe/atelier_dynamo/avatars/avatar', kohana::lang('character.buy_avatar'));	
					?>
				</div>
			</div>
			
			<!-- Info panel 1 --> 
			
			<div class="col-xs-6" id='info-1'>

				<h5 class='left underlined'><?php echo kohana::lang('character.create_generaldata'); ?></h5>
				
				<?php
					echo kohana::lang('global.user_id') . ': ' ."<b>" . $character->user->id  . "</b><br/>";
					echo kohana::lang('global.character_id') . ': ' . "<b>" . $character->id  . "</b><br/>";
					echo kohana::lang('global.referralid') . ': ' . "<b>" . $character->user_id  . "</b><br/>";
					echo kohana::lang('character.born') . ': ' . "<b>" . Utility_Model::format_date($character->birthdate) . "</b><br/>";
					echo kohana::lang('character.birthregion') . ': ' . "<b>" .$birthregionname . "</b><br/>";
					echo kohana::lang('global.age') . ': ' . "<b>" . Utility_Model::d2y( 
						time(), $character -> birthdate ) . "</b><br/>";
					echo kohana::lang('character.home') . ': ' . "<b>" . kohana::lang( $home ) . "</b><br/>";
					echo kohana::lang('character.actual_position') . ': ' . "<b>" . kohana::lang( $current_position ) . "</b><br/>";			
					echo kohana::lang('character.gamescore') . ': ' . "<b>" . $character -> score . "</b><br/>";
					echo kohana::lang('character.honorpoints') . ': ' . "<b>" . $honorpoints . "</b><br/>";
					echo kohana::lang('global.role') . ': ' . "<b>" . $character -> get_rolename( true ) . "</b><br/>";
				?>			
			
			</div> 
			
			<!-- info panel 2-->
			
			<div class="col-xs-5" id='rightinfo'>
					
				<?php if ( !is_null( $character -> church_id ) )
				{
				?>				
					<div id='religion'>
						<h5 class='left underlined'><?php echo kohana::lang('religion.religion') ?></h5>
						
					<?php	
						echo kohana::lang('religion.religion') . ': ' . "<b>" . kohana::lang('religion.religion-' . $character -> church -> religion -> name ) . "</b><br/>";
						echo kohana::lang('religion.church') . ': ' . "<b>" . kohana::lang('religion.church-' . $character -> church -> name ) . "</b><br/>";
						echo kohana::lang('religion.faith') . ': ' . "<b>" . $faithlevel -> value . '%' . '</b><br/>';
						echo kohana::lang('religion.alms', $alms -> value) .'<br/>' ;
						echo kohana::lang('religion.contributedfp') . ': <b>' . $afp -> value . '</b>' ;
										
					?>				
					</div>
				
				<?php 
				} 
				?>
				<br/>
				<div id ='attributes' class="right">
				
					<h5 class='left underlined'><?php echo kohana::lang('character.attributes')?></h5>											
					<div class="stat">
						<?php echo Kohana::lang('character.create_charstr'); ?>
						<div class="boxstat2" >
							<div class="barstat2" style="width:<?php echo round(146*$strinfo/Character_Model::get_attributelimit()) ?>px" 
								title="<?php 
								echo kohana::lang('global.originalvalue') . ': ' ;
								echo $strinfooriginal . ', ' ;
								echo kohana::lang('global.actualvalue') . ': ' ;
								echo $strinfo . '<br/><br/>' ;
								echo '<b>'. kohana::lang('global.modificatore') . '</b><br/>' ;
								echo '<hr/><br/>';
								echo '<table>';
								foreach ( (array) $strmodifiers as $modifier )
								{
									if ( $modifier['value'] != 0 )
									{
										echo '<tr>';
										
										if ( $modifier['value'] > 0 ) 										
											echo "<td width='5%' class='right'>+" . $modifier['value'] . '</td>';
										else
											echo "<td width='5%' class='right'>" . $modifier['value'] . '</td>';
										echo "<td width='95%' >";
										foreach ((array) $modifier['reason'] as $reason)
											echo $reason . ' ';
										echo '</td>';
										echo '</tr>';
									}
								}
								echo '</table>';
								?>">
							</div>
						</div>
					</div>	
					
					<div class="stat">
						<?php echo Kohana::lang('character.create_chardex'); ?>
						<div class="boxstat2">
							<div class="barstat2" style="width:<?php echo round(146*
								$dexinfo/Character_Model::get_attributelimit()) ?>px" title="<?php 
								echo kohana::lang('global.originalvalue') . ': ' ;
								echo $dexinfooriginal . ', ' ;
								echo kohana::lang('global.actualvalue') . ': ' ;
								echo $dexinfo . '<br/><br/>' ;
								echo '<b>'. kohana::lang('global.modificatore') . '</b><br/>' ;
								echo "<hr/><br/>";
								echo '<table>';
								
								foreach ( (array) $dexmodifiers as $modifier )
								{
									if ( $modifier['value'] != 0 )
									{
										echo '<tr>';
										if ( $modifier['value'] > 0 ) 										
											echo "<td width='5%' class='right'>+" . $modifier['value'] . '</td>';
										else
											echo "<td width='5%' class='right'>" . $modifier['value'] . '</td>';
										echo "<td width='95%' >";
										foreach ((array) $modifier['reason'] as $reason)
											echo $reason . ' ';
										echo '</td>';
										echo '</tr>';
									}
								}
								echo '</table>';
							?>">
							</div>
						</div>
					</div>

					<div class="stat">
						<?php echo Kohana::lang('character.create_charintel'); ?>
						<div class="boxstat2">
							<div class="barstat2" style="width:<?php echo round(146*
								$intelinfo/Character_Model::get_attributelimit()) ?>px" title="<?php 
								echo kohana::lang('global.originalvalue') . ': ' ;
								echo $intelinfooriginal . ', ' ;
								echo kohana::lang('global.actualvalue') . ': ' ;
								echo $intelinfo . '<br/><br/>' ;
								echo '<b>'. kohana::lang('global.modificatore') . '</b><br/>' ;
								echo '<hr/><br/>';
								echo '<table>';
								foreach ( (array) $intelmodifiers as $modifier )
								{
									if ( $modifier['value'] != 0 )
									{
										echo '<tr>';
										if ( $modifier['value'] > 0 ) 										
											echo "<td width='5%' class='right'>+" . $modifier['value'] . '</td>';
										else
											echo "<td width='5%' class='right'>" . $modifier['value'] . '</td>';										
										echo "<td width='95%' >";
										foreach ((array) $modifier['reason'] as $reason)
											echo $reason . ' ';
										echo '</td>';
										echo '</tr>';
									}
								}
								echo '</table>';
							?>">
							</div>
						</div>
					</div>

					<div class="stat">
						<?php echo Kohana::lang('character.create_charcost'); ?>
						<div class="boxstat2">
							<div class="barstat2" style="width:<?php echo round(146*
								$costinfo/Character_Model::get_attributelimit()) ?>px" title="<?php 
								
								echo kohana::lang('global.originalvalue') . ': ' ;
								echo $costinfooriginal . ', ' ;
								echo kohana::lang('global.actualvalue') . ': ' ;
								echo $costinfo . '<br/><br/>' ;
								echo '<b>'. kohana::lang('global.modificatore') . '</b><br/>' ;
								echo '<hr/><br/>';
								echo '<table>';
								foreach ( (array) $costmodifiers as $modifier )
								{
									if ( $modifier['value'] != 0 )
									{
										echo '<tr>';
										if ( $modifier['value'] > 0 ) 										
											echo "<td width='5%' class='right'>+" . $modifier['value'] . '</td>';
										else
											echo "<td width='5%' class='right'>" . $modifier['value'] . '</td>';
										echo "<td width='95%' >";
										foreach ((array)$modifier['reason'] as $reason)
											echo $reason . ' ';
										echo '</td>';
										echo '</tr>';
									}
								}
								echo '</table>';
							?>">
							</div>
						</div>
					</div>

					<div class="stat">
						<?php echo Kohana::lang('character.create_charcar'); ?>
						<div class="boxstat2">
							<div class="barstat2" style="width:<?php echo round(146*
								$carinfo/Character_Model::get_attributelimit()) ?>px" title="<?php 
								echo kohana::lang('global.originalvalue') . ': ' ;
								echo $carinfooriginal . ', ' ;
								echo kohana::lang('global.actualvalue') . ': ' ;
								echo $carinfo . '<br/><br/>' ;
								echo '<b>'. kohana::lang('global.modificatore') . '</b><br/>' ;
								echo '<hr/><br/>';
								echo '<table>';							
								foreach ( (array) $carmodifiers as $modifier )
								{
									
									if ( $modifier['value'] != 0 )
									{
										echo '<tr>';
										if ( $modifier['value'] > 0 ) 										
											echo "<td width='5%' class='right'>+" . $modifier['value'] . '</td>';
										else
											echo "<td width='5%' class='right'>" . $modifier['value'] . '</td>';
										echo "<td width='95%'>";
										foreach ( (array) $modifier['reason'] as $reason)
											echo $reason . ' ';
										echo '</td>';
										echo '</tr>';
									}
								}
								echo '</table>';
								
							?>">
							</div>
						</div>
					</div>
					
					<div style='text-align:right'>
					<?php if ( $showcalink ) 
						echo html::anchor('/character/change_attributes/', 
						kohana::lang('character.redistributeattributes', kohana::config('medeur.newbiedays' )));
					?>
					</div>					
					
				</div> <!-- div attributes -->				
			</div>	<!-- div rightinfo -->
		</div> <!-- row --> 
		
		
<div id='diseases'>
	
	<h5 class='left underlined'><?php echo kohana::lang('character.health')?></h5>
	
	<?php echo kohana::lang('global.status') ?>:
	<?php 
		if ( $character -> is_sick() )
			echo "<span class='evidence'>" . kohana::lang('character.sick') . "</span>";
		else
			echo '<b> '. kohana::lang('character.healthy') . '</b>';
	?>
	<br/>
	<?php echo kohana::lang('character.intoxicationlevel') ?>: <b><?php echo $intoxicationlevel ?></b>
	<br/>
	<?php echo kohana::lang('character.diseases') ?>: 
	<?php 
		
		$diseasestext = implode($diseaseslist, ", ");
		echo $diseasestext;				
	?>
	<br/>
</div>

<!--parentele-->

<br/>

<div id='kinrelations'>
	<h5 class="left underlined"><?php echo kohana::lang('character.kinrelations')?></h5>		
	<table>
	<tr>
	<td colspan="2">
	<?php 
	if ( count($kinrelations) > 0 )	
	{
		
		foreach ( $kinrelations['incomingrelations'] as $relationtype => $data )
		{
?>
			<div class='kinship center'>
				<?php echo Character_Model::display_avatar($data['id'], 'l', 'border'); ?>
				<br/>
				<?php echo Character_Model::create_publicprofilelink($data['id']); ?>
				<br/>
				<?php echo kohana::lang('character.kinrelation_' . $relationtype ); ?>					
			</div>			
	<?php
		}
	}
	?>			
	</td>		
	</tr>
	</table>
</div>

<br/>

<h5 class='left underlined'><?php echo kohana::lang('global.otherdata')?></h5>

<div style='float:right'><?php echo html::anchor('character/publicprofile/' . $character -> id, kohana::lang('character.myprofile'))?></div>

<div style="text-align:left; padding-top:10px">
	<b><?php echo Kohana::lang('character.slogan')?></b> ( <?php echo html::anchor('/character/change_slogan/', kohana::lang('global.edit') )?> )
	<p><?php echo nl2br( $character->slogan) ?></p>
	<br/>
	<b><?php echo Kohana::lang('global.description')?></b> ( <?php echo html::anchor('/character/change_description/', kohana::lang('global.edit') )?> )
	<p><?php echo Utility_Model::truncateHtml(
		Utility_Model::bbcode( $character -> description), 250	) ?></p>
	<br/>
	<b><?php echo Kohana::lang('character.char_history')?></b> ( <?php echo html::anchor('/character/change_history/', kohana::lang('global.edit') )?> )
	<p><?php echo Utility_Model::bbcode( $character->history) ?></p>
	<br/>
	<b><?php echo Kohana::lang('character.char_signature')?></b> ( <?php echo html::anchor('/character/change_signature/', kohana::lang('global.edit') )?> )
	<p><?php echo Utility_Model::bbcode( $character->signature) ?></p>
</div>
	</div>
</div>