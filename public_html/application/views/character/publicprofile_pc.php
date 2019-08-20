<script type="text/javascript">
	$(document).ready(function()
	{
		$("#tabs").tabs({active: 0 });
	});
</script>
<?php echo $header ?>
<table>
<tr>
<td colspan="2">
<h5><?php echo kohana::lang('character.citizenshipandtitles')?></h5>
</td>
</tr>

<tr>
<td colspan="2">
	<table>
	<tr>
	<td width="15%" valign="top" class="center">
		<div id='frame'>
		<?php echo Character_Model::display_avatar( $character -> id, 'l', 'charpic');?>
		</div>
	</td>
	<td  width="85%" valign="top">

		<!-- rp title -->

		<table>

			<tr>
			<td width="5%">
				<?php echo html::image('media/images/heraldry/' . $character -> region -> kingdom -> get_image('large'));?>
			</td>
			<td>
				<?php echo Kohana::lang($character -> region -> kingdom -> get_name());?>
			</td>
			</tr>

			<?php
			foreach ( $titles as $r )
			{
			?>
			<tr>

			<td width="5%" class="rptitles">
				<?php echo $r->get_title_image(); ?>
			</td>

			<td>
				<?php echo $r -> get_title(true); ?>
			</td>
			</tr>
			<?php
			}
			?>
			</td>
			</tr>
		</table>


	</td>
	</tr>
	</table>
</td>
</tr>
<tr>
<td colspan="2">

<!-- Titles and Achievements -->

<tr>
<td colspan='2' valign="top" class="badges">
<h5 style='text-align:left;border-bottom:1px solid lightgrey;margin-bottom:5px;'><?php echo kohana::lang('global.badges')?></h5>
<?php


	foreach ( $character -> character_titles as $title )
	{

		// Hide maxstat badges if char selected so.

		if ( $character -> user -> hidemaxstatsbadges == 'Y' and
			 $viewingchar -> id != $character -> id and
			 in_array( $title -> name, array( 'stat_str', 'stat_dex', 'stat_intel', 'stat_car', 'stat_cost' ) ))
			;
		else
		{
			if ( $title -> current == 'Y' )
				echo html::image( 'media/images/badges/character/badge_' . $title -> name .'_' . $title -> stars . '.png',
				array('class' => 'badge', 'title' => kohana::lang('titles.' . $title -> name . '_' . $title -> stars ) ));
		}
	}
?>
</td>
</tr>


<!-- General info -->

<tr>
<td>
<h5 style='text-align:left;border-bottom:1px solid lightgrey;margin-bottom:5px;'><?php echo kohana::lang('character.general_stats')?></h5>
</td>
</tr>

<tr>

<td width="35%" valign="top" class="center">
<?php
//var_dump( Character_Model::get_stat_d( $character -> id, 'tournamentparticipant'));
$isparticipatingtotournament = Character_Model::get_stat_d( $character -> id, 'tournamentparticipant');
if ( $isparticipatingtotournament -> loaded == false )
{
	$character -> render_char ( $equippeditems, 'wardrobe', 'medium' );
}
else
{
	echo kohana::lang('character.info-equipmentnotvisible');
}
?>
</td>

