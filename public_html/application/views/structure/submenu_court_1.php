<div class ="submenutabs">
	<ul>
<li>
<?= html::anchor('structure/manage/' .$id, 
	kohana::lang('global.manage'), 
	array( 'class' => ($action == 'manage') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/court/opencrimeprocedure/'.$id, 
	kohana::lang('structures_court.submenu_opencrimeprocedure'), 
	array( 'class' => ($action == 'opencrimeprocedure') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/court/listcrimeprocedures/'.$id, 
	kohana::lang('structures_court.submenu_managecrimeprocedures'), 
	array( 'class' => ($action == 'listcrimeprocedures') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/court/assign_rolerp/'.$id, 
	kohana::lang('structures.submenu_assignrprole'), 
	array( 'class' => ($action == 'assign_rolerp') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>
<div class ="submenutabs">
<ul>
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
<li>
<?= html::anchor('/structure/rest/'.$id, 
	kohana::lang('global.rest'), 
	array( 'class' => ($action == 'rest') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>