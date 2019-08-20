<div class="pagetitle">
<?php echo kohana::lang('kingdomforum.topictitle', $currenttopic->title); ?>
</div>

<div id='breadcrumb'>
<?php echo html::anchor('region/kingdomboards/' . $kingdom -> id, 
	kohana::lang('kingdomforum.forumtitle', kohana::lang($kingdom -> name))) . ' > ' . 
	html::anchor('region/kingdomtopics/' . $kingdom -> id . '/' . $currentboard -> id, $currentboard -> name ) . ' > ' .  
	$currenttopic->title;?>
</div>	


<div class="pagination">
<?php echo $pagination->render('extended'); ?>
</div>

<br/>
<table>
<?php
$r = 0;
foreach ( $rows as $row )
{
	
	$class = ( $r % 2 ) == 0 ? '' : 'alternaterow_1'; 
?>
	<tr class='<?php echo $class;?>'>
	<td class='center' valign="top" width='20%'>
	<?php 	
	echo Character_Model::create_publicprofilelink ($row -> author, null);?>
	<br/>
	<?php echo Character_Model::display_avatar($row -> author, 'l', 'border'); ?>	
	</td>
	<td valign='top'>
		<div class='topictitle'><?php echo $row -> title; ?></div>		
		<div class='topicdate small'><?php echo kohana::lang('kingdomforum.postedon', $row -> created);?></div>		
		<hr/>
		<br/>
		<?php echo Utility_Model::bbcode($row -> body) ; ?>
	</td>
	</tr>	
	<tr>
		<td colspan='2' class='right'>
		<?php 
			if (Kingdom_Forum_Topic_Model::haswriterights($char, $currenttopic -> kingdom_forum_board -> kingdom) )
			{
				echo 
				html::anchor('/region/editkingdomtopic/' . $row -> id, 
					kohana::lang('global.edit'),
					array('class' => 'button button-small')
					);
				echo "&nbsp;";
				echo 
				html::anchor('/region/deletekingdomtopic/' . $row -> id, 
					kohana::lang('global.delete'),
					array(
						'class' => 'button button-small',
						'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'
					));	
			}
		?>
		<td>
		
	</tr>
<?php 
$r++;
} 
?>
</table>

<div class="pagination"><?php //echo $pagination->render('extended'); ?></div>
