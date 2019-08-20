<?php defined('SYSPATH') or die('No direct script access.');

// Replace the default kohana 404
Event::replace('system.404', array('Kohana', 'show_404'), 
    array('hook_404', 'show'));

class hook_404 {
    public function show()
    {
        require Kohana::find_file('views/page', 'custom_404');
				die();
    }

}