<td valign="top">
<table width='100%' border="0">
	<tr>
		<td width='30%'><?php echo  kohana::lang('global.role') ?></td>
		<td colspan='2'><b>
		<?php
		if ( !is_null( $role ) )
			echo  $character -> get_rolename( true ) . ' - ' . kohana::lang($role -> region -> name) ;
		else
			echo '-';
		?>
		</b></td>
	</tr>

	<?php
	if ( !is_null( $role ) )
	{
	?>

		<tr>
		<td><?php echo kohana::lang('global.since') ?></td>
		<td colspan='2'><b><?php echo Utility_Model::format_date( $role -> begin) ;?></b></td>
		</tr>

	<?php } ?>

	<tr>
		<td><?php echo kohana::lang('character.born') ?></td>
		<td colspan='2'><b><?php echo Utility_Model::format_datetime( $character -> birthdate ) ; ?></b></td>
	</tr>
	<tr>
		<td><?php echo kohana::lang('character.birthregion') ?></td>
		<td colspan='2'><b><?php echo $birthregion ; ?></b></td>
	</tr>
	<tr>
		<td><?php echo kohana::lang('character.home') ?></td>
		<td colspan='2'><b><?php echo kohana::lang($character->region->name) ; ?></b></td>
	</tr>
	<tr>
		<td><?php echo  kohana::lang('global.age') ?></td>
		<td colspan='2'><b>

		<?php echo Utility_Model::secs2hmstostring(
			$character -> get_age('seconds'), 'days');?></b></td>
	</tr>

	<tr>
	<td><?php echo kohana::lang('character.honorpoints') ?></td>
	<td colspan='2'><b><?php echo $honorpoints ;?></b></td>
	</tr>

	<tr>
	<td><?php echo kohana::lang('character.gamescore') ?></td>
	<td colspan='2'><b><?php echo $character -> score ;?></b></td>
	</tr>

	<?php if (
		$character -> position_id == $viewingchar -> position_id and
		(
			$viewingchar -> get_attribute( 'intel' ) >= 18
			or
			$viewingchar -> has_religious_role() == true
		)

	) {?>

	<tr>
		<td><?php echo  kohana::lang('global.status') ?></td>
		<td colspan='2'><b><?php

		if ( $character -> is_sick() )
			echo "<span class='evidence'>" . kohana::lang('character.sick') . "</span>&nbsp;";
		else
			echo kohana::lang('character.healthy');

		?>
		</td>
	</tr>

	<?php } ?>

	<tr>

	<td><?php echo kohana::lang('global.nationality') ?></td>
	<td  colspan='2'><b>
		<?php
		if ( !is_null( $character->user->nationality) and $character->user->nationality != '--' )
			echo $countrycodes[$character->user->nationality] . '&nbsp;' .
				html::image('media/images/flags-lang/' . strtolower($character->user->nationality). ".png");
		else
			echo kohana::lang('global.unknown'); ?>
		</b>
	</td>
	</tr>

	<tr>
		<td><?php echo kohana::lang('global.last_login') ?></td>
		<td colspan='2'><b><?php echo Utility_model::format_date ($character->user->last_login)?></b></td>
	</tr>
	<tr>
		<td><?php echo kohana::lang('global.status') ?></td>
		<td colspan='2'><b>
		<?php

		$online = Character_Model::is_online($character -> id);

		if ( $online )
			echo "<span style='color:#009933;font-weight:bold'>Online</span>";
		else
			echo "<span style='color:#CC0000;font-weight:bold'>Offline</span>";
		?>
		</b>
		</td>
	</tr>
	<tr>
	<td>
		<?php echo kohana::lang('global.meditating') ?>
	</td>
	<td>
	<b>
		<?php
		if ( Character_Model::is_meditating( $character -> id ) )
			echo kohana::lang('global.yes');
		else
			echo kohana::lang('global.no');
		?>
	</b>
	</td>
	</tr>

	<? if ($character -> user -> showlanguages == 'Y') { ?>

		<tr>
		<td>
		<?php echo kohana::lang('user.otherknownlanguages'); ?>
		</td>
		<td>
		<? foreach ($character -> user -> user_languages as $language)
		{
			if ($language -> position == 1)
				echo "<b>" . $language -> language . "</b>";
			else
				echo $language -> language;
			echo "&nbsp;";
		}
		?>
		</td>
		</tr>

	<? } ?>

	</table>
	</td>
</tr>


<!-- Kinship -->

<tr>
<td colspan="2">
	<h5 style='text-align:left;border-bottom:1px solid lightgrey;margin-bottom:5px;'>
		<?php echo kohana::lang('character.kinrelations')?></h5>
	<br/>

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

<!-- rankings -->

