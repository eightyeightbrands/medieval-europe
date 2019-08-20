	<?php defined('SYSPATH') OR die('No direct access allowed.');

class Test_Controller extends Template_Controller
{	
	const SECURITYKEY = 'ro432u4elwfjreljehgrehrekqthkgtqteegq';
	
	// Imposto il nome del template da usare
	
	public $template = 'template/gamelayout';

	function test( $functionname, $dryrun = true, $par1 = null, $par2 = null, $par3 = null, $par4 = null )
	{
		
		$this -> autorender = false;	
		$parameters = array( $par1, $par2, $par3, $par4 );
	
		kohana::log('debug', "-> Testing {$functionname}...");
		
		try 
		{
			Database::instance() -> query("set autocommit = 0");
			Database::instance() -> query("start transaction");
			Database::instance() -> query("begin");
		
			$callback = 'Test_Model::'.$functionname;		
			call_user_func_array( $callback, $parameters );
		
			if (!$dryrun)
			{
				Database::instance() -> query('commit');
				kohana::log('info', "Committed.");				
			}
			else
			{
				Database::instance() -> query('rollback');
				kohana::log('info', "Rollbacked.");								
			}
		} catch (Exception $e)
		{	
			var_dump($e -> getMessage());		
			var_dump($e -> getTraceAsString());
			kohana::log('error', $e->getMessage());
			kohana::log('error', 	"-> An error occurred, rollbacking.");
			Database::instance() -> query("rollback");			
		}		
		
		exit;
		
	}
	
}

?>
