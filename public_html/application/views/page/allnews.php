<div class="title" style="margin-bottom:15px"><?php echo kohana::lang('page-homepage.allnews-title'); ?></div>

<p class='right' style='margin-top:-10px;'><?php echo html::anchor('page/index',Kohana::lang('page-homepage.goto-homepage')); ?></p>

<?php
echo $pagination->render('extended');

$cfglang = Kohana::config('locale.language');
$lang=$cfglang[0];
$now = time();
foreach ( $newslist as $news )
{
	$output  = '<div style=\'text-align:left\'>';		
	if ( $lang == 'it_IT')
	{
		$title = $news->title_it;
		$text = $news->text_it; 
	}
	else
	{
		$title = $news->title_en;
		$text = $news->text_en; 
	}
	if ( $news->create_date < ($now - 24*3600*5 ) )		
		$output .= '<h3>'.date("d M Y H:i:s",$news->create_date) ." - ".$title.'</h3>';		
	else
		$output .= "<h4 style='color:#c00'>".date("d M Y H:i:s",$news->create_date) ." - ".$title.'</h4>';
		
	$output .= '<p>'.nl2br($text).' <i>[' . $news->createdby.']</i></p>';		
	$output .= '</div>';
	echo $output;
}

echo $pagination->render('extended');
?>

<p class='right'><?php echo html::anchor('page/index',Kohana::lang('page-homepage.goto-homepage')); ?></p>
<br style="clear:both;" />
