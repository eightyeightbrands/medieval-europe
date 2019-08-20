<head>
<script type='text/javascript'>

$(document).ready ( function () 
{	
	$("#kingdomstats").hide();
	$("#generatename").click( function() 
	{			
		$.ajax( //ajax request starting
		{
		url: "<?php echo url::base(false)?>" + "index.php/jqcallback/generatename", 
		type:"POST",
		data: { charculture: $("#charculture").val(), charsex: $("#charsex").val() },
		success: 
			function(data) 
			{												
				var content = JSON.parse( data );				
				$("#charname").val(content.name); 
				$("#charsurname").val(content.surname); 
			}
		}	
		);	
		return false;
	}),	

	$(".kingdomheraldry img").click( function() 
	{			
		if ( $(this).hasClass("disabled") )
			return false;
		
		$("#kingdomstats").show();
		
		$("input[name='choosenkingdom_id']").val( $(this).attr('id'));
		$.ajax( //ajax request starting
		{
		url: "<?php echo url::base(true)?>" + "/jqcallback/get_kingdominfo", 
		type:"POST",
		data: { id: $(this).attr('id') },
		success: 
			function(data) 
			{	
				
				var content = JSON.parse( data );	 						  
				$("#slogan").html('<i>' + content.slogan + '</i>' ); 
				$("#citizenscount").html('<b>' + content.citizenscount + '</b>' ); 
				$("#controlledregions").html('<b>' + content.controlledregions + '</b>' ); 								
				$("#richestkingdomposition").html('<b>' + content.richestkingdomposition + '</b>' ); 
				$("#populatedkingdomposition").html('<b>' + content.populatedkingdomposition + '</b>' ); 				
				$("#activekingdomposition").html('<b>' + content.activekingdomposition + '</b>' ); 								
				$("#spokenlanguages").html('<b>' + content.spokenlanguages + '</b>' ); 
				$("#religioninfo").html('<b>' + content.religioninfo + '</b>' ); 
				$("#kingdommessagetitle").html( content.kingmessagetitle ); 
				$("#kingname").html('<b>' + content.kingname + '</b>' ); 
				$("#kingdomname").html('<b>' + content.translatedname + '</b>' ); 
				$("#kingdommessage").html( content.kingmessage ); 
				$("#kingdomnationalities").html( '<b>' + content.nationalities + '</b>'); 
				$("a#kingdomwikilink").attr('href', 'https://wiki.medieval-europe.eu/index.php?title=Kingdom:_' + content.name );
				
			}
		}	
		);
		return false;		
	});
	
	$('#charborn').trigger('change');
	
}); 

function add(field)
{
	if ( parseInt(document.getElementById("charpoints").value) > 0 && parseInt(document.getElementById(field).value) < 15 )
	{
		document.getElementById(field).value = parseInt(document.getElementById(field).value) + 1;
		document.getElementById("charpoints").value = parseInt(document.getElementById("charpoints").value) - 1;
	}
}

function subtract(field)
{
	if ( parseInt(document.getElementById(field).value) > 1 )
	{
		document.getElementById(field).value = parseInt(document.getElementById(field).value) - 1;
		document.getElementById("charpoints").value = parseInt(document.getElementById("charpoints").value) + 1;
	}
}

</script>
</head>

<div class="pagetitle"><?php echo Kohana::lang('character.create_pagetitle') ?></div>


<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('character.create_helper');?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=En_US_CharCreation',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>
<fieldset><legend><?php echo Kohana::lang('character.create_generaldata')?> </legend>

<?php echo form::open(url::current(), array('class'=>'createchar_form')); ?>

<?php echo form::label(
	array(		
		'name' => 'charculture',
		'class' => 'mandatory' ),
		Kohana::lang('character.create_charculture'));?>
<?php echo form::dropdown('charculture', $combo_culture, $form['charculture']);?><br/>

<?php echo form::label(
	array(		
		'name' => 'charsex',
		'class' => 'mandatory' ),
		Kohana::lang('character.create_charsex'));?>

<?php echo form::dropdown('charsex', $combo_sex, $form['charsex']);?><br/>
<div class="boxevidence"><?php echo Kohana::lang('character.create_name_info'); ?></div>

<?php echo form::label(
	array(		
		'name' => 'charname',
		'class' => 'mandatory' ),
		Kohana::lang('character.create_charname'));?>
