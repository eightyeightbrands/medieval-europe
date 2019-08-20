<div class="pagetitle"><?php echo kohana::lang('quests.view_pagetitle')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<h5><?php echo kohana::lang('global.path') . ': ' . $info['path'] . ' ' .
	kohana::lang('global.quest') . ': '. $info['descriptivename'] ?></h5>
<br/>
<div>
	
	<div id='queststepstatus'>
		<span style='float:left'><?php echo kohana::lang('quests.stepprogress') ?>:&nbsp;</span>
		<?php 
		$i = 1;
		foreach ( $info['steps'] as $step ) 
		{
			
			if ( $step -> status != 'nonexistent' )
			{
		?>
			<div class='queststep <?php echo $step -> status ?>'>
				<?php echo $i ?>
			</div>
		<?php
			}
		$i++;
		} 
		?>
	</div>
	
	<p>
	ID: <?php echo $info['id']?><br/>
	<?php echo kohana::lang('global.name') ?>: <?php echo $info['descriptivename']?><br/>
	<?php echo kohana::lang('global.author') ?>: <?php echo Character_Model::create_publicprofilelink($info['author_id'])?><br/>
	<?php echo kohana::lang('global.status') ?>: <?php echo kohana::lang('global.status_' . $info['status'] )?><br/>
	<?php echo kohana::lang('quests.rewards') ?>: <span class='evidence'><?php echo kohana::lang($info['rewards'])?></span><br/>
	</p>
	
	
</div>

<h5><?php echo kohana::lang('quests.steps') ?></h5>
<br/>
<?php 
foreach ( $info['steps'] as $step )
{
	if ( $step -> status != 'nonexistent' )
	{
		if ( $step -> id < $info['currentstep'] -> id )
			echo $step -> id . '. ' . kohana::lang( $step -> summary ) . '<br/>' ;
		
		if ( $step -> id == $info['currentstep'] -> id )
			echo '<b>' . $step -> id . '. ' . kohana::lang( $step -> summary ) . '</b><br/>' ;		
		
	}
}
?>

<br style='clear:both'/>
