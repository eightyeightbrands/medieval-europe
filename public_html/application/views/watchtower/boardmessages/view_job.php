<div class="pagetitle"><?php echo kohana::lang($currentposition -> kingdom -> get_name() ) . ' - '
	. kohana::lang( 'boardmessage.announcementboard' ) . ' - '
	. kohana::lang( 'global.message' ) . ': ' . $message -> id ;	?>
</div>

<br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>

<div class='center'>
<?php 
echo html::anchor(
	'boardmessage/index/job', 
	kohana::lang('boardmessage.announcementboard'),
	array( 'class' => 'button button-small'));
?>
</div>

<fieldset class='alternaterow_1'>
<legend>ID: <?=$message->id; ?></legend>
	
	<div id='suggestionleftpanel' style='float:left;width:20;text-align:center;margin-right:1%'>			
		<div><?php 
		echo Character_Model::display_avatar( $poster -> id, 'l', 'border' ); ?></div>
		<div id='suggestioncaption'>
		<?php echo Character_Model::create_publicprofilelink( $poster -> id, null );?>		
		</div>
	</div>
	<div id='suggestionrightpanel' style='float:left;width:77%;border-left:1px solid;padding:1%;'>		
		<div>
			<div class='right'>
				<?php
					echo 'ID: <b>' . $message -> id. '</b>, ' 
					. kohana::lang('boardmessage.messagecategory') . '<b>' .
						kohana::lang('boardmessage.messagecategory' . $message -> category ) . '</b>, '
					. kohana::lang('boardmessage.visibility_' . $message -> visibility ) . ', '
					. kohana::lang('boardmessage.published', Utility_Model::format_datetime($message -> created) ) . '<br/>'			
					. kohana::lang('boardmessage.expireson', Utility_Model::format_datetime($message -> created + $message -> validity *24*3600) ) . ', ' 
					. kohana::lang('boardmessage.starpoints', $message -> starpoints  ) . ', '
					. kohana::lang('admin.timesread', $message -> readtimes  ) ;
				?>		
			</div>
			
			<br/>
			<h2><?php echo $message -> title ?></h2>		
			<p style='max-width:600px;word-wrap:break-word;'>
			<?php 
				$structure = StructureFactory_Model::create( null, $message -> spare3 ); 
				if ( !is_null($structure) )
					echo kohana::lang('boardmessage.jobstructure') . ': ' 
						. kohana::lang( $structure -> structure_type -> name ) . ' - ' . kohana::lang( $structure -> region -> name );
				else
					echo kohana::lang('boardmessage.jobstructure') . ': '  . kohana::lang('global.none' ); 
				echo '<br/>'; 
				echo kohana::lang('boardmessage.workduration') . ': ' . $message -> spare1 . '<br/>';
				echo kohana::lang('boardmessage.hourlywage') . ': ' . $message -> spare4 . '<br/><br/>';		
				echo Utility_Model::bbcode($message -> message);		
			?>
			</p>
		</div>
	</div>
	<br style='clear:both'/>
	
	<div id='suggestionfooter' style='float:right;bottom-margin:0'>
	<? 		
		echo html::anchor('boardmessage/view/' . $message -> id, kohana::lang('global.read') ) ;
		echo "&nbsp;";
		if ( $message -> character_id == $reader -> id or $auth -> logged_in('admin') or $auth -> logged_in('staff') )		
		{
			echo html::anchor('boardmessage/edit/' . $message -> id, kohana::lang('global.edit') ) ;
			echo "&nbsp;";
		}
		if ( $message -> character_id == $reader -> id or $auth -> logged_in('admin') or $auth -> logged_in('staff') )
		{
			echo html::anchor('boardmessage/delete/'. $message -> id, kohana::lang('global.delete'),
				array('onclick' => 'return confirm(\'' . kohana::lang('boardmessage.confirm_delete').'\')') ) ;
			echo "&nbsp;";
		}
		echo html::anchor('boardmessage/report/' . $message -> id, kohana::lang('boardmessage.report'));
		echo "&nbsp;";
		echo html::anchor('message/write/0/new/' . $message -> character_id, kohana::lang('boardmessage.contact') ) ;
		echo "&nbsp;";
		if ( $message -> character_id == $reader -> id  )
		{
			echo html::anchor('boardmessage/give_globalvisibility/' . $message -> id, kohana::lang('boardmessage.globalvisibility'),
				array('onclick' => 'return confirm(\''.kohana::lang('boardmessage.confirm_globalvisibility').'\')') ) ;
			echo "&nbsp;";
		}
		if ( $message -> character_id == $reader -> id  )
		{
			echo html::anchor('boardmessage/bump_up/' . $message -> id, kohana::lang('boardmessage.bumpup'),
				array('onclick' => 'return confirm(\'' . kohana::lang('boardmessage.confirm_bumpup').'\')') ) ;
			echo "&nbsp;";
		}
				
		if ( $message -> character_id != $reader -> id  and $message -> spare3 != 0 and $message -> status == 'published' )
		{
			echo html::anchor('jobs/accept/' . $message -> id, kohana::lang('boardmessage.accept'),
				array('onclick' => 'return confirm(\'' . kohana::lang('global.confirm_operation').'\')') ) ;	
		
		}
	?>
	</div>
	
</fieldset>

<br style="clear:both;" />
