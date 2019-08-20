
PHP FormatterHomeFeaturesContactAbout
Bookmark and Share 

Welcome to the new PHP Formatter!
We've given PHP Formatter a new design as well as a new engine! The new engine features:
Blazingly fast, on the fly formatting of all scripts!
PHP 4 and PHP 5 support
Handy syntax check function
Ability to create your own coding styles, or to use builtin styles
Proper handling of doc comments, and alternative control structures


InputStyleFormat
1
2
3
4
5
6
7
8
9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
<?php
defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_easteregg_Model extends PremiumBonus_Model
{
    var $randomnumber = 0;
    var $availableprizes = null;
    function __construct()
    {
        $this->name        = 'easteregg';
        $this->canbegifted = false;
    }
	
    /**    
	* Make checks    
	* @param obj $targetchar Character that receives the bonus    
	* @param obj $targetstructure Structure that receives the bonus    
	* @param string $cut Chosen Cut    
	* @param array $par array of parameters    
	* @param string $message feedback message    
	* @param boolean $free (is bonus free of charge)    
	* @returns boolean     */
    
    protected function checks($targetchar, $targetstructure, $cut, $par = null, &$message, $free)
    {
        $currentchar = Character_Model::get_info(Session::instance()->get('char_id'));
        if (parent::checks($targetchar, $targetstructure, $cut, $par = null, $message, $free) == false)
            return false;
        // TODO: check cooldown                
        $lastbought = Character_Model::get_stat_d($currentchar->id, 'lastbonusbought', null, null);
        if ($lastbought->loaded and time() - $lastbought->stat1 <= 60) {
            $message = 'You need to wait at least a minute before buying the next egg.';
            return false;
        }
        // Event ended?        
        //kohana::log('debug', date("YmdHi", time()));exit;                
        if (date("YmdHi", time()) >= '201804252000') {
            $message = 'Sorry but this event is finished.';
            return false;
        }
        Database::instance()->query("LOCK TABLES events_randomextractions WRITE");
        $rset = Database::instance()->query("SELECT id         FROM events_randomextractions         WHERE status = 'available'         ORDER by RAND() limit 1");
        if ($rset->count() == 0) {
            $message = "Sorry, but there are no more available prizes.";
            Database::instance()->query("UNLOCK TABLES");
            return false;
        }
        Database::instance()->query("        UPDATE events_randomextractions        SET character_id = {$currentchar -> id},        status = 'reserved'         WHERE id = {$rset[0] -> id}");
        $this->randomnumber = $rset[0]->id;
        Database::instance()->query("UNLOCK TABLES");
        Character_Model::modify_stat_d($currentchar->id, 'lastbonusbought', 0, null, null, true, time());
        return true;
    }
	
    function postsaveactions($char, $cut, $par, &$message)
    {
        $info         = $this->get_info();
        $item         = Item_Model::factory(null, $this->get_name());
        $item->param1 = $this->randomnumber;
        $item->additem('character', $char->id, $info['cuts'][$cut]['cut']);
        parent::postsaveactions($char, $cut, $par, $message);
        return true;
    }
}
