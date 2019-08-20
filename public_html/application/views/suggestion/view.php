<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
<script type="text/javascript">
$(function () {
 
  $("#rateYo").rateYo({
    starWidth: "20px",
	onSet: function (rating, rateYoInstance) 
	{		
		var rc = confirm( '<?= kohana::lang('suggestions.ratingsuggestion'); ?> ' + rating );
		if (rc == false)
			return false;
		var id = $(this).data('suggestionid');
		var url = '<?= url::base(true)?>' + 'suggestion/vote/' + id + '/' + rating ;		
		location.replace( url );
	}
  });
});
</script>

<div class="pagetitle"><?php echo kohana::lang('suggestions.suggestion') . '&nbsp;#' .  $suggestion -> id ;	?>
</div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div style='text-align:center'>
<?= html::anchor('suggestion/index', 'Back to Suggestion Index', array('class' => 'button button-medium')); ?>
</div>

<fieldset class='alternaterow_1'>
<legend>ID: <?=$suggestion->id; ?></legend>
	
	<div id='suggestionleftpanel' style='float:left;width:20%;text-align:center;margin-right:1%'>			
		<div><?php 
		echo Character_Model::display_avatar( $suggestion -> character_id, 'l', 'border' ); ?></div>
		<div id='suggestioncaption'>
		<?php echo Character_Model::create_publicprofilelink( $suggestion -> character_id, null );?>		
		</div>
	</div>

	<div id='suggestionrightpanel' style='float:left;width:75%;border-left:1px solid;padding:1%;'>		
		<div>
			<div class='right'>
				<?= kohana::lang('suggestions.rating', $suggestion -> baesianrating)?> - <?= kohana::lang('suggestions.votes', $suggestion -> votes); ?> - 
				<? if ($alreadyvoted == true ) { ?>
					You gave this suggestion a rating of <?= $charrating; ?>
				<? } else { ?>
					<div title="<?= kohana::lang('suggestions.votehelper')?>" id='rateYo' data-suggestionid="<?=$suggestion->id?>" style='float:right' ></div>
				<? } ?>		
			</div>
			
			<br/>
			<div style='float:right'><?= kohana::lang('global.status');?>: <span class='value'><?= kohana::lang('suggestions.status_'.$suggestion -> status);?></span></div>
			<br/>
			<? if ($suggestion->status=='fundable' 
			       or 
				   $suggestion->status=='funded'
				   or
				   $suggestion->status=='completed'
				   ) { ?>
				<div style='float:right;' class='right'>
				<?=kohana::lang('suggestions.sponsorstatus')?>: <span class='value'><?= $suggestion -> sponsoredamount?>/<?=$suggestion->quote?> (<?= min(1,round($suggestion -> sponsoredamount/$suggestion-> quote,2))*100?>%)
				<br/>
				<?= html::anchor('suggestion/sponsorlist/'.$suggestion->id, 'Sponsor List'); ?>
				</span>
				</div>
				
			<? } ?>
			<br/>
			<h2><?php echo $suggestion -> title ?></h2>		
			<p style='max-width:600px;word-wrap:break-word;'>
			<?php 
				echo Utility_Model::bbcode($suggestion -> body);
			?>
			</p>
		</div>
	</div>
	<br style='clear:both'/>
	
	<div id='suggestionfooter' style='float:right;bottom-margin:0'>
	<? 		
		echo $suggestioncommands; 
	?>
	</div>
</fieldset>
<br style="clear:both;" />
