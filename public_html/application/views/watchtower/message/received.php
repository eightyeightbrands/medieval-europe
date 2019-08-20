<head>
<?php echo html::script('media/js/setallcheckboxes.js', FALSE)?>
</head>

<div class="pagetitle"><?php echo kohana::lang('message.received_pagetitle'); ?></div>

<?php echo $submenu ?>

<?php if (!$bonus) { ?>
<div style='float:right;margin:0px 0px 10px 0px'>
<?php 
	echo html::anchor( 'bonus/acquire_professionaldesk_bonus/',
		kohana::lang('message.upgradedesk'), array( 'class' => 'button button-medium button-red') );
?>
</div>
<?php } ?>

<?php if ( $bonus ) { ?>

<div>

	<div style='float:left'>
	<?php
	echo form::open('message/received', array('method' => 'get' ) );
	echo form::label( kohana::lang('message.subject')) . '&nbsp;' . 
		 form::input( array( 'id' => 'subject', 'name' => 'subject', 'class' => 'input-normal') );
	echo '&nbsp;';
	echo form::label( kohana::lang('message.sender')) . '&nbsp;' . 
		 form::input( array( 'id' => 'sender', 'name' => 'sender', 'class' => 'input-normal') );
	?>
	</div>

	<div style='float:right'>
	<?php
	echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'value' => kohana::lang('global.search')) );
	echo form::submit( array( 'id' => 'reset', 'class' => 'button button-small', 'value' => kohana::lang('global.reset') ) );
	echo form::close();
	?>
	</div>
	
</div>
<br style='clear:both'/>
<div><b><?php echo kohana::lang('global.criteria') . $criteria; ?></b></div>
<?php } ?>

<div class="pagination"><?php echo $pagination->render(); ?></div>

<?php echo form::open('message/deleteselectedmessages') ?>
<?php echo form::hidden('type', 'received') ?> 
<table class='smallfonts' id='msgreceived'>
<th><?php echo form::checkbox(array('id'=>'selectallcheckboxes')) ?></th>
<th width="5%"><?php echo kohana::lang('message.archived'); ?></th>
<th width="20%"><?php echo kohana::lang('message.date'); ?></th>
<th width="20%"><?php echo kohana::lang('message.from'); ?></th>
<th width="35%"><?php echo kohana::lang('message.subject'); ?></th>
<th width="20%"><?php echo kohana::lang('message.options'); ?></th>

<?php 
if ( count($messages) == 0 ) 
	echo "<tr><td class='center' colspan='5'><br/>" . kohana::lang('message.nomessages') . "</td></tr>" ;

else
{
		$k = 0;
		foreach($messages as $message)
		{
			$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2' ;			
			$class .= ($message -> isread == FALSE) ? ' unread' : '' ;
			echo "<tr class = '$class'>";
			echo '<td>'. form::checkbox('messages['.$message -> id.']', true, false) . '</td>';			
			echo "<td class='center'>" . $message -> archived . "</td>";			
			echo '<td>'. Utility_Model::format_datetime( $message -> date ) . '</td>';
			echo '<td>' . html::anchor('character/publicprofile/' . $message -> fromchar_id, $message->sender ).'</td>';
		
			echo "<td>" . html::anchor('message/view/received/'.$message->id, $message->subject).'</td>';
			echo "<td class='center'>";
			
			if ( $bonus )
			
				echo html::anchor('message/archive/received/'.$message->id, 
				html::image('media/images/template/archive_icon.png'), array( 
					'title' => kohana::lang('message.archive'),
					'style' => 'margin-left:3px'
					));
			
			echo html::anchor('message/delete/received/'.$message->id, 
				html::image('media/images/template/delete_icon.png'), array( 
				'title' => kohana::lang('message.delete'),
				'style' => 'margin-left:3px'				
				));			
			
			if ( $message -> sender != kohana::lang('global.noone')
			)
			{
				echo html::anchor('message/write/'. $message -> id . '/reply', 
				html::image('media/images/template/reply_icon.png'), array( 'title' => kohana::lang('message.reply'),
				'style' => 'margin-left:3px'				
				));			
			}
			echo html::anchor('message/write/'. $message -> id . '/forward',  
				html::image('media/images/template/forward_icon.png'), array( 'title' => kohana::lang('message.forward'),
				'style' => 'margin-left:3px'));							
			echo "</td>";
			echo '</tr>';
			$k++;
		}
	}
?>
</table>

<div class="pagination"><?php echo $pagination->render(); ?></div>
<br/>
<?php 
echo form::submit( 
	array ( 	
	'class' => 'button button-medium' , 
	'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
,kohana::lang('message.delete_selected'))?>
<?php echo form::close();?>

<br style="clear:both;" />
