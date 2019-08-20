<div class ="submenutabs">
	<ul>
<li>
<?= html::anchor('tavern/game_dice/' .$id, 
	kohana::lang('structures_tavern.game_dice'), 
	array( 'class' => ($action == 'game_dice') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/tavern/rest/'.$id, 
	kohana::lang('global.rest'), 
	array( 'class' => ($action == 'rest') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>