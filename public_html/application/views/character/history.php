<?php echo $header ?>

<br/>

<h5><?php echo kohana::lang('character.description')?></h5>

<br/>

<div>
	
	<div id='frame' style='margin-right:1%'>
		<?php echo Character_Model::display_avatar( $character -> id, 'l', 'charpic');?>
	</div>
	
	<div style="float:left;width:75%">
		
		<p>		
		
		<?php 
			if ( !empty( $character->description))
			{
				echo Utility_Model::bbcode( $character -> description );
				echo "<br/>";
				echo "<br/>";
			}
		?>
		
		<?php 
			if (empty( $character -> history))
				echo kohana::lang('character.nohistoryprovided');
			else
				echo Utility_Model::bbcode($character->history) 
		?>		
		</p>
		
	</div>
	
</div>

<br style='clear:both'/>
<br/>
<br/>

<h5><?php echo kohana::lang('character.importantevents')?></h5>

<br/>

<?php 
if ( count( $permanentevents ) == 0)
	echo kohana::lang('character.noimportantevents');
else
{
?>
<table>
<?
	$r = 0;
	foreach ( $permanentevents as $permanentevent )
	{			
		$class = ( $r % 2 ) ? 'alternaterow_1' : '';
?>		
	<tr class="<?=$class;?>">
	<td width='15%'><?= Utility_Model::format_date( $permanentevent -> timestamp ); ?></td>
	<td width='85%'><?= My_I18n_Model::translate($permanentevent -> description ) ?> </td>
	</tr>	
<?	
	$r++;
	}	
?>
</table>
<?	
}
?>