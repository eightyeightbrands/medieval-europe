<?php defined('SYSPATH') OR die('No direct access allowed.');

class Batch_Controller extends Template_Controller
{
	public $template = 'template/gamelayout';

	function christmas_event_drops() {

		$this -> auto_render = false;

		// quantity of each to drop
		$basics_cnt = 4;
		$coins_cnt = 1000;
		$specials_cnt = 1;

		// item ids of possible drops

		// basic things
		$basics = array(170, 171, 172, 99, 97, 112, 109, 16, 71, 72, 73);

		// money
		$coins = array(6, 35);

		// special
		$specials = array(230, 231, 232, 233, 234, 235);


		// get regions
		$regions = Database::instance() -> query ('SELECT kingdom_id, id, name, status FROM regions group by kingdom_id');
		foreach($regions AS $row) {
			//print_r($row);

			for($i=0; $i<$basics_cnt; $i++) {
				//echo "basics give region: " . $row->id . " item: " . $basics[rand(0, count($basics)-1)] . " <br> \n";
				$this->item_in_region($row->id, $basics[rand(0, count($basics)-1)], 12);
			}

			//echo "coins give region: " . $row->id . " item: " . $coins[rand(0, count($coins)-1)] . " qnt: " . $coins_cnt . "<br> \n";
			$this->item_in_region($row->id, $coins[rand(0, count($coins)-1)], $coins_cnt);


			for($i=0; $i<$specials_cnt; $i++) {
				//echo "specials give region: " . $row->id . " item: " . $specials[rand(0, count($specials)-1)] . " <br> \n";
				$this->item_in_region($row->id, $specials[rand(0, count($specials)-1)], 1);
			}

		}


	}

	function item_in_region($region = 0, $item = 0, $quantity = 0) {
		if(!empty($region) && !empty($item) && !empty($quantity)) {
			$sql = "INSERT INTO `items` (`id`, `cfgitem_id`, `character_id`, `region_id`, `structure_id`, `npc_id`, `seller_id`, `lend_id`, `status`, `recipient_id`, `equipped`, `price`, `mindmg`, `maxdmg`, `persistent`, `defense`, `quantity`, `quality`, `salepostdate`, `tax_citizen`, `tax_neutral`, `tax_friendly`, `tax_allied`, `sendorder`, `color`, `hexcolor`, `locked`, `param1`, `param2`, `param3`) VALUES (NULL, '" . $item . "', NULL, '" . $region . "', NULL, NULL, NULL, NULL, 'New', NULL, 'unequipped', NULL, NULL, NULL, '0', NULL, '" . $quantity . "', '100.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL);";
			//echo "<br>\n";
			Database::instance() -> query ($sql);
		}
	}

	function mergeregions( $securitykey, $regionname1, $regionname2, $resourcetomantain, $climatosave, $simulate = true )
	{
		kohana::log('debug', "------- MERGING REGIONS: {$regionname1} {$regionname2} in: {$regionname2}, saving resource in {$resourcetomantain}, using clima of: {$climatosave}, dry run: {$simulate}");

		$this -> auto_render = false;

		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		try {
			Database::instance() -> query("set autocommit = 0");
			Database::instance() -> query("start transaction");
			Database::instance() -> query("begin");
			Batch_Model::mergeregions($securitykey, $regionname1, $regionname2, $resourcetomantain, $climatosave);

			if (!$simulate)
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

	}

	function respawnnpcs($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		try {
			Database::instance() -> query("set autocommit = 0");
			Database::instance() -> query("start transaction");
			Database::instance() -> query("begin");
			Batch_Model::respawnnpcs($securitykey);
			kohana::log('info', "Committing...");
			Database::instance() -> query('commit');
			kohana::log('info', "Committed.");
		} catch (Exception $e)
		{
			var_dump($e -> getMessage());
			kohana::log('error', $e->getMessage());
			kohana::log('error', 	"-> An error occurred, rollbacking.");
			Database::instance() -> query("rollback");
		}
	}

	function rechargebasicresources($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::rechargebasicresources($securitykey);
	}

	function splitkingdoms($securitykey=null, $kingdomname, $capitalname, $king, $title, $color )
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::splitkingdoms($securitykey, $kingdomname, $capitalname, $king, $title, $color );
	}

	function mergekingdoms($securitykey=null, $kingdomsourcename, $kingdomtargetname, $newkingdomname = null, $newkingdomimage = null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::mergekingdoms($securitykey, $kingdomsourcename, $kingdomtargetname, $newkingdomname, $newkingdomimage);
	}

