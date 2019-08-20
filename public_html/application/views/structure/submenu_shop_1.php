	<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('structure/manage/' .$id, 
	kohana::lang('global.manage'), 
	array( 'class' => ($action == 'manage') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('structure/listcraftableitems/' .$id, 
	kohana::lang('structures.listcraftableitems'), 
	array( 'class' => ($action == 'listcraftableitems') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/structure/upgradelevel/' .$id, 
	kohana::lang('structures.upgradelevel'), 
	array( 'class' => ($action == 'upgradelevel') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>
<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/structure/upgradeinventory/'.$id, 
	kohana::lang('structures.upgradeinventory'), 
	array( 'class' => ($action == 'upgradeinventory') ? 'button selected' : 'button' )
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