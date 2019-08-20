<script type="text/javascript">

$(function() {
		$( "#configuretab" ).tabs(
		{selected:0});
});
</script>

<div class="pagetitle"><?php echo kohana::lang('wardrobe.atelier') ?></div>

<?php echo $submenu ?>

<div id='helper'><?php echo kohana::lang('wardrobe.atelier_dynamo_helper',
html::anchor('https://support.medieval-europe.eu', kohana::lang('global.support')));?></div>

<br/>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>

<div id ='configuretab'>
	
<ul>
<li><?php echo html::anchor( '#tabs-1', kohana::lang('wardrobe.atelier_avatars')); ?></li>
<li><?php echo html::anchor( '#tabs-2', kohana::lang('wardrobe.atelier_armors')); ?></li>
<li><?php echo html::anchor( '#tabs-3', kohana::lang('wardrobe.atelier_weapons')); ?></li>
<li><?php echo html::anchor( '#tabs-4', kohana::lang('wardrobe.atelier_clothes')); ?></li>
<li><?php echo html::anchor( '#tabs-5', kohana::lang('wardrobe.atelier_backgrounds')); ?></li>
</ul>

<div id='tabs-1'>
	<div id='atelierwrapper'>	
		<?php
			
			$directory = $basedirectory . '/avatars/avatars/watermarked/' . $sex;
			$files = scandir( $directory );			
			foreach ( $files as $file )
			{				
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
									
				if ( !is_dir( $file ) )
				{
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'avatars',
						'avatars',												
						'wardrobe.avatar'
					);					
				}
			}
			
		?>		
		<br style="clear:both;" />
	</div>
</div>
	
<div id='tabs-2'>
	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/armors/head/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'armors',
						'head',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	

	</div>
	
	<!-- body armors --> 
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/armors/body/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
				
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'armors',
						'body',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
	<!-- legs armors --> 
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/armors/legs/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'armors',
						'legs',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
	<!-- feet armors --> 	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/armors/feet/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'armors',
						'feet',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
	
	<!-- Shields --> 	
	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/armors/shields/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'armors',
						'shields',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
	<!-- Sets --> 	
	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/armors/sets/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'armors',
						'sets',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
</div> <!-- end tab 2 -->

<div id='tabs-3'>	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/weapons/weapons/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'weapons',
						'weapons',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
</div>  <!-- end tab 4 -->	

<div id='tabs-4'>
	
	<!-- head -->
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/clothes/head/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'clothes',
						'head',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
	<!-- body -->
	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/clothes/body/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
									
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'clothes',
						'body',
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
	<!-- legs -->
	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/clothes/legs/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'clothes',
						'legs',
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
	<!-- scarpe -->
	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/clothes/feet/watermarked/' . $sex;
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
					
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'clothes',
						'feet',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
	<!-- sets -->
	
	<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/clothes/sets/watermarked/' . $sex;
			
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
				//var_dump($filename);
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'clothes',
						'sets',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
	
</div>  <!-- end tab 4 -->	

<div id='tabs-5'>
<div class='atelierwrapper'>
		<?php
			
			$directory = $basedirectory . '/backgrounds/backgrounds/watermarked/' . $sex;
			
			$files = scandir( $directory );			
			
			foreach ( $files as $file )
			{
				$i = 1;			
				$filename = $directory . '/' . $file;
				$filenamewithoutextension = basename($file, ".png");
				
				//var_dump($filename);
				
				if ( !is_dir( $file ) )
				{
				
					echo Wardrobe_Model::helper_itemform ( 
						$filenamewithoutextension,
						$filename,
						'backgrounds',
						'backgrounds',						
						'wardrobe.' . $filenamewithoutextension . '_desc'
					);					
				}
				$i++;
			}
		?>	
	</div>
</div>

	
</div>

<br style='clear:both'/>
