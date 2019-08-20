<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/boardmessage/index/europecrier',
	kohana::lang('boardmessage.messagecategoryeuropecrier'), 
	array( 'class' => ($category == 'europecrier') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/boardmessage/index/job',
	kohana::lang('boardmessage.messagecategoryjob'), 
	array( 'class' => ($category == 'job') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/boardmessage/index/other',
	kohana::lang('boardmessage.messagecategoryother'), 
	array( 'class' => ($category == 'other') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/suggestion/index/',
	kohana::lang('suggestions.suggestions'), 
	array( 'class' => ($category == 'suggestion') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>