<div class ="submenutabs">
	<ul>
<li>
<?= html::anchor('structure/manage/' .$id, 
	kohana::lang('global.manage'), 
	array( 'class' => ($action == 'manage') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('castle/propertyreport/' .$id, 
	kohana::lang('structures_castle.submenu_propertyreport'), 
	array( 'class' => ($action == 'propertyreport') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/castle/valueaddedtax/' .$id, 
	kohana::lang('structures_castle.submenu_taxes'), 
	array( 'class' => ($action == 'valueaddedtax') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/castle/assignrole/'.$id, 
	kohana::lang('structures_castle.submenu_nominees'), 
	array( 'class' => ($action == 'list_subordinates') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/castle/assign_rolerp/'.$id, 
	kohana::lang('structures.submenu_assignrprole'), 
	array( 'class' => ($action == 'assign_rolerp') ? 'button selected' : 'button' )
	); ?>
</li>
</ul>
</div>
<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/structure/buildproject/'.$id, 
	kohana::lang('structures_royalpalace.submenu_kingdomprojects'), 
	array( 'class' => ($action == 'buildproject') ? 'button selected' : 'button' )
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
<li>
<?= html::anchor('/structure/rest/'.$id, 
	kohana::lang('global.rest'), 
	array( 'class' => ($action == 'rest') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>