<?php
//require_once ‘PHPUnit/Framework.php’;
class Character_ModelTest extends PHPUnit_Framework_TestCase
{
    public function test_is_newbie()
    {
				$char = new Character_Model();				
				$char -> birthdate = time();				
				$this -> assertEquals(true, $char -> is_newbie($char) );
				$char -> birthdate = time() - (31*24*3600);				
				$this -> assertEquals(false, $char -> is_newbie($char) );
    }
		
		public function test_modify_doubloons()
		{
			
			$char = ORM::factory('character', 1);						
			$predoubloons = $char -> get_item_quantity_d( $char -> id, 'doubloon' );			
			$char -> modify_doubloons( +30, 'test' );
			$postdoubloons = $char -> get_item_quantity_d( $char -> id, 'doubloon' );
			
			$this -> assertEquals(30, $postdoubloons - $predoubloons );
			
			$predoubloons = $char -> get_item_quantity_d( $char -> id, 'doubloon' );			
			$char -> modify_doubloons( -3, 'test' );
			$postdoubloons = $char -> get_item_quantity_d( $char -> id, 'doubloon' );
			
			$this -> assertEquals(-3, $postdoubloons - $predoubloons );
			
			
		}
}
?>