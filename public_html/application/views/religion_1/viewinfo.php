<script type="text/javascript">		
	$(document).ready(function()
	{	
		$("#tabs").tabs({active: 0 });		
	});
</script>

<div class="pagetitle"><?php echo kohana::lang($info['name'])?></div>

<div>
	
	<div style='float:left;width:75%;border:0px solid #000;'>
	
		<h5><?= kohana::lang('global.info'); ?></h5>	
		
		<?php echo kohana::lang('religion.followers') . ': <b>' .  $info['followers'] . '</b>' ; ?>
		<br/>
		<?php echo kohana::lang('religion.percentage') . ': <b>' .  $info['percentage'] . '%</b>' ; ?>
		<br/>
		<br/>
		<h5><?php echo kohana::lang('religion.dogmabonuses');?></h5>
		<?
		if ( count($info['dogmabonuses']) == 0 )
		{
		?>
			<?= kohana::lang('global.none'); ?>
		<?php
		}
		else
		{			
		?>
		
		<ol>		
		<?
			foreach ($info['dogmabonuses'] as $dogmabonus)			
			{
		?>
		<li>
		<?= html::anchor($dogmabonus -> url,
			kohana::lang('religion.dogmabonus_'. $dogmabonus -> bonus),
			array('target' => '_new'));?>
		</li>			
		<?
			}
		?>
		</ol>	
		<?
		}
		?>		
		<br/>
		<br/>
		<?
		if ( $info['tag'] != 'nochurch' )
		{
		?>
		<h5><?php echo kohana::lang('religion.holytext');?></h5>
		<?
			echo html::anchor(
				$info['holytexturl'], 
				kohana::lang('global.read'),
				array ( 'target' => '_new' ) ); 
		}
		?>
	
	</div>
	
	
	<div style='float:left;width:24%;border:0px solid #c00' class="right">
	<?php echo html::image('media/images/badges/religionsymbols/symbol_' . $info['tag'] . '.png') ?>
	</div>

	<br style='clear:both'/>

</div>

<?php 
if ( $info['tag'] != 'nochurch' ) 
{
?>
	
	<br/>
	
	<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

	<br/>
	<br/>

	<div id='tabs'>
		<ul>
			<li><?php echo html::anchor('#tab-followers', kohana::lang('religion.followers'));?></li>
			<li><?php echo html::anchor('#tab-hierarchy', kohana::lang('global.hierarchy'));?></li>
		</ul>

		
		<div id="tab-followers">
		<br/>
		<div class="pagination"><?php echo $pagination->render(); ?></div>
		<br/>
			<table>
			<th width='33%'><?= kohana::lang('global.name');?></th>						
			<th width='33%'><?= kohana::lang('global.kingdom');?></th>	
			<th width='33%'><?= kohana::lang('global.role');?></th>				
				<?php
					$r = 0;
					foreach ( $followers as $follower) {				
						
						$class = ($r % 2 == 0) ? '' : 'alternaterow_1' ; 
						$role = Character_Model::get_current_role_s( $follower -> id );
						$roletext = '-';
						if (!is_null($role) and $role -> get_roletype() == 'religious' )
							$roletext = $role -> get_title(true);
				?>					
				<tr class='<?php echo $class; ?>'>
					<td class='left'><?= Character_Model::create_publicprofilelink(null, $follower -> character_name); ?></td>								
					<td class='left'><?= kohana::lang($follower -> kingdom_name) ; ?></td>
					<td class='center'><?= $roletext;?></td>	
					</tr>
			<?php
				$r++;
				}
			?>
			</table>
		</div>
		<div id="tab-hierarchy">
			<br/>
			<br/>
			<?php echo $output ?>
		</div>
	</div>
	
<?php } ?>

<br style='clear:both'/>