<tr>
<td colspan="2">
	<br/>
	<!-- Rankings -->
	<h5 style='text-align:left;border-bottom:1px solid lightgrey;margin-bottom:5px;'><?php echo kohana::lang('character.rankings') ?></h5>
	<br/>
	<?php

		$rankings = Character_Model::get_rankings( $character -> id );

		if ( count( $rankings ) > 0 )
		{
	?>

			<hr/>
			<br/>
		<table width='100%'>
			<td><b><?php echo kohana::lang('global.rank')?></b></td>
			<td class="right"><b><?php echo kohana::lang('character.actual_position')?></b></td>

		<?php

			$r=0;

			foreach ( (array) $rankings as $rank )
			{
				$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : '' ;
		?>

				<tr class='<?php echo $class?>'>
				<td width='50%'><?php echo kohana::lang('rankings.' . $rank -> type)  ?></td>
				<td class='right' width='25%'><?php echo $rank -> position; ?></td>
				</tr>
		<?php
				$r++;
			}
		?>

	</table>

	<?php }
	else
	{
	?>
		<p class='center'><i><?php echo kohana::lang('rankings.rankingsnotyetcalculated');?></i></p>

	<?php
	}
	?>

	<br/>
</td>
</tr>
<!-- Groups -->
<tr>
<td colspan="2">
	<h5 style='text-align:left;border-bottom:1px solid lightgrey;margin-bottom:5px;'><?php echo kohana::lang('character.list_my_groups') ?></h5>

	<?php
		if ( count( $groups ) == 0 )
			echo "<center><i>" . kohana::lang('groups.nogroups' ) . "</i></center>";
		else
		{
			echo "<table>";
			foreach ( $groups as $group )
			{
				if ( $viewingchar -> id != $character -> id and $group -> secret )
					;
				else
				{
					echo '<td width=50px>';
					$file = "media/images/groups/".$group->id.".png";
					if ( file_exists( $file) )
						echo html::image(
							'media/images/groups/'.$group->id.'.png',
							array('class' => 'size50	'));
					else
						echo html::image(
							'media/images/template/group_no_image.png',
							array('class' => 'size50'));
					echo '</td>';

					echo '<td style=\'border:0px\' valign=top><b>' . html::anchor( '/group/view/'.$group->id, $group->name ) . '</b><br/>';
					echo '<i>'. $group->description .'</i></td></tr>';
				}
			}
			echo '</table>';
		}
	?>
</td>
</tr>
</table>

