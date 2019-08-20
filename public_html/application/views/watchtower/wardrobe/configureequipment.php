<script type="text/javascript">

$(function() {
		$( "#configuretab" ).tabs();		
});
</script>

<div class="pagetitle"><?php echo kohana::lang('wardrobe.ownwardrobe') ?></div>

<?php echo $submenu ?>
<br/>
<div id='helper'>
	<div id='helpertext'>
		<?php echo kohana::lang('wardrobe.ownwardrobe_helper');?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Wardrobe_Bonus',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>


<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div>
	<fieldset>
	<legend><?php echo kohana::lang('wardrobe.settodisplay')?></legend>
	<?php echo form::open(); ?>
	<?php echo form::label('disablewardrobecustomization', kohana::lang('wardrobe.disablewardrobecustomization')); ?>
	&nbsp;
	<?php echo form::checkbox( 'disablewardrobecustomization', true, ( $disablewardrobecustomization ) ); ?>	
	<br/>
	<?php echo form::label('hideringunderclothes', kohana::lang('wardrobe.hideringunderclothes')); ?>
	&nbsp;
	<?php echo form::checkbox( 'hideringunderclothes', true, ( $hideringunderclothes ) ); ?>		
	<br/>
	<?php echo form::label('hidehairsunderclothes', kohana::lang('wardrobe.hidehairsunderclothes')); ?>
	&nbsp;
	<?php echo form::checkbox( 'hidehairsunderclothes', true, ( $hidehairsunderclothes ) ); ?>	
	<center>
	<?php echo form::submit( 
		array (
			'id' => 'submit', 
			'class' => 'button button-small', 
			'name'=> 'disablecustomization'),			
		kohana::lang('global.set')); 
	?>	
	</center>
	<?php echo form::close() ?>
	</fieldset>
	
	
	<br/>
	<fieldset>
	<legend><?php echo kohana::lang('wardrobe.chooseskincolor')?></legend>
	<?php echo form::open(); ?>
	<?php echo kohana::lang('wardrobe.chooseskincolor') ?>
		<?php echo form::dropdown(
			'skincolorset', 			
			array( 
				'default' => kohana::lang( 'wardrobe.default'),
				'lightbrown' => kohana::lang( 'wardrobe.lightbrown'),		
				'lightwhite' => kohana::lang( 'wardrobe.lightwhite')),
			$skincolorset ); ?>
	<br/>
	<center>
	
	<?php echo form::submit( 
				array ('id' => 'submit', 'class' => 'button button-small', 'name'=> 'setskincolor'),			
				kohana::lang('global.set')); ?>		
	</center>
	<?php echo form::close() ?>
	
	</fieldset>
</div>

<br/>	

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<fieldset>
<legend><?php echo kohana::lang('wardrobe.uploadsection')?></legend>

<div class='helper'>
<?php echo kohana::lang('wardrobe.uploadsectionhelper')?>
</div>

<!-- elenco immagini caricate-->
<div>
<?php 
if ( count($uploadedimages) > 0 )
{
?>
<h5 style='text-align:left'><?php echo kohana::lang('wardrobe.uploadedimages') ?></h5>
<?php
foreach ( (array) $uploadedimages as $key => $slot )
	echo kohana::lang('wardrobe.uploadedimageslistentry', 
		kohana::lang('items.' . $key . '_name'),
		key($slot) ) . '<br/>';		
}
?>
</div>	
	
<br/>

<!-- elenco richieste pending-->
<div>
<?php 
if ( $pendingapprovalrequest -> loaded )
{
?>
<h5 style='text-align:left'><?php echo kohana::lang('wardrobe.pendingapprovalrequest') ?></h5>
<?php
echo kohana::lang('wardrobe.pendingapprovalrequestinfo', 
	$pendingapprovalrequest -> id , date("Y M d, H:i:s", $pendingapprovalrequest -> created));
}
?>
</div>	

<br/>
<br/>

