<head>
<script>

$(document).ready(
 function()
 {	
	
	$('a.expand').click(function()
	{		
		$('div#members_' + $(this).attr("name")).show();
	});
	$('a.collapse').click(function()
	{ 
		$('div#members_' + $(this).attr("name")).hide();
	});
 }
);
</script>
</head>

<div class="pagetitle"><?php echo kohana::lang('structures_nativevillage.attack') ?></div>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('structures_nativevillage.helper')?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_Conquering_Independent_Region', 
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<br/>

<?php
if ( count( $groups ) == 0 )
	echo '<p><i>' . kohana::lang('structures_nativevillage.nomilitarygroups') . '</i></p>';
else
{
echo form::open('nativevillage/attack/');
echo form::hidden('structure_id' , $structure -> id ); 
foreach ( $groups as $group )
{
	
	echo "<p style='text-align:left' id='group_" . $group-> id . "'>";
	
	echo "<div style='float:left'>" . '<b>' . kohana::lang( 'groups.group' ) . ': ' . $group->name . '</b>' . 
		'&nbsp;' . html::anchor ( '#', '[+]', array('class' => 'expand', 'name' => 'group_' . $group->id ) ) . '&nbsp;' 
		.  html::anchor ( '#', '[-]', array('class' => 'collapse', 'name' => 'group_' . $group->id ) );
	echo "</div>";
	 
	echo "<div style='float:right'>" . kohana::lang('structures_nativevillage.attackwiththisgroup') . ' &nbsp;' . 
		form::radio( 'attackwithgroup', $group->id, false ) . 
	"</div>";
	echo "<div style='clear:both;text-align:left;'>";
		echo '<b>' . kohana::lang( 'global.description' ) . ':' . '</b>' . '<i>' . $group -> description . '</i>';
	
	echo "</div>";
	echo "<div style='display: none;text-align:left;' id='members_group_" . $group->id ."'>";
	
	$members = $group -> get_all_members( 'joined', $group -> id ); 
	echo '<br/>';
	if ( count ($members) == 0 )
		echo kohana::lang('groups.nomembers');
	else	
	{
		echo '<table>';
		echo '<th>' . kohana::lang('global.name') . '</th>';
		echo '<th>' . kohana::lang('character.actual_position') . '</th>';
		echo '<th>' . kohana::lang('global.status') . '</th>';
		echo '<th></th>'; 
		
		foreach ($members as $member )
		{
			echo '<tr><td>' . $member -> character -> name . '</td>';
			
			$current_position = $member -> character -> get_currentposition();			
                        kohana::log('info', kohana::debug( $current_position ) );
			
			if ( is_null( $current_position ) or $current_position == false )
			{
				echo 				
				'<td>' .  kohana::lang( 'regionview.traveling' ) . '</td>' .
				'<td>?</td>';
			}
			else
			{
				echo '<td>' .  kohana::lang( $current_position -> name ) . '</td>';
				if ( $current_position -> id == $structure -> region_id )				
				{
					if ( !is_null ( $member -> character -> get_status( $member -> character -> id )  ))
						echo '<td>' . kohana::lang('regionview.' . $member -> character -> status. '_shortmessage' ) . '</td>'; 
					elseif ( $member -> character -> get_age() < kohana::config('medeur.mindaystofight') ) 
						echo '<td>' . kohana::lang('ca_attackir.tooyoungtofight') . '</td>'; 
					else
						echo '<td>' . kohana::lang('ca_attackir.readyforbattle') . '</td>'; 
				}
				else
						echo '<td>?</td>'; 
			}
				
			echo '<td>' . html::anchor( '/message/write/0/new/' . $member -> character -> id , '[' . kohana::lang('message.write_scroll') . ']') . '</td>';
			echo '</tr>'; 
		}
		echo '</table>';
	}
	echo "</div>";	
	echo "</p>";
	
}
echo '<center>' . 
	form::submit( array( 'id' => 'submit', 'class'=> 'submit', 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 'value' => Kohana::lang('global.confirm'))) . 
'</center>'; 
}
?>
<br style='clear:both'/>	