<?php
if ( $viewingchar -> id == $character -> id )
{
?>

	<h5 style='text-align:left;border-bottom:1px solid lightgrey;margin-bottom:5px;'>
	<?= kohana::lang('character.stats'); ?>
	</h5>

	<div id="tabs">
			<ul>
				<li>
				<?= html::anchor(
					'#tab-general',
					kohana::lang('character.stats_generalstatistics') );
				?>
				</li>
				<li>
				<?= html::anchor(
					'#tab-itemproductions',
					kohana::lang('character.stats_itemproductions') );

				?>
				</li>
			<li>
				<?= html::anchor(
					'#tab-killednpc',
					kohana::lang('character.stats_killednpcs') );

				?>
			</li>
		</ul>

		<div id='tab-general'>
			<table class="hover grid">
			<?

				$battlechampion = Character_Model::get_stat_d( $viewingchar -> id, 'battlechampion');
				if ($battlechampion -> loaded )
				{
			?>
				<tr><td width="50%"><?=kohana::lang('character.stat_battlechampion');?></td><td class='value'><?= $battlechampion -> value; ?></td></tr>
			<?
				}

				$boughtdoubloons = Character_Model::get_stat_d( $viewingchar -> id, 'boughtdoubloons');
				if ($boughtdoubloons -> loaded )
				{
			?>
				<tr><td width="50%"><?=kohana::lang('character.stat_boughtdoubloons');?></td><td class='value'><?= Utility_Model::number_format($boughtdoubloons -> value); ?></td></tr>
			<?
				}

				$changedkingdom = Character_Model::get_stat_d( $viewingchar -> id, 'changedkingdom');
				if ($changedkingdom -> loaded )
				{
			?>
				<tr><td width="50%"><?=kohana::lang('character.stat_changedkingdom');?></td><td class='value'><?= Utility_Model::format_date($changedkingdom -> value); ?></td></tr>
			<?
				}

				$duelscore = Character_Model::get_stat_d( $viewingchar -> id, 'duelscore');
				if ($duelscore -> loaded )
				{
			?>
				<tr><td width="50%"><?=kohana::lang('character.stat_duelscore');?></td><td class='value'><?= $duelscore -> value; ?></td></tr>
			<?
				}

				$fightstats = Character_Model::get_stat_d( $viewingchar -> id, 'fightstats');
				if ($fightstats -> loaded )
				{
			?>
				<tr><td width="50%"><?=kohana::lang('character.stat_fightstats');?></td><td class='value'><?= $fightstats -> value; ?>/<?= $fightstats -> stat1; ?>&nbsp;
				(<?= Utility_Model::number_format($fightstats -> value/$fightstats -> stat1 * 100,2) ?>%)</td></tr>
			<?
				}

				$fpcontribution = Character_Model::get_stat_d( $viewingchar -> id, 'fpcontribution', $viewingchar -> church_id);
				if ($fpcontribution -> loaded )
				{
			?>
				<tr><td><?=kohana::lang('character.stat_fpcontribution');?></td><td class='value'><?= Utility_Model::number_format($fpcontribution -> value); ?></td></tr>
			<?
				}

				$lastretiretime = Character_Model::get_stat_d( $viewingchar -> id, 'lastretiretime');
				if ($lastretiretime -> loaded )
				{
			?>
				<tr><td width="50%"><?=kohana::lang('character.stat_lastretiretime');?></td><td class='value'><?= Utility_Model::format_datetime($lastretiretime -> value); ?></td></tr>
			<?
				}

				$questtokensent = Character_Model::get_stat_d( $viewingchar -> id, 'questtokensent');
				if ($questtokensent -> loaded )
				{
			?>
				<tr><td width="50%"><?=kohana::lang('character.stat_questtokensent');?></td><td class='value'><?= $questtokensent -> value; ?></td></tr>

			<?
				}
			?>
		</table>
		</div>

		<div id="tab-itemproductions">
			<table class="hover grid">
				<?php

				$pi_stats = $character -> get_stats( 'itemproduction' );
				if ( count($pi_stats) > 0 )
				{
						foreach ( $pi_stats as $pi_stat )
						{
							$cfgitem = ORM::factory('cfgitem', $pi_stat -> param1);
							if ($cfgitem -> loaded )
							{
				?>


				<tr>
					<td width="50%">
						<?php
						echo '&nbsp;' . kohana::lang($cfgitem -> name ) ;
						?>
					</td>

					<td class="value">
						<?php echo $pi_stat -> value; ?>
					</td>
				</tr>
				<?php

							}
						}
				?>
				<?php
				}
				?>
			</table>
		</div>
		<div id="tab-killednpc">
		<table class="hover grid">
			<?php
			$killednpc_stats = $character -> get_stats( 'killednpc' );
			if ( count($killednpc_stats) > 0 )
			{
					foreach ( $killednpc_stats as $killednpc_stat )
					{
			?>
						<tr>
							<td width="50%">
								<?php
								echo '&nbsp;' . kohana::lang('npc.'.$killednpc_stat-> param1.'_name') ;
								?>
							</td>

							<td class="value">
								<?php echo $killednpc_stat -> value . '/' .  $killednpc_stat -> stat1 . ' (' .
								Utility_Model::number_format(($killednpc_stat->value/$killednpc_stat -> stat1)*100,2) . '%)' ?>
							</td>
						</tr>
			<?php
					}
			}
			?>
		</table>
		</div>
	</div>
<?
}
?>

</td>
</tr>
</table>

<br style='clear:both'/>
