<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/structure/manage/' .$id, 
	kohana::lang('global.manage'), 
	array( 'class' => ($action == 'manage') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/breeding/feed/'.$id, 
	kohana::lang('structures.breeding_feed'), 
	array( 'class' => ($action == 'feed') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/breeding/gather/'.$id, 
	kohana::lang('structures.breeding_gather'), 
	array( 'class' => ($action == 'gather') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/breeding/butcher/'.$id, 
	kohana::lang('structures.breeding_butcher'), 
	array( 'class' => ($action == 'butcher') ? 'button selected' : 'button' )
	); ?>
</li>
</ul>
</div>
<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/structure/manageaccess/'.$id, 
	kohana::lang('structures.manageaccess'), 
	array( 'class' => ($action == 'manageaccess') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/structure/inventory/'.$id, 
	kohana::lang('global.inventory'), 
	array( 'class' => ($action == 'inventory') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('structure/events/'.$id, 
	kohana::lang('global.events'), 
	array( 'class' => ($action == 'events') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>