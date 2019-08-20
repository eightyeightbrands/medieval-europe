<head>
<?php echo html::script('media/js/setallcheckboxes.js', FALSE)?>
</head>

<div class="pagetitle"><?php echo kohana::lang('message.sent_pagetitle'); ?></div>

<?php echo $submenu ?>

<?php if (!$bonus) { ?>
<div style='float:right;margin:0px 0px 10px 0px'>
<?php 
	echo html::anchor( 'bonus/acquire_professionaldesk_bonus/',
		kohana::lang('message.upgradedesk'), array( 'class' => 'button button-medium button-red') );
?>
</div>
<?php } ?>

<!-- form ricerca -->
<?php if ( $bonus ) { ?>

<div>
	<div style='float:left'>
	<?php
	echo form::open('message/sent', array('method' => 'get' ) );
	echo form::label( kohana::lang('message.subject')) . '&nbsp;' . 
		 form::input( array( 'id' => 'subject', 'name' => 'subject', 'class' => 'input-normal') );
	echo '&nbsp;';
	echo form::label( kohana::lang('message.to')) . '&nbsp;' . 
		 form::input( array( 'id' => 'recipient', 'name' => 'recipient', 'class' => 'input-normal') );
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

<!-- end forma ricerca -->


<?php echo form::open('message/deleteselectedmessages') ?>
<?php echo form::hidden('type', 'sent') ?> 
<div class="pagination"><?php echo $pagination->render	(); ?></div>

<table class='smallfonts'	>
<th><?php echo form::checkbox(array('id'=>'selectallcheckboxes')) ?></th>
<th width="5%"><?php echo kohana::lang('message.archived'); ?></th>
<th width="20%"><?php echo kohana::lang('message.date'); ?></th>
<th width="20%"><?php echo kohana::lang('message.to'); ?></th>
<th width="35%"><?php echo kohana::lang('message.subject'); ?></th>
<th width="20%"><?php echo kohana::lang('message.options'); ?></th>

<?php 
if ( count($messages) == 0 ) 
	echo "<tr><td class='center' colspan='5'><br/>" . kohana::lang('message.nomessages') . "</td></tr>" ;
else
{
	$k = 0;
	
	foreach( $messages as $message)
	{
		$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2' ;			
		$class .= ($message -> isread == FALSE) ? ' unread' : '' ;
		echo "<tr class='$class'>";
		echo '<td>'. form::checkbox('messages['.$message->id.']', true, false) . '</td>';
		echo "<td class='center'>" . $message -> archived . "</td>";			
		echo '<td>'.Utility_Model::format_datetime( $message -> date ).'</td>';
		
		echo '<td>' . html::anchor('character/publicprofile/' . $message -> tochar_id, $message -> receiver ).'</td>';
		echo '<td>'. html::anchor('message/view/sent/' . $message->id, $message->subject).'</td>';			
		echo '<td>' ;
		
		if ( $bonus )			
			echo html::anchor('message/archive/received/'.$message->id, 
			html::image('media/images/template/archive_icon.png'), array( 'title' => kohana::lang('message.archive')));
		echo html::anchor('message/delete/sent/'.$message->id, 
			html::image('media/images/template/delete_icon.png'), array( 'title' => kohana::lang('message.delete')));	
		echo html::anchor('message/write/'. $message -> id . '/forward',  
			html::image('media/images/template/forward_icon.png'), array( 'title' => kohana::lang('message.forward')));					
		echo '</td>';
		echo '</tr>';
		$k++;
	}
}
?>
</table>

<div class="pagination"><?php echo $pagination->render	(); ?></div>
<br/>
<?php echo form::submit( array ( 'id' => 'submit', 'class' => 'button button-medium' , 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
,kohana::lang('message.delete_selected'))?>
<?php echo form::close();?>

<br style="clear:both;" />