<?php echo form::input('charname',($form['charname'])) ?>
<?php echo "<input type='button' id='generatename' value='" . Kohana::lang('character.create_generatename') . "'>";  ?>
<?php if (!empty ($errors['charname'])) echo "<div class='error_msg'>".$errors['charname']."</div>";?><br/>

<?php echo form::label(
	array(
		'name' => 'charsurname',
		'class' => 'mandatory' ),
		Kohana::lang('character.create_charsurname'));?>


<?php echo form::input('charsurname',($form['charsurname']) );?>
<?php if (!empty ($errors['charsurname'])) echo "<div class='error_msg'>".$errors['charsurname']."</div>";?><br/>

<br/>

<div class='helper'>
<?= kohana::lang('character.create_spokenlanguageshelper'); ?>
</div>

<?php echo form::label(
	array(
		'id' => 'charspokenlanguage1', 		
		'class' => 'mandatory' ),
		Kohana::lang('character.create_spokenlanguage1'));?>
<?php echo form::dropdown('charspokenlanguage1', $spokenlanguages ); ?>
<?php if (!empty ($errors['charspokenlanguage1'])) echo "<div class='error_msg'>".$errors['charspokenlanguage1']."</div>";?>
<br/>
<?php echo form::label('charspokenlanguage2', Kohana::lang('character.create_spokenlanguage2'));?>
<?php echo form::dropdown('charspokenlanguage2', $spokenlanguages ); ?>

<br/>

<?php echo form::label('charspokenlanguage3', Kohana::lang('character.create_spokenlanguage3'));?>
<?php echo form::dropdown('charspokenlanguage3', $spokenlanguages ); ?>

</fieldset>

<br/>


<fieldset>
<legend><?php echo kohana::lang('character.create_customizeattributes') . ': ' ?></legend>

<div id='helper'><?php echo Kohana::lang('character.create_stat', 
	html::anchor( kohana::lang('character.attributewikiarticle'), 'link',
	array('target' => 'new')) ) ?></div>

<?php echo Kohana::lang('character.create_charpoints');?>:<?php echo form::input('charpoints', ($form['charpoints']),'style="width:7px;text-align:right;background:transparent;border:0px;color:#000;font-weight:bold;" readonly');?>

<p>
<?php if (!empty ($errors['charpoints'])) echo "<div class='error_msg'>".$errors['charpoints']."</div>";?>
</p>

<strong><?php echo Kohana::lang('character.create_charstr')?></strong>
<br/>
<div style='float:left;width:15%'>
		 <?php echo form::input('charstr', ($form['charstr']),'style="width:30px;text-align:right" readonly');?>
		<?php echo '<input type="button" value="+" class="submit" style="width:20px" onClick="Javascript:add(\'charstr\')">'; ?>
		<?php echo '<input type="button" value="-" class="submit" style="width:20px" onClick="Javascript:subtract(\'charstr\')">'; ?>
	</div>
<div style='float:right;width:85%'>
		<p class ="small">
		   <?php echo Kohana::lang('character.create_info_charstr');?>
		</p>
</div>

<br style='clear:both'/>

<strong><?php echo Kohana::lang('character.create_chardex')?></strong>
<br/>
<div style='float:left;width:15%'>
	<?php echo form::input('chardex', ($form['chardex']),'style="width:30px;text-align:right" readonly');?>
	<?php echo '<input type="button" class="submit" style="width:20px" value="+" onClick="Javascript:add(\'chardex\')">'; ?>
	<?php echo '<input type="button" class="submit" style="width:20px" value="-" onClick="Javascript:subtract(\'chardex\')">'; ?>
</div>

<div style='float:right;width:85%'>
<p class ="small">
	<?php echo Kohana::lang('character.create_info_chardex');?>
</p>
</div>

<br style='clear:both'/>

<strong><?php echo Kohana::lang('character.create_charintel')?></strong>
<br/>

<div style='float:left;width:15%'>
	<?php echo form::input('charint', ($form['charint']),'style="width:30px;text-align:right" readonly');?>
	<?php echo '<input type="button" class="submit" style="width:20px" value="+" onClick="Javascript:add(\'charint\')">'; ?>
	<?php echo '<input type="button" class="submit" style="width:20px" value="-" onClick="Javascript:subtract(\'charint\')">'; ?>
