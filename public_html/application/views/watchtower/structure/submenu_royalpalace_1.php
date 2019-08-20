<div class ="submenutabs">
	<ul>
<li>
<?= html::anchor('structure/manage/' .$id, 
	kohana::lang('global.manage'), 
	array( 'class' => ($action == 'manage') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/royalpalace/declarehostileaction/' .$id, 
	kohana::lang('structures_royalpalace.submenu_declarehostileaction'), 
	array( 'class' => ($action == 'declarehostileaction') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/royalpalace/diplomacy/' .$id, 
	kohana::lang('structures_royalpalace.submenu_diplomacy'), 
	array( 'class' => ($action == 'diplomacy') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/royalpalace/viewlaws/'.$id, 
	kohana::lang('structures_royalpalace.submenu_lawsandtaxes'), 
	array( 'class' => ($action == 'viewlaws') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/royalpalace/assign_roles/'.$id, 
	kohana::lang('structures_royalpalace.submenu_assign_roles'), 
	array( 'class' => ($action == 'assign_roles') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/royalpalace/assign_rolerp/'.$id, 
	kohana::lang('structures.submenu_assignrprole'), 
	array( 'class' => ($action == 'assign_rolerp') ? 'button selected' : 'button' )
	); ?>
</li>
</ul>
</div>
<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/royalpalace/welcomeannouncement/'.$id, 
	kohana::lang('structures_royalpalace.submenu_announcements'), 
	array( 'class' => ($action == 'welcomeannouncement') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/structure/buildproject/'.$id, 
	kohana::lang('structures_royalpalace.submenu_kingdomprojects'), 
	array( 'class' => ($action == 'buildproject') ? 'button selected' : 'button' )
	); ?>
</li>
<li>
<?= html::anchor('/royalpalace/resourcereport/'.$id, 
	kohana::lang('structures_royalpalace.submenu_reports'), 
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