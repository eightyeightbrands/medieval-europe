<div class="pagetitle"><?php echo kohana::lang('groups.listall') ?></div>

<?php echo $submenu; ?>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class='submenu'>
	<?= $secondarymenu; ?>
</div>

<br/>

<div style='float:left'>
	<?php 
	echo form::open('/group/listall', array('method' => 'get' ) ); 
	echo kohana::lang('global.name') . '&nbsp;'; 
	echo form::input(array( 'id' => 'name', 'name' => 'name', 'style' => 'width:250px') ) . '&nbsp;&nbsp;' ;
	echo kohana::lang('global.type') . '&nbsp;&nbsp' ;
	echo form::dropdown( 'type', array ( 
		'' => kohana::lang('global.catchall'),
		'mercenary' => kohana::lang('groups.mercenary'),
		'military' => kohana::lang('groups.military'),
		'other' => kohana::lang('groups.other'),	
		) )
	?>
</div>

<div style='float:right'>
<?
echo form::submit( array( 'id' => 'submit', 'class' => 'button button-small', 'value' => kohana::lang('global.search')) );
?>
</div>

<? echo form::close(); ?>

<?php
	
	if ( $groups->count() == 0 )
	{
?>
<p class='center'>
	<?php echo kohana::lang('groups.no_groups_found'); ?>
</p>

<?php
	}
	else
	{	
		$r=0;	
	?>
	
	<br/>
	<br/>
	
	<?php echo $pagination->render(); ?>

	<br/>


	<table class='border'>
	
	<th width="5%"></th>
	<th width='40%' class='center'>
		<?php echo kohana::lang('groups.name') . '&nbsp;'.
			html::anchor('group/listall/?orderby=name:asc', html::image('media/images/other/up.png'))
			.'&nbsp;'.
			html::anchor('group/listall/?orderby=name:desc', html::image('media/images/other/down.png'));	
		?>
	</th>
		
	<th class='center'><?php echo kohana::lang('groups.type') ?></th>
	<th class='center'><?php echo kohana::lang('groups.date_foundation') ?></th>
	
	<?php 
	
		foreach ( $groups as $group )
		{
			$class = ($r % 2 == 0) ? 'alternaterow_1' : 'alternaterow_2';
		?>
			<tr class="<?= $class; ?>">
			<td>
			<?
				$file = "media/images/groups/".$group->id.".png";	
				if ( file_exists( $file) )
					echo html::image(
						'media/images/groups/'.$group -> id.'.png?r='. time(), 
						array ( 
							'class' => 'size50',
						) );
				else
					echo html::image('media/images/template/group_no_image.png',
						array ( 
							'class' => 'size50',
						) );
			?>
			</td>
			<td>
				<h3><?=	html::anchor( '/group/view/'.$group->id, $group->name ); ?></h3>				
				<?= kohana::lang('groups.group_founder'). ' '. Character_Model::create_publicprofilelink( $group -> char_id ); ?>
				<br/>
				<i><?= $group -> description ; ?></i>
			</td>
			<td class="center">
			<?
			if ( $group -> type == 'groups.mercenary' )
				echo kohana::lang('groups.military') . '-' . kohana::lang( $group -> type );
			else
				echo kohana::lang( $group -> type ); 
			?>
			</td>
			
			<td class="center">					
			<?= Utility_Model::format_date($group->date); ?>
			</td>
			</tr>
		<?
			$r++;
		}
		?>
		</table>
<?
	}
?>


<br/>

<?php echo $pagination->render(); ?>

<br style="clear:both;" />
