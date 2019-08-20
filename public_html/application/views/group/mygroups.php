<div class="pagetitle"><?php echo kohana::lang('character.mygroups') ?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div class="submenu">
	<?= $secondarymenu; ?>
</div>

<div style='float:right'>
<?= html::anchor(
	'/group/create', 
	kohana::lang('groups.create_a_group'), 
	array ('class' => 'button button-medium' ));
?>
</div>

<br style='clear:both'/>

<?php
	
	if ( $groups -> count() == 0 )
	{
?>

	<div>
	<?= kohana::lang('groups.no_groups_found'); ?>
	</div>
<?	
	}
	else
	{
?>
	<br/>
	<table class='border'>
		
	<th colspan="2" width="60%"><?=kohana::lang('global.name');?></th>
	<th width="10%"><?=kohana::lang('groups.type');?></th>
	<th width="10%"><?=kohana::lang('groups.secret');?></th>
	<th></th>

<?	
	$k = 0;
	foreach ( $groups as $group )
	{
		$class = ($k % 2 == 0) ? 'alternaterow_1' : 'alternaterow_2';

?>			
	<tr class="<?= $class; ?>">
	<td valign="top">		
	<?
		$file = "media/images/groups/".$group->id.".png";	
		if ( file_exists( $file) )
			echo html::image(
			'media/images/groups/'.$group->id.'.png?r='.time(),
			array( 
				'class' => 'size75' 
			));
		else
			echo html::image('media/images/template/group_no_image.png',
			array( 
				'class' => 'size75' 
			));
	?>
	</td>
	<td style="vertical-align:top">
	<?
	
		$nummembers = ORM::factory('group_character') -> 
			where( array( 'group_id' => $group->id, 'joined' => '1' ) )->count_all();
		echo "<h3>";		
		echo html::anchor( '/group/view/'.$group -> id, 
			$group->name ) .' ('. ($nummembers + 1) . ' '. kohana::lang('groups.members').')';
		echo "</h3>";
		
		echo '<i>' . $group->description . '</i></td>';
	?>
	</td>
	<td class="center">	
	<?
		if ( $group -> type == 'groups.mercenary' )
			echo kohana::lang('groups.military') . '-' . kohana::lang( $group -> type );
		else
			echo kohana::lang( $group -> type );
	?>
	</td>
	<td class="center">
	<?
		if ($group -> secret) 		
			echo kohana::lang('global.yes');
		else 
			echo kohana::lang('global.no');
	?>
	</td>
	<td class="center">		
		<?
			if ($group -> character_id == $character -> id)
			{
				echo html::anchor( '/group/upload_image/'.$group->id, kohana::lang('groups.upload_image') ) . '<br/>';	echo html::anchor( '/group/edit/'.$group->id, kohana::lang('groups.edit') ) . '<br/>';
				echo html::anchor( '/group/message/'.$group->id, kohana::lang('groups.mass_email') ) . '<br/>'; 
				echo html::anchor( '/newchat/groupchat/'.$group->id, kohana::lang('global.chat'), 
					array( 'target' => 'blank' ) ) . '<br/>';
				echo html::anchor( '/group/transfer_leadership/'.$group->id, kohana::lang('groups.transfer_leadership') ) . '<br/>';				
				echo html::anchor( '/group/delete/'.$group->id, kohana::lang('groups.delete'), array('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )). '<br/>';
			}
			else
			{				
				echo html::anchor( '/newchat/groupchat/'.$group->id, kohana::lang('global.chat'), 
					array( 'target' => 'blank' ) ) . '<br/>';
				
				if ( $group -> classification == 'tutor' )
				{
					if ( $group -> character_id == $character -> id) 
						echo 
							html::anchor( '/group/message/'.$group->id, kohana::lang('groups.mass_email') ) . '<br/>'; 
				}
				else
					echo 
						html::anchor( '/group/message/'.$group->id, kohana::lang('groups.mass_email') ) . '<br/>'; 
				
				echo html::anchor( '/group/leave/'.$group -> id, kohana::lang('groups.leave_group'),
					array('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
				);
			}
		?>
	</td>
	</tr>
	<?
		$k++;
	}
	?>	
	</table>		
	<?
	}
	?>
	
<br style="clear:both;" />
