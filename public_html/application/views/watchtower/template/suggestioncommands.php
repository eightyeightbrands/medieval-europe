<?
echo html::anchor( 'suggestion/view/' . $suggestion -> id, 
html::image(
	'media/images/template/icon-info.png',
	array('class' => 'size20 border')
	),
array( 
	'title' => kohana::lang('suggestions.fulldetails'),	
));
echo '&nbsp;';
	
if ( $suggestion -> discussionurl != '')
{
	echo html::anchor( $suggestion -> discussionurl, 
	html::image(
		'media/images/template/icon-discussion.png',
		array('class' => 'size20 border')
		),
	array( 
	'title' => kohana::lang('suggestions.discussionurl'), 
	'target' => 'new'));
	echo '&nbsp;';
}
	
if ( 
	(
		Session::instance() -> get('isadmin')
		or
		$suggestion -> character_id == $char -> id
	)
	and in_array($suggestion -> status, array( 'new' ))
	
)
{
	echo html::anchor('suggestion/edit/' . $suggestion -> id, 
	html::image(
		'media/images/template/icon-edit.png',
		array('class' => 'size20 border')
		),
	array( 'title' => kohana::lang('global.edit')));
	echo '&nbsp;';
}

if ( 
	(
		Session::instance() -> get('isadmin')
		or
		$suggestion -> character_id == $char -> id
	)
	and in_array($suggestion -> status, array( 'new' ))	
)
{
	echo html::anchor('suggestion/remove/'. $suggestion -> id, 
	html::image(
		'media/images/template/icon-delete.png',
		array('class' => 'size20 border')
		),
	array( 
		'title' => kohana::lang('global.delete')));

	echo '&nbsp;';
}

				
// solo se è stato stabilito un costo si può sponsorizzare la CR	

if ( $suggestion -> quote > 0 and $suggestion -> quote > $suggestion -> sponsoredamount )
{
	echo html::anchor($suggestion -> detailsurl,
		html::image(
			'media/images/template/icon-analysis.png',
			array('class' => 'size20 border')
		),
		array(
			'target' => 'new',
			'title' => kohana::lang('suggestions.analysis')
		)
	);
	
	echo html::anchor('suggestion/sponsor/'. $suggestion -> id . '/50', 
		html::image(
			'media/images/template/icon-sponsor.png',
			array('class' => 'size20 border')
		),
		array(
			'title' => kohana::lang('suggestions.sponsor', 50)
		)
	);
	echo html::anchor('suggestion/sponsor/'. $suggestion -> id . '/500', 
		html::image(
			'media/images/template/icon-sponsor.png',
			array('class' => 'size20 border')
		),
		array(
		'title' => kohana::lang('suggestions.sponsor', 500)
		)
	);
}
?>