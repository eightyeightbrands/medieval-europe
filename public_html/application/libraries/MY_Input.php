<?php

/*
 * overloadata la classe xss_clean poichè l' xss clean verifica se ci sono attacchi xss ma non 
 * filtrava l' html. 
 */

class Input extends Input_Core {
  public function xss_clean($data, $tool = NULL)
  {
   $data = parent::xss_clean($data, $tool);	
	 return strip_tags($data);
  }
}