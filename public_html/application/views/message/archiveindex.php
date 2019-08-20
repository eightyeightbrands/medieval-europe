<head>
<?php echo html::script('media/js/setallcheckboxes.js', FALSE)?>
</head>

<div class="pagetitle"><?php echo kohana::lang('message.archiveindex_pagetitle'); ?></div>

<?php echo $submenu ?>

<div>

	<?php echo kohana::lang('message.archiveinfo',	
		$archivedmessages,
		$capacity,
		round($archivedmessages/$capacity,2)*100
		); ?>

	<div style='float:right;margin-right:0px'>

	<?php 
		echo html::anchor( 'bonus/acquire_archivecapacity_bonus/',
			kohana::lang('message.upgradearchive'), array( 'class' => 'button button-red') );
	?>
	</div>
</div>		
<br/>
<?php echo form::open('message/deleteselectedmessages') ?>
<?php echo form::hidden('type', 'received') ?> 
<table class='smallfonts' id='msgreceived'>
<th><?php echo form::checkbox(array('id'=>'selectallcheckboxes')) ?></th>
<th width="25%"><?php echo kohana::lang('message.date'); ?></th>
<th width="20%"><?php echo kohana::lang('message.from'); ?></th>
<th width="30%"><?php echo kohana::lang('message.subject'); ?></th>
<th width="20%"><?php echo kohana::lang('message.options'); ?></th>

<?php 
if ( count($messages) == 0 ) 
	echo "<tr><td class='center' colspan='5'><br/>" . kohana::lang('message.nomessages') . "</td></tr>" ;
else
{
		$k = 0;
		
		foreach($messages as $message)
		{
			$class = ( $k % 2 == 0 ) ? '' : 'alternaterow_1' ;						
			echo "<tr class = '$class'>";
			echo '<td>'. form::checkbox('messages['.$message['id'].']', true, false) . '</td>';			
			echo '<td>'. Utility_Model::format_datetime( $message['date'] ) . '</td>';
			echo '<td>' . html::anchor('character/publicprofile/' . $message['fromchar_id'], $message['from'] ).'</td>';
		
			echo "<td>" . html::anchor('message/view/received/'.$message['id'], $message['subject']).'</td>';
			echo "<td class='center'>";
			
			if ( $bonus )
			
				echo html::anchor('message/archive/received/'.$message['id'], 
				html::image('media/images/template/archive_icon.png'), array( 'title' => kohana::lang('message.archive')));
			
			echo html::anchor('message/delete/received/'.$message['id'], 
				html::image('media/images/template/delete_icon.png'), array( 'title' => kohana::lang('message.delete')));						
			echo "</td>";
			echo '</tr>';
			$k++;
		}
	}
?>
</table>

<?php echo form::submit( array ( 'id'=> 'submit', 'class' => 'button button-medium' , 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
,kohana::lang('message.delete_selected'))?>
<?php echo form::close();?>

<br style="clear:both;" />
