<div id='contentbackground'>
<div class="pagetitle"><?php echo kohana::lang('character.pagetitle') ?></div>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id="general-info">	
		
		<h3 class='center'>
			<?php echo $character->get_name() ?> (<?php echo strtoupper($character->sex) ?>)
		</h3>
		
		<div style='float:right'><?php echo html::anchor('character/publicprofile/' . $character -> id, kohana::lang('character.myprofile'))?></div>


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
		
		<div id='infocontainer'>
			<div id="leftinfo">			
				
				<h5 class='left underlined'><?php echo kohana::lang('character.create_generaldata'); ?></h5>
				
					<?php
					echo kohana::lang('global.user_id') . ': ' ."<span class='value'>" . $character->user->id  . "</span><br/>";
					echo kohana::lang('global.character_id') . ': ' . "<span class='value'>" . $character->id  . "</span><br/>";
					echo kohana::lang('global.referralid') . ': ' . "<span class='value'>" . $character->user_id  . "</span><br/>";
					echo kohana::lang('character.born') . ': ' . "<span class='value'>" . Utility_Model::format_date($character->birthdate) . "</span><br/>";
					echo kohana::lang('character.birthregion') . ': ' . "<span class='value'>" .$birthregionname . "</span><br/>";
					echo kohana::lang('global.age') . ': ' . "<span class='value'>" . Utility_Model::d2y( 
						time(), $character -> birthdate ) . "</span><br/>";
					echo kohana::lang('character.home') . ': ' . "<span class='value'>" . kohana::lang( $home ) . "</span><br/>";
					echo kohana::lang('character.actual_position') . ': ' . "<span class='value'>" . kohana::lang( $current_position ) . "</span><br/>";			
					echo kohana::lang('character.gamescore') . ': ' . "<span class='value'>" . $character -> score . "</span><br/>";
					echo kohana::lang('character.honorpoints') . ': ' . "<span class='value'>" . $honorpoints . "</span><br/>";
					echo kohana::lang('global.role') . ': ' . "<span class='value'>" . $character -> get_rolename( true ) . "</span><br/>";
					?>			
			</div> <!-- div leftinfo -->
			<div id='rightinfo'>
					
			<?php if ( !is_null( $character -> church_id ) )
			{
			?>				
				<div id='religion'>
					<h5 class='left underlined'><?php echo kohana::lang('religion.religion') ?></h5>					
					<?php	
						echo kohana::lang('religion.religion') . ': ' . "<span class='value'>" . kohana::lang('religion.religion-' . $character -> church -> religion -> name ) . "</span><br/>";
						echo kohana::lang('religion.church') . ': ' . "<span class='value'>" . kohana::lang('religion.church-' . $character -> church -> name ) . "</span><br/>";
						echo kohana::lang('religion.faith') . ': ' . "<span class='value'>" . $faithlevel -> value . '%' . '</span><br/>';
						echo kohana::lang('religion.alms') . "<span class='value'>" . $alms -> value . "</span><br/>";
						echo kohana::lang('religion.contributedfp') . ": <span class='value'>" . $afp -> value . '</span>' ;
						echo '<br/>';					
					?>
					<br/>
					<? 					
					if ($character -> church -> religion -> name != 'atheism' ) { ?>
					
					<div class='right'><?= html::anchor(
						'/character/leavereligion/', 
						Kohana::lang('structures.leavereligion'),
						array
						(							
							'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'
						));?>
					</div>
					<? } ?>
				</div>
			
			<?php 
			} 
			?>
			
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
		</div>
		<div style='clear:both'></div>		
		
	</div>		

<br style='clear:both'/>

<fieldset>
<legend>Abilità</legend>
<? 
foreach ((array)$skills as $skill) { ?>
<div style='width:50%;float:left'>
<? $skillobj = SkillFactory_Model::create($skill->param1);?>
<?= $skillobj -> helper_view( $character ); ?>
</div>
<? } ?>
</fieldset>
<br/>

<fieldset>
	<legend><?php echo kohana::lang('character.health')?></legend>
	
	<!--<h5 class='left underlined'><?php echo kohana::lang('character.health')?></h5>-->
	
	<?php echo kohana::lang('global.status') ?>:
	<?php 
		if ( $character -> is_sick() )
			echo "<span class='value'>" . kohana::lang('character.sick') . "</span>";
		else
			echo "<span class='value'>" . kohana::lang('character.healthy') . '</span>';
	?>
	<br/>
	<?php echo kohana::lang('character.intoxicationlevel') ?> : <span class='value'><?php echo $intoxicationlevel ?></span>
	<br/>
	<? if ($character -> is_sick() ){ ?>
	<span class='value'>
		<?php echo kohana::lang('character.diseases') ?>: 
		<? 
		$diseasestext = implode($diseaseslist, ", ");
		echo $diseasestext;				
		?>
	</span>
	<? } ?>
</fieldset>
<br/>
<!--parentele-->
<fieldset>
<legend><?php echo kohana::lang('character.kinrelations')?></legend>
	<table>
	<tr>
	<td colspan="2">
	<?php 
	if ( !is_null($kinrelations['incomingrelations']) )	
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
</fieldset>

<br/>

<fieldset>
<legend><?php echo Kohana::lang('character.slogan')?></legend>
<p><?php echo nl2br( $character->slogan) ?></p>
<div style='float:right'><?php echo html::anchor('/character/change_slogan/', kohana::lang('global.edit') )?></div>
</fieldset>
<br/>
<fieldset>
<legend><?php echo Kohana::lang('global.description')?></legend>
<p><?php echo Utility_Model::truncateHtml(
		Utility_Model::bbcode( $character -> description), 250) ?></p>
<div style='float:right'><?php echo html::anchor('/character/change_description/', kohana::lang('global.edit') )?></div>
</fieldset>
<br/>
<fieldset>
<legend><?php echo Kohana::lang('character.char_history')?></legend>
<p><?php echo Utility_Model::truncateHtml(
		Utility_Model::bbcode( $character -> history), 250) ?></p>
<div style='float:right'><?php echo html::anchor('/character/change_history/', kohana::lang('global.edit') )?></div>
</fieldset>	
<br/>	
<fieldset>
<legend><?php echo Kohana::lang('character.char_signature')?></legend>
<p><?php echo Utility_Model::bbcode( $character->signature) ?></p>
<div style='float:right'><?php echo html::anchor('/character/change_signature/', kohana::lang('global.edit') )?></div>
</fieldset>	


<br style="clear:both;" />
</div>