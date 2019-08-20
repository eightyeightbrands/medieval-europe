<div class="pagetitle"><?php echo kohana::lang('structures_religion_1.submenu_managedogmas')?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('structures_religion_1.managedogmas_helper') ?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Dogmas',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
</div>

<br style='clear:both'/>

<br/>

<fieldset>

<legend>
	<?php echo kohana::lang('structures_religion_1.list_current_dogmas')?>
</legend>

<br/>

<table>

	<tr>
		<th><?php echo ucfirst(kohana::lang('religion.dogma')) ?></td>
		<th><?php echo ucfirst(kohana::lang('structures_house.level')) ?></td>
		<th><?php echo ucfirst(kohana::lang('page.bonus')) ?></td>
		<th><?php echo ucfirst(kohana::lang('message.options')) ?></td>
	</tr>
	
	<?php 
	$k = 0;
	
	foreach($church->church_dogmabonuses as $dogma)
	{
		$class = ($k % 2 == 0) ? 'alternaterow_1' : '';
		echo '<tr class='.$class.'>';
		echo '<td>';
		echo kohana::lang('religion.dogma_' . $dogma->cfgdogmabonus->dogma);
		echo '</td>';
		echo '<td class="center">';
		echo $dogma->cfgdogmabonus->level;
		echo '</td>';
		echo '<td class="center">';
		echo kohana::lang('religion.dogmabonus_' . $dogma->cfgdogmabonus->bonus);
		echo '</td>';
		
		echo '<td class="center">';
			echo html::anchor
			(
				'religion_1/removedogmabonus/'. $structure->id .'/'. $dogma->id,
				'[' . kohana::lang('global.delete' ) . ']',
				array
				(
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'
				)
			);
		echo '</td>';	
		echo '</tr>';
		$k++;
	}
	?>
</table>
<br/>
</fieldset>


<br/>

<fieldset>
<legend>
	<?php echo kohana::lang('structures_religion_1.add_dogma')?>
</legend>

<div id='helper'>
	<?php 
	echo kohana::lang
	(
		'structures_religion_1.add_dogma_helper', 
		count($church->church_dogmabonuses), 
		$church->get_cost_next_dogma_bonus()
	)
	?>
</div>
	
<div class='center'>
	<?php
	echo form::open();
		echo form::dropdown('dogmabonus', $dogmas);
		echo form::submit
		(
			array
			(
				'id' => 'add',
				'name' => 'add',
				'value' => kohana::lang('global.add'),
				'class' => 'button button-small',	
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' 
			), 
			kohana::lang('message.write_submit')
		);
	echo form::close();
	?>
</div>
</fieldset>
<br style='clear:both'/>