</div>
<div style='float:right;width:85%'>
<p class ="small">
	<?php echo Kohana::lang('character.create_info_charint');?>		
</p>
</div>

<br style='clear:both'/>

<strong><?php echo Kohana::lang('character.create_charcost')?></strong>
<br/>

<div style='float:left;width:15%'>
	<?php echo form::input('charcost', ($form['charcost']),'style="width:30px;text-align:right" readonly');?>
	<?php echo '<input type="button" class="submit" style="width:20px" value="+" onClick="Javascript:add(\'charcost\')">'; ?>
	<?php echo '<input type="button" class="submit" style="width:20px" value="-" onClick="Javascript:subtract(\'charcost\')">'; ?>
</div>
<div style='float:right;width:85%'>
<p class ="small">
	<?php echo Kohana::lang('character.create_info_charcost');?>
</p>
</div>

<br style='clear:both'/>

<strong><?php echo Kohana::lang('character.create_charcar')?></strong>
<br/>

<div style='float:left;width:15%'>
	<?php echo form::input('charcar', ($form['charcar']),'style="width:30px;text-align:right" readonly');?>
	<?php echo '<input type="button" class="submit" style="width:20px" value="+" onClick="Javascript:add(\'charcar\')">'; ?>
	<?php echo '<input type="button" class="submit" style="width:20px" value="-" onClick="Javascript:subtract(\'charcar\')">'; ?>
</div>
<div style='float:right;width:85%'>
<p class ="small">
	<?php echo Kohana::lang('character.create_info_charcar');?>
</p>
</div>

<br style='clear:both'/>


</fieldset>

<br/>

<fieldset>
<legend><?php echo kohana::lang('character.create_choosekingdom') ?></legend>

<p>
<?php if (!empty ($errors['choosenkingdom_id'])) echo "<div class='error_msg'>".$errors['choosenkingdom_id']."</div>";?>
</p>

<div id="kingdomsheraldry">
		<?php 
			$i = 1; 						
			foreach ($subscribable_kingdoms['kingdoms'] as $kingdom)
			{ 			
			
		?>
				<div class="kingdomheraldry">				
				<?php 
					
					$title = kohana::lang( $kingdom['name'] );
					echo html::image(
						'media/images/heraldry/' . $kingdom['image'] . '-large.png',
						array(
							'id' => $kingdom['id'],
							'title' => $title,
						)
					);
				?>			
				</div>
				
				<?php if ($i >= 12 and $i %12 == 0 ) { ?>
					<br style='clear:both'/>
				<?php } ?>
				
		<?php 	$i++;
		}?>
</div>

<br style='clear:both'>

<div id='kingdomstats'>
<br/>
<h2 class='center' id='kingdomname'></h2>

<div class="center" id="slogan"></div>
<br/>
<?php echo kohana::lang('character.create_kingname') . ': ' ?> <span id='kingname'></span><br/>
<?php echo kohana::lang('character.create_population') . ': ' ?> <span id='citizenscount'></span><br/>
<?php echo kohana::lang('character.create_controlledregions') . ': ' ?> <span id='controlledregions'></span><br/>
<?php echo kohana::lang('regioninfo.richestkingdom_position') ?> <span id='richestkingdomposition'></span><br/>
<?php echo kohana::lang('regioninfo.populatedkingdom_position') ?> <span id='populatedkingdomposition'></span><br/>
<?php echo kohana::lang('regioninfo.activekingdom_position') ?> <span id='activekingdomposition'></span><br/>
<?php echo kohana::lang('character.create_spokenlanguages') . ': ' ?> <span id='spokenlanguages'></span><br/>
<?php echo kohana::lang('character.create_religioninfo') . ': ' ?> <span id='religioninfo'></span><br/>
<?php echo kohana::lang('character.create_nationalities') . ': ' ?> <span id='kingdomnationalities'></span><br/>

<br/>

<h5 id='kingdommessagetitle' class='center'></h5>
<br/>
<div id='kingdommessage'></div>

</fieldset>

<br/>

<div class='center'>
<?php 
echo form::hidden('choosenkingdom_id');
	
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium' , 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.create'));
?>
</div>

<br style="clear:both;" />
