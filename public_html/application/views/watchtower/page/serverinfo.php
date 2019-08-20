<div class="row">
	<div class="col-xs-12">
		<h1 class="text-center"><?php echo Kohana::lang("page.server_info") ?></h1>
		<ul class="list-inline text-center">
		<li>
			<?php echo html::anchor('/', Kohana::lang('page-homepage.goto-homepage')); ?>
		</li>
		</ul>		
		<table class="table" style="margin:0 auto;width:80%">
			<tr><th colspan='2'><?= kohana::lang('global.generalparameters');?></th></tr>
			<tr>
				<td><?php echo kohana::lang('global.servername') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.servername'); ?></td>
			</tr>			
			<tr>
				<td><?php echo kohana::lang('global.environment') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.environment'); ?></td>
			</tr>
				
			<tr>
				<td><?php echo kohana::lang('global.serverspeed') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.serverspeed'); ?></td>
			</tr>

			<tr>
				<td><?php echo kohana::lang('global.maxshops') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.maxshops'); ?></td>
			</tr>
				
			<tr>
				<td><?php echo kohana::lang('global.maxterrains') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.maxterrains'); ?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.diplomacychangecooldown') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.diplomacychangecooldown'); ?></td>
			</tr>
			
			<tr>
				<td><?php echo kohana::lang('global.mindaystofight') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.mindaystofight'); ?></td>
			</tr>
			<tr><th colspan='2'><?= kohana::lang('global.roles');?></th></tr>
			<tr>
				<td><?php echo kohana::lang('global.churchlevel1minage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.churchlevel1minage'); ?></td>
			</tr>

			<tr>
				<td><?php echo kohana::lang('global.churchlevel2minage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.churchlevel2minage'); ?></td>
			</tr>
				
			<tr>
				<td><?php echo kohana::lang('global.churchlevel3minage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.churchlevel3minage'); ?></td>
			</tr>

			<tr>
				<td><?php echo kohana::lang('global.churchlevel4minage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.churchlevel4minage'); ?></td>
			</tr>
				
			<tr>
				<td><?php echo kohana::lang('global.kingminage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.kingminage'); ?></td>
			</tr>

			<tr>
				<td><?php echo kohana::lang('global.vassalminage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.vassalminage'); ?></td>
			</tr>
				
			<tr>
				<td><?php echo kohana::lang('global.judgeminage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.judgeminage'); ?></td>
			</tr>

			<tr>
				<td><?php echo kohana::lang('global.guardcaptainminage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.guardcaptainminage'); ?></td>
			</tr>
				
			<tr>
				<td><?php echo kohana::lang('global.towergurdianminage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.towerguardianminage'); ?></td>
			</tr>

			<tr>
				<td><?php echo kohana::lang('global.academydirectorminage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.academydirectorminage'); ?></td>
			</tr>
				
			<tr>
				<td><?php echo kohana::lang('global.drillmasterminage') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.drillmasterminage'); ?></td>
			</tr>		
			<tr><th colspan='2'><?= kohana::lang('global.wars');?></th></tr>
			<tr>
				<td><?php echo kohana::lang('global.maxwarlength') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.maxwarlength');?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.war_newdeclarationcooldown') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.war_newdeclarationcooldown');?></td>
			</tr>
			<tr><th colspan='2'><?= kohana::lang('global.revolts');?></th></tr>
			<tr>
				<td><?php echo kohana::lang('global.revolt_declarerevoltdayslimit') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.revolt_declarerevoltdayslimit');?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.revolt_attackerdayslimit') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.revolt_attackerdayslimit');?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.revolt_defenderdayslimit') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.revolt_defenderdayslimit');?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.battlefieldcreationtime') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.revolt_battlefieldcreationtime');?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.firstbattleroundtime') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.revolt_firstbattleroundtime');?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.battlefielddestroytime') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.battlefielddestroytime');?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.revolt_cooldown') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.revolt_cooldown');?></td>
			</tr>
			<tr><th colspan='2'><?= kohana::lang('global.raids');?></th></tr>
			<tr>
				<td><?php echo kohana::lang('global.battlefieldcreationtime') ?></td>
				<td class="text-right value">16</td>
			</tr>			
			<tr>
				<td><?php echo kohana::lang('global.firstbattleroundtime') ?></td>
				<td class="text-right value">12</td>
			</tr>			
			<tr>
				<td><?php echo kohana::lang('global.battlefielddestroytime') ?></td>
				<td class="text-right value"><?=kohana::config('medeur.battlefielddestroytime');?></td>
			</tr>
			<tr><th colspan='2'><?= kohana::lang('global.conquerregion');?></th></tr>
			<tr>
				<td><?php echo kohana::lang('global.battlefieldcreationtime') ?></td>
				<td class="text-right value">48</td>
			</tr>			
			<tr>
				<td><?php echo kohana::lang('global.firstbattleroundtime') ?></td>
				<td class="text-right value">12</td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.nextroundtime') ?></td>
				<td class="text-right value"><?=kohana::config('medeur.nextroundtime');?></td>
			</tr>
			<tr>
				<td><?php echo kohana::lang('global.battlefielddestroytime') ?></td>
				<td class="text-right value"><?=kohana::config('medeur.battlefielddestroytime');?></td>
			</tr>
			<tr><th colspan='2'><?= kohana::lang('global.nativerevolts');?></th></tr>
			<tr>
				<td><?php echo kohana::lang('global.nativerevoltinterval') ?></td>
				<td class="text-right value"><?php echo kohana::config('medeur.nativerevoltinterval'); ?></td>
			</tr>			
		</table>
	</div>
</div>	
<br/>
