<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('character/details', 
	kohana::lang('character.submenu_details'), 
	array( 'class' => ($action == 'details') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('character/inventory', 
	kohana::lang('global.inventory'), 
	array( 'class' => ($action == 'inventory') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('character/role', 
	kohana::lang('character.submenu_role'), 
	array( 'class' => ($action == 'role') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('character/myproperties', 
	kohana::lang('character.submenu_myproperties'), 
	array( 'class' => ($action == 'myproperties') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>
<div class="submenutabs">
<ul>
<li>
<?= html::anchor('character/myjobs', 
	kohana::lang('character.submenu_myjobs'), 
	array( 'class' => ($action == 'myjobs') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('character/myquests', 
	kohana::lang('character.submenu_myquests'), 
	array( 'class' => ($action == 'myquests') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('user/referrals', 
	kohana::lang('character.submenu_referrals'), 
	array( 'class' => ($action == 'referrals') ? 'button selected' : 'button' )
	); ?>
</li>	
<li>
<?= html::anchor('group/mygroups', 
	kohana::lang('character.mygroups'), 
	array( 'class' => ($action == 'mygroups') ? 'button selected' : 'button' )
	); ?>
</li>	
</ul>
</div>
