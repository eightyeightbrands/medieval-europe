<div class="pagetitle"><?php echo kohana::lang('message.view_pagetitle'); ?></div>

<?php echo $submenu ?>

<?php if (!$bonus) { ?>
<div style='float:right'>
<?php 
	echo html::anchor( 'bonus/acquire_professionaldesk_bonus/',
		kohana::lang('message.upgradedesk'), array( 'class' => 'button button-small') );
?>
</div>
<?php } ?>

<? if (
		Auth::instance() -> logged_in('admin') 
		or
		Auth::instance() -> logged_in('doubloonreseller') )
	{ 
		$doubloon_id = Character_Model::find_item( $char -> id, 'doubloon' );
		if (!is_null( $doubloon_id ) )
		{	
?>
<div style='float:right;;margin-right:5px'>
	<?= html::anchor("item/senddoubloons/{$sender->id}", 'Invia dobloni', array( 'class' => 'button button-small')); ?>
</div>
<?  }
	} 
?>

<br style='clear:both'/>

<hr style='margin:5px 0px 5px 0px'/>

<?php

	if ( ! $sender ->loaded )
		$sendername = kohana::lang('global.systemmessage');
	else
		$sendername = $sender -> name ;

	if ( ! $sender ->loaded )
		$sendername = kohana::lang('global.systemmessage');
	else
		$sendername = $sender -> name ;
?>

<div>
	<div>
	<?= Character_Model::create_publicprofilelink( null, $sendername) . 
		' to ' . 
		Character_Model::create_publicprofilelink( null, $recipient -> name ) . ' | ' . '<strong>' . $message -> subject . '</strong>';
	?>
	</div>	

	<div style='float:right'>
	On <?= Utility_Model::format_datetime($message->date); ?>
	</div>
	
</div>	

<br style='clear:both'/>

<div style='width:100%'>
	<?php 
	
	if ( $message -> type == 'weddingproposal' )
	{
		echo  html::anchor ('character/acceptweddingproposal/1/' .$message -> id, '[' . kohana::lang('global.accept') . ']') ."&nbsp;&nbsp;" ;
		echo  html::anchor ('character/acceptweddingproposal/0/' .$message -> id, '[' . kohana::lang('global.deny') . ']') ."&nbsp;&nbsp;" ;
	}

	if ( $sendername != kohana::lang('global.systemmessage') and $type != 'sent' )
	{
		echo html::anchor('message/write/' . $message -> id . '/reply','['.kohana::lang('message.reply').']') ."&nbsp;&nbsp;" ;
	}

	echo  html::anchor ('message/write/' . $message -> id . '/forward', '[' . kohana::lang('message.forward') . ']') ."&nbsp;&nbsp;" ;
	echo  html::anchor ('message/archive/' . $type . '/' . $message -> id, '[' . kohana::lang('message.archive') . ']') ."&nbsp;&nbsp;" ;
	echo  html::anchor ('message/delete/' . $type . '/'.$message->id, '[' . kohana::lang('message.delete') . ']',
		array ('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') 
	 ) ;
	?>	
</div>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<div>

	<div style='float:left;margin-right:5px' id='frame_s'>
	<?= Character_Model::display_avatar($sender -> id, 's', 'charpic_s	') ;?>
	</div>

	<div style='width:85%;float:left;border-left:1px solid #bbb;padding-left:5px;'>
	<?php echo Utility_Model::bbcode( $message -> body );?>
	</div>
	
</div>

<br style='clear:both'/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div style='width:100%'>
	<?php 
	
	if ( $message -> type == 'weddingproposal' )
	{
		echo  html::anchor ('character/acceptweddingproposal/1/' .$message -> id, '[' . kohana::lang('global.accept') . ']') ."&nbsp;&nbsp;" ;
		echo  html::anchor ('character/acceptweddingproposal/0/' .$message -> id, '[' . kohana::lang('global.deny') . ']') ."&nbsp;&nbsp;" ;
	}

	if ( $sendername != kohana::lang('global.systemmessage') and $type != 'sent' )
	{
		echo html::anchor('message/write/' . $message -> id . '/reply','['.kohana::lang('message.reply').']') ."&nbsp;&nbsp;" ;
	}

	echo  html::anchor ('message/write/' . $message -> id . '/forward', '[' . kohana::lang('message.forward') . ']') ."&nbsp;&nbsp;" ;
	echo  html::anchor ('message/archive/' . $type . '/' . $message -> id, '[' . kohana::lang('message.archive') . ']') ."&nbsp;&nbsp;" ;
	echo  html::anchor ('message/delete/' . $type . '/'.$message->id, '[' . kohana::lang('message.delete') . ']',
		array ('onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')') 
	 ) ;
	?>	
</div>

<br style="clear:both;" />

