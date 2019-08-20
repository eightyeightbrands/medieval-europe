<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/structure/manage/' .$id, 
	kohana::lang('global.manage'), 
	array( 'class' => ($action == 'manage') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/structure/sethourlycost/' .$id, 
	kohana::lang('structures_trainingground.sethourlyprice'), 
	array( 'class' => ($action == 'sethourlycost') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/structure/upgradelevel/'.$id, 
	kohana::lang('structures.upgradelevel'), 
	array( 'class' => ($action == 'upgradelevel') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>
<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/academy/assign_rolerp/'.$id, 
	kohana::lang('structures.submenu_assignrprole'), 
	array( 'class' => ($action == 'assign_rolerp') ? 'button selected' : 'button' )
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