	function takedoubloons() {

			if (!Auth::instance()->logged_in('admin'))
				url::redirect('/user/login');

				if(empty($_REQUEST['id'])) {
					die('missing id');
				}

				if(empty($_REQUEST['doubloons'])) {
					die('missing doubloons');
				}

				$reason = !empty($_REQUEST['reason']) ? $_REQUEST['reason'] : 'unknown';

				$character = ORM::factory('character', (int) $_REQUEST['id']);

				$starting_balance = $doubloons = $character->get_item_quantity('doubloon');
				if($doubloons < (int) $_REQUEST['doubloons']) {
					die('not enough doubloons');
				}

				$doubloons = (int) $_REQUEST['doubloons'];
				$doubloons = $doubloons * -1;

				$character->modify_doubloons($doubloons, $reason);
				$character->save();

				echo "removed " . $doubloons . " from " . $_REQUEST['id'] . " starting balance: " . $starting_balance . " new balance: ";
				echo $character->get_item_quantity('doubloon');

				/*
					// check if char has enough doubloons
					if ( $request -> character -> get_item_quantity( 'doubloon' ) < 150 )
					{
						Session::set_flash('user_message', "<div class=\"error_msg\">Il char non ha 150 dobloni.</div>");
						url::redirect('admin/wardrobeapprovalrequests');
					}
					// muovi immagini nella directory corretta

					Wardrobe_Model::approvecustomizeditems( $request );

					// take off doubloons
					$request -> character -> modify_doubloons( -150, 'wardrobeapprovalfree' );

					// marca request come accettata
					$request -> status = 'accepted';
					$request -> save();
				*/
	}

	function deleteavatar($securitykey=null, $character_id)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::deleteavatar($securitykey, $character_id );

	}

	function computekingdomsactivity($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::computekingdomsactivity($securitykey);

	}

	function watchdog($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::watchdog($securitykey);
	}

	function give_daily_revenues($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::give_daily_revenues($securitykey);
	}

	function initquest( $securitykey, $char_id, $questname )
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Utility_Model::initquest( $securitykey, $char_id, $questname );
	}

	function getitemaverageprices( $securitykey=null, $period )
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Utility_Model::getitemaverageprices( $securitykey, $period );
	}

	function reduceintoxicationlevel($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::reduceintoxicationlevel($securitykey);
	}

	function sendstarvingemail($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::sendstarvingemail($securitykey);
	}

	function checkpremiumexpiration($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::checkpremiumexpiration($securitykey);
	}

	function consumeitems($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::consumeitems($securitykey);
	}

	function givereferralcoins($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::givereferralcoins($securitykey);

	}

	function computestats($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::computestats($securitykey);

	}

	function cleanupdatabase($securitykey=null)
	{
		$this -> auto_render = false;
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		Batch_Model::cleanupdatabase($securitykey);

	}

	function tempproc ()
	{
		$this -> auto_render = false;

		$characters_plague_delete = Database::instance() -> query( " delete from character_stats where name ='disease' and param1='plague'; ");

					// stabilisce quanti giocatori per ogni regno devono essere infettati (10%)

					$characters_kingdom = Database::instance() -> query( "
						select r.kingdom_id,truncate(count(*)/100*7, 0) as count
						from characters c
						left outer join regions r on r.id=c.region_id
						where r.kingdom_id is not null
						group by r.kingdom_id;
					");


					//conto i regni attivi
					//$kingdomcount = mysql_query( "
					//select count(*) from kingdoms
					//where status !='deleted' and name not like '%independent%';
					//");

					//metto il numero di regni attivi nella variabile per usarla nel ciclo
					$rset = Database::instance() -> query(   "
					select * from kingdoms_v;
					" ) ;

					//azzero la varaibile del ciclo
				$totalitem = 0;

				$disease = DiseaseFactory_Model::createDisease( 'plague');


				foreach ( $characters_kingdom  as $row)
				{
					if ($totalitem <= count ( $rset ) )
					{
					$totalitem +=1;

					//inserisco in $temp tutti i char id del regno selezionato a questo giro
					$temp = Database::instance() -> query("select c.id from characters c
							left outer join regions r on r.id=c.region_id
							where r.kingdom_id= ". $row -> kingdom_id . "
							ORDER BY RAND()
							limit ". $row -> count . " ;" );

						kohana::log('debug', ('limite di infettati di '. $row -> count .' per il regno ' . $row -> kingdom_id .' ' ));

						foreach ( $temp as $row2  )
						{
						//infetto tutti i char id del regno selezionato a questo giro

						$disease -> injectdisease( $row2 -> id );
						}


					}



				}

	}
}