<div>
<?php echo form::open_multipart( url::current(), array ('name' => 'configure', 'method' => 'post' ) ); ?>
<center>

	<?php echo form::submit( 
		array (
			'id' => 'submit', 
			'class' => 'submit', 
			'name'=> 'upload',
			'title' => kohana::lang('wardrobe.upload_helper')),
			kohana::lang('global.upload')); ?>
	
	<?php echo form::submit( 
		array (
			'id' => 'submit', 
			'class' => 'submit', 
			'name'=> 'approval',
			'title' => kohana::lang('wardrobe.approval_helper')),			
			kohana::lang('wardrobe.submitforapproval')); ?>
	
	<?php echo form::submit( 
		array (
			'id' => 'submit', 
			'class' => 'submit', 
			'name'=> 'reset',
			'title' => kohana::lang('wardrobe.cleanup_helper'),
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm').'\')' ), 
			kohana::lang('wardrobe.cleanup')); ?>
						
</center>	
</div>

<br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>


<div id ='configuretab'>
	
	<ul>
		<li><a href="#tabs-clothes"><?php echo kohana::lang('items.category_clothes')?></a></li>
		<li><a href="#tabs-armors"><?php echo kohana::lang('items.category_armors')?></a></li>
		<li><a href="#tabs-weapons"><?php echo kohana::lang('items.category_weapons')?></a></li>
		<li><a href="#tabs-tools"><?php echo kohana::lang('items.category_tools')?></a></li>
		
		<li><a href="#tabs-aspect"><?php echo kohana::lang('wardrobe.aspect')?></a></li>
		<li><a href="#tabs-preview"><?php echo kohana::lang('global.preview')?></a></li>
	</ul>
	<br/>
	<div id='tabs-clothes'>
		<table border='0'>
			<?php 
				foreach ( $items as $item ) 
				{
				
					if ( 
						$item -> parentcategory == 'clothes' 
							and 
						(	$item -> subcategory == $char -> sex 
								or
							is_null( $item -> subcategory ) 
						) 
					)
					{						
					
						echo Wardrobe_Model::helper_ownwardrobe(
							'items', $char, $item -> parentcategory, $item -> tag, $item -> name );	
					}
				}
			?>			
		</table>
	</div>
	
	<div id='tabs-armors'>
		<table border='0'>
			<?php 
				
				foreach ( $items as $item ) 
				{					
					if ( $item -> parentcategory == 'armors' )
					{						
						echo Wardrobe_Model::helper_ownwardrobe(
							'items', $char, $item -> parentcategory, $item -> tag, $item -> name );	
					}
				}
			?>			
		</table>
	</div>
	
	<div id='tabs-tools'>
		<table border='0'>
			<?php 
				foreach ( $items as $item ) 
				{				
					if ( $item -> parentcategory == 'tools' )
					{						
						echo Wardrobe_Model::helper_ownwardrobe(
							'items', $char, $item -> parentcategory, $item -> tag, $item -> name );
					}
				}
			?>			
		</table>
	</div>
	
	<div id='tabs-weapons'>
		<table border='0'>
			<?php 
				foreach ( $items as $item ) 
				{				
					if ( $item -> parentcategory == 'weapons' )
					{						
						echo Wardrobe_Model::helper_ownwardrobe(
							'items', $char, $item -> parentcategory, $item -> tag, $item -> name );
					}
				}
			?>			
		</table>
	</div>
	
	<div id='tabs-aspect'>
		<table border='0'>
			<?php 
				echo Wardrobe_Model::helper_ownwardrobe(
					'characters', $char, 'aspect', 'face', 'wardrobe.face' );
			?>
			<?php 
				echo Wardrobe_Model::helper_ownwardrobe(
					'characters', $char, 'aspect', 'hair', 'wardrobe.hair' );
			?>
			<?php 
				echo Wardrobe_Model::helper_ownwardrobe(
					'characters', $char, 'aspect', 'background', 'wardrobe.background' );
			?>	
		</table>
	</div>
	
	<div id='tabs-aspect'>
	</div>
	
	<div id='tabs-preview'>
			
			<fieldset>
			<legend><?php echo kohana::lang('wardrobe.currentandpreview');?></legend>
			<div style='float:left;width:48%'>			
				<?php echo $char -> render_char( $equippeditems, 'wardrobe' ) ?>
			</div>
			<div style='float:left;width:48%;margin-left:2%'>			
				<?php echo $char -> render_char( $equippeditems, 'preview' ) ?>						
			</div>
			</fieldset>
	</div>

</div>

</fieldset>

<?php echo form::close()?>	

<br style='clear:both'/>
