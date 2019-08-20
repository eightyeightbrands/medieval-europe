<?php
$centeronplayer = ( $mode == 'all' ) ? true : false;
?>

<div class='pagetitle'><?php echo kohana::lang('rankings.pagetitle') ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<ul class="dropdown">
	<li>
	<a href="#"><?php echo kohana::lang('global.churches')?></a>
		<ul class="sub_menu">
			 <li>
			 <?php echo html::anchor('page/rankings/church/mostfollowedchurch/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.mostfollowedchurch')); ?> </li>
		</ul>
	</li>
	<li>
		<a href="#"><?php echo kohana::lang('global.kingdoms')?></a>
		<ul class="sub_menu">
			 <li><?php echo html::anchor('page/rankings/kingdom/richestkingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.richestkingdoms')); ?> </li>
			 <li><?php echo html::anchor('page/rankings/kingdom/populatedkingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.populatedkingdoms')); ?> </li>
			 <li><?php echo html::anchor('page/rankings/kingdom/raiderskingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.raiderskingdoms'));  ?> </li>
			 <li><?php echo html::anchor('page/rankings/kingdom/raidedkingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.raidedkingdoms'));  ?> </li>			 
			 <li><?php echo html::anchor('page/rankings/kingdom/activekingdoms/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.activekingdoms'));  ?> </li>			 
			 <li><?php echo html::anchor('page/kingdomstats/', kohana::lang('rankings.ranking_kingdomstats'));  ?> </li>			 
		</ul>
	</li>
	<li>
	<a href="#"><?php echo kohana::lang('global.regions')?></a>
		<ul class="sub_menu">
		 <li><?php echo html::anchor('page/rankings/region/richestcities/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.richestcities')); ?> </li>
		 <li><?php echo html::anchor('page/rankings/region/populatedcities/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.populatedcities')); ?> </li>
		</ul>
	</li>
	<li>
	<a href="#"><?php echo kohana::lang('global.characters')?></a>
		<ul class="sub_menu">
			<li><?php echo html::anchor('page/rankings/char/gamescore/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.gamescore'));  ?></li>
			 <li><?php echo html::anchor('page/rankings/char/richestchars/' . $mode  . '/' . $centeronplayer, kohana::lang('rankings.richestchars'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/oldestchars/' . $mode  . '/' . $centeronplayer,   kohana::lang('rankings.oldestchars'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/fightstats/' . $mode  . '/' . $centeronplayer,   kohana::lang('rankings.fightstats'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/bestduelist/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.bestduelist'));  ?></li>
			 <li><?php echo html::anchor('page/rankings/char/honorpoints/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.honorpoints'));  ?></li>
			 <li><?php echo html::anchor('page/rankings/char/battlechampion/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.battlechampion'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/arrests/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.arrests'));?></li>
			 <li><?php echo html::anchor('page/rankings/char/boughtdoubloons/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.mesupporters')); ?></li>
			 <li><?php echo html::anchor('page/rankings/char/fpcontribution/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.fpcontribution'));  ?></li>
			 <li><?php echo html::anchor('page/rankings/char/mostcharitable/' . $mode  . '/' . $centeronplayer,  kohana::lang('rankings.mostcharitable'));  ?></li>
			  
		</ul>
	</li>
</ul>

<ul class='dropdown' style='float:right'>
	<li><?php echo html::anchor('page/rankings/' . $type . '/' . $category . '/top25/false', 'Top 25'); ?></li>
	<li><?php echo html::anchor('page/rankings/' . $type . '/' . $category . '/all/true', 'Overall'); ?></li>
</ul>

<div style='clear:both'></div>
<br/>
<div id='helper'>
  <div id='helpertext'>
		<?php 
		if ( count($rankings) > 0 )
			echo kohana::lang('rankings.helper', Utility_Model::format_datetime( $rankings['extractiontime'] )) 
		?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Badges',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<br/>

<h5><?php echo kohana::lang('rankings.' . $category ) . ': ' . $modelabel ?></h5>

<br/>

<?php 

if ( count($rankings) == 0 )
		echo "<p class='center'>" . kohana::lang( 'rankings.ranking_norankings') . " </p>"; 
else
{	
?>

<?php if ( $mode == 'all' ) echo $pagination->render(); ?>

<br/>

<center>
<table>
<th class='center'  width='5%' ></th>
<th class='center'  width='10%' ><?php echo kohana::lang('rankings.ranking_prevrank')?></th>
<th class='center'  width='5%' ><?php echo kohana::lang('rankings.ranking_rank')?></th>
<th class='center'  width='25%' ><?php echo kohana::lang('global.name')?></th>

<? if ( $category == 'richestchars' )	{ ?>
<th colspan="2"></th>
<? }
else
{
?>
<th class='center'  width='25%'><?php echo kohana::lang('global.title')?></th>
<th class='center'  width='30%'><?php echo kohana::lang('rankings.ranking_score')?></th>
<? 
} 
?>

<?php	
	$i = 1 + $pagination -> sql_offset;
	$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
	//var_dump($rankings[$category]);exit;
	foreach ( $rankings[$category] as $r )
	{
		if ( !is_null( $limit ) and $r['stat']->position > $limit )
			break;
			
		$class = ( $i % 2 == 0 ) ? 'alternaterow_1' : '' ;
		
		if ( $type == 'char' and $r['stat'] -> stats_id == $char -> id )
			$class = 'evidence3'; 
		if ( $type == 'kingdom' and $r['stat'] -> stats_id == $char -> region -> kingdom -> id )
			$class = 'evidence3'; 				
		if ( $type == 'region' and $r['stat'] -> stats_id == $char -> region -> id )		
			$class = 'evidence3'; 				
		if ( $type == 'church' and $r['stat'] -> stats_id == $char -> church -> id )		
			$class = 'evidence3'; 	
			
		
		echo "<tr class='$class' >";
		
		// posizione
		
		echo "<td class='center'>";
		if ( $r['stat'] -> prevposition > $r['stat'] -> position ) 
			echo html::image( 'media/images/template/up.png' );
		if ( $r['stat'] -> prevposition == $r['stat'] -> position ) 
			echo html::image( 'media/images/template/neutral.png' );
		if ( $r['stat'] -> prevposition < $r['stat'] -> position ) 
			echo html::image( 'media/images/template/down.png' );			
		echo "</td>";

		echo "<td class='center' >" . ($r['stat'] -> prevposition == 999999 ? '-' : $r['stat'] -> prevposition) . "</td>";
		echo "<td class='center'>" . $r['stat'] -> position . "</td>";
	
		// Valore del punteggio
	
		if ( $category == 'oldestchars' )		
			$value = Utility_Model::d2y( time(), ( $r['stat'] -> value )); 
		elseif ( in_array($category, array( 
			'richestchars', 
			'poorestchars', 
			'richestkingdoms', 
			'richestcities', 
			'boughtdoubloons',
			'activekingdoms') ) )
			$value = '';		
		elseif ( $category == 'fightstats' )
			$value = kohana::lang( $r['stat']->entity, $r['stat']->param1, $r['stat']->param2, $r['stat'] -> value ) ;		
		elseif ( $category == 'bestduelist' )
			$value = kohana::lang( $r['stat']->entity, $r['stat']->value, $r['stat']->param1, $r['stat']->param2, round($r['stat']->param1/$r['stat']->param2,2)*100 ) ;
		else
			$value = kohana::lang( $r['stat']->entity, $r['stat']->value);
		
		
		// Etichetta
		
		if ( in_array($category, 
			array ( 'richestkingdoms', 'populatedkingdoms', 'richestcities', 'populatedcities', 
				'raiderskingdoms', 'raidedkingdoms', 'activekingdoms' ) ) )
			echo "<td class='center'>" . kohana::lang($r['stat'] -> stats_label) . "</td>" ;
		elseif ( in_array( $category, array ( 'mostfollowedchurch') ) )
			echo "<td class='center'>" . kohana::lang($r['stat'] -> stats_label) . ' (' . kohana::lang($r['stat'] -> param1) . ") </td>" ;
		elseif ( in_array( $category, array ( 'mostreligious', 'mostcharitable') ) )
			echo "<td class='center'>" . Character_Model::create_publicprofilelink($r['stat'] -> stats_id, $r['stat'] -> stats_label) . "</td>" ;				
		else
			echo "<td class='center'>" . Character_Model::create_publicprofilelink($r['stat'] -> stats_id, $r['stat'] -> stats_label) . "</td>" ;
		
		// Titolo collegato
		echo "<td class='center' >" ;
		
		if ( $type == 'char' )
			echo $r['title'] ;
		else
			echo '';
		
		echo "</td>";
		
		echo "<td class='center'>" .  $value . '</td>';								
		echo "</tr>";		
		$i++;
	
	}
	
?>
</table>
</center>
<br/>

<?php if ( $mode == 'all' ) echo $pagination->render(); ?>
<?php } ?>
<br/>
<br style='clear:both' />
