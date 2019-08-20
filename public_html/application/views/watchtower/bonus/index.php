<script type="text/javascript">
	
	
	$(document).ready(function()
	{	
		$("#tabs").tabs({active: <?php echo $tabindex ?> });		
		$("li.buydoubloons a").unbind('click');		
		
		$(".targetchar").autocomplete({
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});
		
		$(".region").autocomplete({
			source: "index.php/jqcallback/listallregions",
			minLength: 2
		});
		
		$(".premiumbonuscuts").change( function()
		{
			id = $(this).attr("id");
			
			$.ajax( //ajax request starting
			{				
				url: "<?php echo url::base(true);?>" + "jqcallback/getinfo", 
				type:"POST",
				data: { name: id, cut: $(this).val() },
				success: 
				function(data) 
				{																	
					var d = JSON.parse( data );	
					//console.log('discount: ' + d.discount );	
					if (d.discount != null)
					{	

						//console.log('-> discount found, setting fields for id:' + d.id);
						
						$("#price-" + d.id).html("<s>" + d.originalprice + "</s>");
						$("#discountedprice-" + d.id).html(d.discountedprice);
						$("#discounttime-"+d.id).show();
						$("#timeuntildiscountends-" + d.id).html(d.timeuntildiscountends);
					}
					else
					{
						console.log('-> discount not found, setting fields for id:' + d.id);
						$("#price-" + d.id).html(d.originalprice);
						$("#discountedprice-" + d.id).hide();
						$("#discounttime-"+d.id).hide();
						$("#timeuntildiscountends-" + d.id).hide();
					}
				}
			})
		});
		
		$(".premiumbonuscuts").each(function() {			
			$(this).trigger('change');
		});
	});
	
	
	
</script>

<div class="pagetitle"><?php echo kohana::lang('page.shop_pagetitle')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='helper'><?= kohana::lang('bonus.bonuspage_helper')?></div>

<div class='right'><?= html::anchor( 'user/bonuspurchases/' . $char -> user_id, kohana::lang('bonus.activebonuses'),
	array('target' => 'new')); ?></div>

<div id='tabs' >

	<ul>
		<li class='buydoubloons'><?php echo html::anchor('bonus/getdoubloons', kohana::lang('global.buy'));?></li>
		<li><?php echo html::anchor('#tab-packages', kohana::lang('bonus.packages'));?></li>
		<li><?php echo html::anchor('#tab-powerups', kohana::lang('bonus.powerup'));?></li>
		<li><?php echo html::anchor('#tab-utilities', kohana::lang('bonus.utilities'));?></li>
		<li><?php echo html::anchor('#tab-estetical', kohana::lang('bonus.estetical')) ?></li>				
	</ul>
	
		
	<div id='tab-packages'>
	
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('basicpackage');				
				echo $bp -> displaybonus(); 
			?>				
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('workerpackage');				
				echo $bp -> displaybonus(); 
			?>	
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('travelerpackage');				
				echo $bp -> displaybonus(); 
			?>	
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('rosary');				
				echo $bp -> displaybonus(); 
			?>	
		</div>
				
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('ipcheckshield');				
				echo $bp -> displaybonus(); 
			?>	
		</div>
		
	</div>
	
	<div id='tab-powerups'>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('armory');				
				echo $bp -> displaybonus(); 
			?>
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('diamondring');				
				echo $bp -> displaybonus(); 
			?>
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('elixirofdexterity');				
				echo $bp -> displaybonus(); 
			?>
		</div>
			
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('elixirofhealth');				
				echo $bp -> displaybonus(); 
			?>
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('elixirofstrength');				
				echo $bp -> displaybonus(); 
			?>
		</div>
			
			
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('elixirofintelligence');				
				echo $bp -> displaybonus(); 
			?>
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('elixirofconstitution');				
				echo $bp -> displaybonus(); 
			?>
		</div>
			
			
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('elixirofstamina');				
				echo $bp -> displaybonus(); 
			?>
		</div>
			
			
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('elixirofcuredisease');				
				echo $bp -> displaybonus(); 
			?>
		</div>
			
		
	</div>
	
	<div id='tab-utilities'>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('automatedsleep');				
				echo $bp -> displaybonus(); 
			?>	
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('supercart');				
				echo $bp -> displaybonus(); 
			?>	
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('professionaldesk');				
				echo $bp -> displaybonus(); 
			?>	
		</div>
		
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('professionaldeskslot');				
				echo $bp -> displaybonus(); 
			?>
		</div>
		
	</div>
	
	<div id='tab-estetical'>
	
		<div class='bonus alternaterow_1'>		
			<?php
				$bp = PremiumBonus_Factory_Model::create('wardrobe');				
				echo $bp -> displaybonus(); 
			?>	
		</div>
		
	<br style="clear:both;" /> 
	</div>
	
</div>		

