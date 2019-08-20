<div class ="submenutabs">
<ul>
<li>
<?= html::anchor('region/privatestructures/shop/' . $region_id, kohana::lang('regionview.shops'),	
	array( 'class' => ($action == 'shop') ? 'selected' : '' )
	); ?>
</li>	
<li>
<?= html::anchor('region/privatestructures/terrain/' . $region_id, kohana::lang('regionview.terrains'),	
	array( 'class' => ($action == 'terrain') ? 'selected' : '' )
	); ?>
</li>	
<li>
<?= html::anchor('region/privatestructures/house/' . $region_id, kohana::lang('regionview.houses'),	
	array( 'class' => ($action == 'house') ? 'selected' : '' )
	); ?>
</li>	
<li>
<?= html::anchor('region/privatestructures/breeding/' . $region_id, kohana::lang('regionview.breedings'),	
	array( 'class' => ($action == 'breeding') ? 'selected' : '' )
	); ?>
</li>	
</ul>
</div>
