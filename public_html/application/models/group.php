<?php defined('SYSPATH') OR die('No direct access allowed.');

class Group_Model extends ORM
{

	protected $belongs_to = array( 'character' );

	/*
	* Costruisce il menu per i gruppi
	* @param string $action corrente azione
	* @returm string $html HTML
	*/
	
	static function get_groupmenu( $action )
	{
		$html = '';
		$class = '';
		
		if ($action == 'listall')
			$class = 'selected';
		else
			$class = '';
		
		$html .= html::anchor( 
			'/group/listall/',
			kohana::lang('groups.listall'),
			array('class' => $class )
		) . '&nbsp;';
		
		if ($action == 'mygroups')
			$class = 'selected';
		else
			$class = '';
		
		$html .= html::anchor( 
			'/group/mygroups/',
			kohana::lang('character.mygroups'),
			array('class' => $class )
		);				
		
		return $html;
		
	}
	
	/**
	* Estrae l'elenco dei chars che appartengono ad un gruppo
	* @param string $type Tipo gruppo
	* @param int $group_id ID Gruppo
	* @return none
	*/
	
	function get_all_members ($type, $group_id)
	{
		switch ($type)
		{
							
			case "all":
				// Prima di caricare le richieste pendenti, elimino quelle scadute
				$ora = (time() - 86400);
				ORM::factory('group_character')
					-> where( array( 'group_id' => $group_id, 'joined' => 0, 'date <' => $ora )) -> delete_all();
				$members = ORM::factory('group_character')->where( array( 'group_id' => $group_id) )->find_all();
				break;
			case "joined":
				$members = ORM::factory('group_character')->where( array( 'group_id' => $group_id, 'joined' => '1') )->find_all();
				break;
			case "pendent":
				// Prima di caricare le richieste pendenti, elimino quelle scadute
				$ora = (time() - 86400);
				ORM::factory('group_character')->where( array( 'group_id' => $group_id, 'joined' => 0, 'date <' => $ora ))->delete_all();
				$members = ORM::factory('group_character')->where( array( 'group_id' => $group_id, 'joined' => 0) )->find_all();
				break;
		}
		
		return $members;
	}
	
	function get_all_joined_members ()
	{
		
		$members = ORM::factory('group_character')->where( array( 'group_id' => $this -> id ) ) -> find_all();
		foreach ( $members as $member )
			$chars[] = $member -> character ;
		
		$chars[] = $this -> character ;
		
		return $chars;
	}
	
	// Funzione: search_a_members
	// Controlla se un char appartiene ad un gruppo
	// @Input: ID del char
	// @Output: True se il char è presente nel gruppo, false altrimenti
	
	function search_a_member ($char_id)
	{
		if ($char_id == $this->character_id)
		return true;
		
		$member = ORM::factory('group_character')->where( array('character_id' => $char_id, 'group_id'=>$this->id) )->find();
		if ( $member->loaded )
	
		return true;
		else
		return false;
	}
	
	/**
	* Aggiunge un membro ad un gruppo
	* @param group_id ID del gruppo
	* @char_id ID del char
	* @flag joined
	* @return none
	*/
	
	function add_member ($group_id, $char_id, $joined = false)
	{
	
		$group_character = ORM::factory('group_character');
		$group_character -> group_id = $group_id;
		$group_character -> character_id = $char_id;
		$group_character -> joined = $joined;
		$group_character -> date = time();
		$group_character -> save();
	
	}
	
	// Funzione: check_pendent_request
	// Cerca se ci sono delle richieste pendenti di adesione ad un gruppo
	// @Input: ID del gruppo, ID del char
	
	function check_pendent_request ($group_id, $char_id)
	{
		$request = ORM::factory('group_character')->where( array( 'character_id' => $char_id, 'group_id' => $group_id, 'joined' => 0) )->find();
		if ( $request->loaded )
			return true;
		else
			return false;
	}	

	/**
	* Funzione: accept_invite
	* Attiva la richiesta pendente di adesione ad un gruppo
	* @Input: ID del char da attivare, ID del gruppo
	*/
	
	function accept_invite ($char_id, $group_id)
	{
		$member = ORM::factory('group_character')->where( array( 'character_id' => $char_id, 'group_id' => $group_id ) )->find();
		$char = ORM::factory('character', $char_id );
		$group = ORM::factory	('group', $group_id ); 
		
		// Controllo che esista una richiesta pendente
		
		if ( ! $member -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error_no_pending_invite') . "</div>");
			url::redirect( 'character/details' );
		}
		
		// Controllo che la richiesta non sia scaduta
		
		if ( $member -> date < (time() - 86400) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error_invitation_expired') . "</div>");
			url::redirect( 'character/details' );
		}
			
		// Attivo lo status di membro effettivo
		
		$member -> joined = '1';
		$member -> save();
		
	}
	
	/**
	* Rimuove un membro dal gruppo
	* @group_id ID del gruppo
	* @char_id ID del char
	* @return none
	*/
	
	function remove_a_member ( $group_id, $char_id )
	{
		$member = ORM::factory('group_character') -> 
			where( array( 'character_id' => $char_id, 'group_id' => $group_id ) ) -> find();
		$member -> delete();
	}
	
	/** 
	* Funzione: get_char_groups
	* Trova i gruppi di un giocatore
	* @param: Oggetto char
	* @param: classification all, military
	* @return: oggetto ORM result contenente i gruppi
	*/
	
	function get_char_groups ( $char, $classification = 'all' )
	{
		if ( $classification == 'all' )
			$groups = ORM::factory('group') -> 
				where ( array( 'character_id' => $char -> id) ) -> find_all() ; 
		else
			$groups = ORM::factory('group') -> 
				where ( 
					array( 
						'character_id' => $char -> id,
						'classification' => $classification
						) ) -> find_all() ; 
		
		return $groups;
	}

	/** 
	* Funzione: count_members
	* Conta il numero dei membri di un gruppo
	* @Input: Id del gruppo
	* @Output: Numero dei membri
	*/
	
	function count_members ( $group_id )
	{
		$members = ORM::factory('group_character')->where( array( 'group_id' => $group_id ) )->count_all();
		// Aggiungo 1 membro (il fondatore)
		return ($members + 1) ;
	}

}
