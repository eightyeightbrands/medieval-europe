<div class="pagetitle"><?php echo kohana::lang('gameevents.event',
$gameevent -> name) ?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<br/>
<? 
if ($gameevent -> loaded == false ) 
{
?>
<p><?= kohana::loang('gameevents.error-eventdoesnotexist')?></p>
<? 
}
else
{
?>

<p class='center'>
<?= $gameevent -> description ; ?>
<br/>
<?= kohana::lang('gameevents.rules',
	html::anchor( 
	$gameevent -> rulesurl, 
	kohana::lang('global.here'),
	array('target' => 'new') 
	)
);
?>
</p>

<br/>

<p class='center'>
<?= 
kohana::lang('gameevents.subscriptionprice',
$gameevent -> doubloons, 
$gameevent -> silvercoins,
Utility_Model::format_datetime( $gameevent -> subscriptionenddate)
); ?>
</p>

<br/>

<h2 class='center'>
<?= kohana::lang('gameevents.jackpots',
$doubloonsjackpot, $silvercoinsjackpot ); ?>
<br/>
<?= kohana::lang('gameevents.subscriptions', $totalsubscriptions); ?>
</h5>

<br/>



<br/>
<br/>
<?= form::open('gameevent/subscribe');?>
<?= form::hidden('cfggameeventid', $gameevent->id);?>
<div class='center'>

	<?php echo 
		form::submit( 
			array( 
			'name' => 'subscribedoubloons',
			'class' => 'button button-large', 
			'value' => kohana::lang('gameevents.subscribedoubloons'),
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')')
		);
	?>
	&nbsp;
	<?php echo 
		form::submit( 
			array( 			
			'name' => 'subscribesilver',
			'class' => 'button button-large', 
			'value' => kohana::lang('gameevents.subscribesilvercoins'),
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')')
		);
	?>
</div>
<?= form::close();?>

<? } ?>

<br style='clear:both'/>