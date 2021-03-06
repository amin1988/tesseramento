<?php

if (!defined("_BASE_DIR_")) exit();

include_model('Modello','ModelFactory');



class Settore extends Modello {

	const TAB = 'settori';

	const IDCOL = 'idsettore';

	

	/**

	 * @return Settore[]

	 */

	public static function elenco($where = "1"){

		$rs = Database::get()->select('settori',$where);
                

		return ModelFactory::get(__CLASS__)->listaCompleta(self::TAB, self::IDCOL);

	}

	

	public static function listaId($lista_id) {

		if (!is_array($lista_id) || count($lista_id) == 0) return array();

		

		$db = Database::get();

		return ModelFactory::get(__CLASS__)->listFromSql(

				$db->select(self::TAB, 'idsettore IN '.$db->quoteArray($lista_id)),

				'idsettore');

	}

	

	/**

	 * Restituisce il settore associato ad un certo id

	 * @param int $id l'id del settore

	 * @return Settore o NULL se non esiste nessun settore con l'id specificato

	 */

	public static function fromId($id) {

		return ModelFactory::get(__CLASS__)->fromId($id);

	}

	

	/**

	 * Confronta due settori in base al nome

	 * @param Settore $sa

	 * @param Settore $sb

	 * @return int

	 */

	public static function compare($sa, $sb) {

		return strcasecmp($sa->getNome(), $sb->getNome());

	}

	

	function __construct($id = NULL) {

		parent::__construct(self::TAB, self::IDCOL, $id);

	}

	

	function getNome() {

		return $this->get('nome');

	}

	

	function getPrezzo() {

		if(in_rinnovo())

			return $this->get('prezzo');

		else 

			return $this->get('prezzo_pieno');

	}

	

	function getPrezzoEuro() {

		if(in_rinnovo())

			return $this->get('prezzo')/100.0;

		else 

			return $this->get('prezzo_pieno')/100.0;

	}

	

	function getNote() {

		return $this->get('note');

	}
        function getAttivo() {

		return $this->get('attivo');

	}

	

	function setNome($nome) {

		$this->set('nome', $nome);

	}

	

	function setPrezzo($prezzo) {

		$this->set('prezzo', $prezzo);

	}

}