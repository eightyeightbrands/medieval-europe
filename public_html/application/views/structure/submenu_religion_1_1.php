<div class ="submenutabs">
	<ul>
<li>
<?= html::anchor('structure/manage/' .$id, 
	kohana::lang('global.manage'), 
	array( 'class' => ($action == 'manage') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/religion_1/managedogmas/'.$id, 
	kohana::lang('structures_religion_1.submenu_managedogmas'), 
	array( 'class' => ($action == 'managedogmas') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/religion_1/managehierarchy/'.$id, 
	kohana::lang('structures.managehierarchy'), 
	array( 'class' => ($action == 'managehierarchy') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/religion_1/assign_rolerp/'.$id, 
	kohana::lang('structures.submenu_assignrprole'), 
	array( 'class' => ($action == 'assign_rolerp') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/religion_4/celebratemarriage/'.$id, 
	kohana::lang('structures.celebratemarriage'), 
	array( 'class' => ($action == 'celebratemarriage') ? 'button selected' : 'button' )
	); ?>
</li>	

</ul>
</div>
<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/structure/buildproject/'.$id, 
	kohana::lang('structures.buildproject'), 
	array( 'class' => ($action == 'buildproject') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/religion_1/resourcereport/'.$id, 
	kohana::lang('structures_religion_1.resourcereport'), 
	array( 'class' => ($action == 'resourcereport') ? 'button selected' : 'button' )
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