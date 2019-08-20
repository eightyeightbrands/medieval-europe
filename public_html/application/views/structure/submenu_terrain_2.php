<div class ="submenutabs">					
<ul>
<li>
<?= html::anchor('structure/manage/' .$id, 
	kohana::lang('global.manage'), 
	array( 'class' => ($action == 'manage') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('terrain/seed/' .$id, 
	kohana::lang('structures_terrain.seed'), 
	array( 'class' => ($action == 'seed') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/terrain/harvest/' .$id, 
	kohana::lang('structures_terrain.harvest'), 
	array( 'class' => ($action == 'harvest') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('/structure/manageaccess/' .$id, 
	kohana::lang('structures.manageaccess'), 
	array( 'class' => ($action == 'manageaccess') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>
<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('/structure/upgradelevel/' .$id, 
	kohana::lang('structures.upgradelevel'), 
	array( 'class' => ($action == 'upgradelevel') ? 'button selected' : 'button' )
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