<?php

if (!defined("_BASE_DIR_"))
        exit();

include_class('Data');

include_model('Modello', 'ModelFactory', 'Pagamento', 'Settore');



define('_PATH_ACSI_', 'acsi/');

/**

 * @access public

 */
class Societa extends Modello {

        const TAB_SETT = 'settori_societa';

        private $settori = NULL;
        private $consiglio = NULL;
        private $mod_settori = false;

        /**

         * @return Societa[]

         */
        public static function listaCompleta($id_fed = NULL) {
                $lista_id_settore_kudo = Societa::getSettori(7);
                $kudo_stringa = implode(",", $lista_id_settore_kudo);
                $nome_regione = isset($_POST['ricerca_reg']) ? $_POST['ricerca_reg'] : NULL;
                if (!empty($nome_regione)) {
	     $nome_regione = ucfirst(strtolower($nome_regione));

	     $array_idcomune = Societa::getProvinceDelReg($nome_regione);
	     $str_comuni = implode(",", $array_idcomune);
                }
                $and_comuni = "";
                if (!empty($str_comuni)) {
	     $and_comuni = " AND idcomune in ($str_comuni) ";
                }

                if ($id_fed === NULL)
	     return ModelFactory::listaSql(__CLASS__, Database::get()->select('societa'));
                else
	     return ModelFactory::listaSql(__CLASS__, Database::get()->select('societa', "idfederazione='$id_fed'  and idsocieta not in ($kudo_stringa) $and_comuni"));
        }
        
        public static function listaSocKudo()
        {
                 $lista_id_settore_kudo = Societa::getSettori(7);
                 return $lista_id_settore_kudo;
        }

        public static function listaCompletaKudo($nome_regione = "") {
                $lista_id_settore_kudo = Societa::getSettori(7);
                $kudo_stringa = implode(",", $lista_id_settore_kudo);
                $str_comuni = "";
                if (!empty($nome_regione)) {
	     $nome_regione = ucfirst(strtolower($nome_regione));

	     $array_idcomune = Societa::getProvinceDelReg($nome_regione);
	     $str_comuni = implode(",", $array_idcomune);
                }
                $and_comuni = "";
                if (!empty($str_comuni)) {
	     $and_comuni = " AND idcomune in ($str_comuni) ";
                }
                return ModelFactory::listaSql(__CLASS__, Database::get()->select('societa', "idsocieta in ($kudo_stringa) $and_comuni"));
        }

        private static function getProvinceDelReg($nome_regione) {
                $db = Database::get();
                $sql = "SELECT idregione FROM regioni WHERE nome like '$nome_regione' ";

                $result = $db->query($sql);
                if ($result) {
	     $row = $result->fetch_array(MYSQLI_ASSOC);
	     $idregione = $row['idregione'];
                }

                $query = "SELECT c.idcomune"
	     . " FROM comuni as c INNER JOIN province as p ON p.idprovincia = c.idprovincia"
	     . "  WHERE p.idregione=$idregione";
                $res = $db->query($query);
                $array_idcomune = array();
                if ($res) {

	     while ($row = $res->fetch_array(MYSQLI_ASSOC)) {

	             $array_idcomune[] = $row['idcomune'];
	     }
                }
                return $array_idcomune;
        }

        private static function getSettori($settore) {
                $sql = "SELECT idsocieta FROM settori_societa WHERE idsettore = $settore ";
                $db = Database::get();
                $result = $db->query($sql);
                $array_settore_kudo = array();
                if ($result) {

	     while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

	             $array_settore_kudo[] = $row['idsocieta'];
	     }
                }
                return $array_settore_kudo;
        }

        /**

         * @param integer $id

         * @return Societa

         */
        public static function fromId($id) {

                return ModelFactory::get(__CLASS__)->fromId($id);
        }

        /**

         * Confronta le società per nome completo

         * @param Societa $sa

         * @param Societa $sb

         */
        public static function compareFull($sa, $sb) {

                return strcasecmp($sa->getNome(), $sb->getNome());
        }

        /**

         * Confronta le società per nome breve

         * @param Societa $sa

         * @param Societa $sb

         */
        public static function compareBreve($sa, $sb) {

                return strcasecmp($sa->getNomeBreve(), $sb->getNomeBreve());
        }

        /**

         * Da utilizzare come callback per FormElem_AutoList

         * @param int $idreg

         * @param int $idprov [opz]

         */
        public static function ajax_comp($idfed) {

                return Societa::listaCompleta($idfed);
        }

        /**

         * Indica se una società ha un settore rinnovato per l'anno in corso

         */
        public function isRinnovata() {

                if (count(PagamentoUtil::get()->settoriPagati($this->getId())) > 0)
	     return true;
                else
	     return false;
        }

        public function __construct($id = NULL) {

                parent::__construct('societa', 'idsocieta', $id);
        }

        public function __toString() {

                return $this->getNomeBreve();
        }

        /**

         * Restituisce il codice della societa

         * @return int

         */
        public function getCodice() {

                return $this->get('codice');
        }

        /**

         * Restitusice il percorso del file ACSI relativo alla root del progetto,

         * da concatenare a _BASE_DIR_ o PATH_ROOT_

         * @return string 

         */
        public function getFileAcsi() {

                if ($this->haId())
	     return _PATH_ACSI_ . $this->getId() . '.pdf';
                else
	     return NULL;
        }

        /**

         * Indica se il file ACSI esiste

         * @return boolean

         */
        public function isFileAcsiEsistente() {

                if ($this->haId())
	     return file_exists(_BASE_DIR_ . $this->getFileAcsi());
                else
	     return false;
        }

        /**

         * Restituisce il codice ACSI della societa (codice assicurazione)

         * @return int

         */
        public function getCodiceAcsi() {

                return $this->get('acsi');
        }

        public function getIdComune() {

                return $this->get('idcomune');
        }

        public function getIdFederazione() {

                return $this->get('idfederazione');
        }

        /**

         * Restituisce il nome della societa

         * @return string

         */
        public function getNome() {

                return $this->get('nome');
        }

        /**

         * Restituisce il nome breve della societa

         * @return string

         */
        public function getNomeBreve() {

                return $this->get('nomebreve');
        }

        /**

         * Restituisce la data di costituzione della societa

         * @return Data

         */
        public function getDataCostituzione() {

                return $this->getData('data_cost');
        }

        /**

         * Restituisce la partita iva della societa

         * @return string

         */
        public function getPIva() {

                return $this->get('p_iva');
        }

        /**

         * Restituisce la sede legale della societa

         * @return string

         */
        public function getSedeLegale() {

                return $this->get('sede_legale');
        }

        /**

         * Restituisce il cap della societa

         * @return string

         */
        public function getCAP() {

                return $this->get('cap');
        }

        /**

         * Restituisce il numero telefonico della societa

         * @return string

         */
        public function getTel() {

                return $this->get('tel');
        }

        /**

         * Restituisce il numero del fax della societa

         * @return string

         */
        public function getFax() {

                return $this->get('fax');
        }

        /**

         * Restituisce l'email della societa

         * @return string

         */
        public function getEmail() {

                return $this->get('email');
        }

        /**

         * Restituisce il sito internet della societa

         * @return string

         */
        public function getSito() {

                return $this->get('web');
        }

        /**

         * Restituisce la data di inserimento della societa

         * @return Data

         */
        public function getDataInserimento() {

                return $this->getTimestamp('data_inserimento');
        }

        public function getLastCodice() {
                $max_codice = 0;
                $rs = Database::get()->select("societa", 1, "MAX(codice) as max_codice");
                if ($rs) {
	     $row = $rs->fetch_row();
	     $max_codice = $row[0];
                }
                return $max_codice;
        }

        /**

         * Restituisce gli id dei settori per i quali la società possiede un pagamento non scaduto

         * @return integer[]

         */
        public function getIDSettori() {



                if ($this->settori === NULL) {

	     if (!$this->haId())
	             return array();

	     $ids = $this->getId();

	     $rs = Database::get()->select(
	             'settori_societa ss INNER JOIN pagamenti_correnti pc ' .
	             'ON (ss.idsocieta=pc.idsocieta AND ss.idsettore=pc.idsettore)', "ss.idsocieta = '$ids'", 'ss.idsettore');



	     $this->settori = array();



	     while ($row = $rs->fetch_row()) {

	             $this->settori[$row[0]] = $row[0];
	     }
                }

                return $this->settori;
        }

        /**

         * Restituisce gli id degli ultimi settori salvati 

         * @return integer[]

         */
        public function getIDSettoriUltimi() {



                if (!$this->haId())
	     return array();

                $ids = $this->getId();

                $rs = Database::get()->select(
	     'settori_societa', "idsocieta = '$ids'", 'idsettore');



                $res = array();



                while ($row = $rs->fetch_row()) {

	     $res[] = $row[0];
                }



                return $res;
        }

        /**

         * 

         * @return Consiglio

         */
        public function getConsiglio() {

                if ($this->consiglio === NULL) {

	     include_model('Consiglio');

	     $this->consiglio = new Consiglio($this->getId());
                }



                return $this->consiglio;
        }

        /**

         * Restituisce il pagamento non scaduto di un settore

         * @param integer $idsett oppure oggetto Settore

         * @return Pagamento oppure NULL se non c'è nessun pagamento non scaduto

         */
        public function getPagamentoCorrente($idsett) {

                return Pagamento::getCorrenteSettore($this->getId(), $idsett);
        }

        /**

         * Indica se esiste un pagamento saldato per il settore ancora non scaduto

         * @param integer $idsett oppure oggetto Settore

         * @return boolean 

         */
        public function isSettorePagato($idsett) {

                $p = Pagamento::getCorrenteSettore($this->getId(), $idsett);



                return $p !== NULL && $p->isPagato();
        }

        /**

         *

         * @param int $val

         */
        public function setCodice($val) {

                $this->set('codice', $val);
        }

        /**

         *

         * @param int $val

         */
        public function setCodiceAcsi($val) {

                $this->set('acsi', $val);
        }

        /**

         *

         * @param int $val

         */
        public function setIDComune($val) {

                $this->set('idcomune', $val);
        }

        public function setIdFederazione($val) {

                $this->set('idfederazione', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setNome($val) {

                $this->set('nome', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setNomeBreve($val) {

                $this->set('nomebreve', $val);
        }

        /**

         *

         * @param Data $val

         */
        public function setDataCostituzione($val) {

                $this->setData('data_cost', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setPIva($val) {

                $this->set('p_iva', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setSedeLegale($val) {

                $this->set('sede_legale', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setCAP($val) {

                $this->set('cap', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setTel($val) {

                $this->set('tel', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setFax($val) {

                $this->set('fax', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setEmail($val) {

                $this->set('email', $val);
        }

        /**

         *

         * @param string $val

         */
        public function setSito($val) {

                $this->set('web', $val);
        }

        /**

         * Indica se la società si occupa di un settore

         * @param integer $idsett oppure oggetto Settore

         */
        public function haSettore($idsett) {

                if (is_object($idsett))
	     $idsett = $idsett->getID();

                $arrsett = $this->getIDSettori();

                return in_array($idsett, $arrsett);
        }

        /**

         * Aggiunge un settore a quelli di competenza della società

         * @param integer $idsettore

         */
        public function aggiungiSettore($idsettore) {

                $idsettore = intval($idsettore);



                $sett = $this->getIDSettori();



                if (!in_array($idsettore, $sett)) {

	     $this->settori[] = $idsettore;

	     $this->mod_settori = true;
                }
        }

        /**

         * Rimuove un settore da quelli di competenza della società

         * @param integer $idsettore

         */
        public function rimuoviSettore($idsettore) {

                $idsettore = intval($idsettore);



                $sett = $this->getIDSettori();



                $rim = array_search($idsettore, $sett);



                if ($rim !== false) {

	     unset($this->settori[$rim]);

	     $this->mod_settori = true;
                }
        }

        /**

         * Inserisce nuovo settore nel database con relativo pagamento

         * @param integer $sett id settore da inserire

         * @param array $val valori delle colonne della tabella settori_societa

         */
        private function settoreInDatabase($sett, $val) {

                $db = Database::get();

                $rp = true;



                $os = Settore::fromId($sett);

                $p = Pagamento::creaSettore($this->getId(), $os);

                $rs = $p->salva();

                if ($rs) {

	     $val['idsettore'] = $sett;

	     $rs = $db->insert('settori_societa', $val);

	     if (!$rs) {

	             $rp = false;

	             $p->elimina();
	     }
                } else
	     $rp = false;



                return $rp;
        }

        function salva() {

                $rp = parent::salva();



                if ($this->consiglio !== NULL)
	     $rp &= $this->consiglio->salva();



                return $rp;
        }

        protected function insert() {

                $rp = parent::insert();



                if (!$this->haId())
	     return $rp;



                Database::get()->insert('consiglio', array('idsocieta' => $this->getId()));



                if ($this->mod_settori) {

	     $db = Database::get();

	     $val = array('idsocieta' => $this->getId());

	     foreach ($this->settori as $sett)
	             $rp = $this->settoreInDatabase($sett, $val);



	     $this->mod_settori = !$rp;
                }



                return $rp;
        }

        protected function update() {

                $rp = parent::update();

                if (!$rp)
	     return $rp;



                if ($this->mod_settori) {

	     $idsoc = $this->getId();



	     $db = Database::get();

	     $rs = $db->select(self::TAB_SETT, "idsocieta='$idsoc'", 'idsettore');

	     $sdb = array(); //settori nel database

	     $del = array(); //settori da eliminare

	     while ($row = $rs->fetch_row()) {

	             $ids = $row[0];

	             $sdb[$ids] = $ids;

	             if (!in_array($ids, $this->settori))
		  $del[] = $ids; //è nel db ma non è selezionato, va eliminato
	     }

	     if (count($del) > 0) //se c'è da eliminare, elimina
	             $db->delete(self::TAB_SETT, "idsocieta='$idsoc' AND idsettore IN " . $db->quoteArray($del));



	     $val = array('idsocieta' => $idsoc);

	     foreach ($this->settori as $ids) {

	             if (!isset($sdb[$ids])) {

		  //non nel db, va inserito

		  $val['idsettore'] = $ids;

		  $rp &= $db->insert(self::TAB_SETT, $val);
	             }
	     }

	     //se non ci sono stati errori allora i settori non sono più modificati

	     $this->mod_settori = !$rp;



// 			//TODO rivedere completamente, in pagamenti_correnti potrebbero esserci due pagamenti per lo stesso settore
// 			$db = Database::get();
// 			$ids = $this->getId();
// 			//settori per cui esiste un pagamento
// 			$settl = array();
// 			//TODO creare metodo in Pagamento
// 			//cerca tutti i pagamenti della società relativi ai settori
// 			$rq = $db->select('pagamenti_correnti',"idsocieta='$ids' AND idsettore IS NOT NULL");
// 			$lp = ModelFactory::listaSql('Pagamento', $rq);
// 			//elimina i pagamenti per i settori che non sono selezionati
// 			foreach ($lp as $pag)
// 			{
// 				/* @var $pag Pagamento */ 
// 				$idsett = $pag->getIdSettore();
// 				if(!in_array($idsett, $this->settori))
// 				{
// 					//se il settore è stato eliminato
// 					if($pag->isPagato())
// 					{
// 						//se il settore era pagato non si può eliminare
// 						$this->settori[] = $idsett;
// 						$settl[] = $idsett;
// 					}
// 					else 
// 					{
// 						//elimina il pagamento e il settore
// 						$rp &= $pag->elimina();
// 						$rp &= $db->delete('settori_societa', "idsocieta='$ids' AND idsettore='$idsett'");
// 					}
// 				}
// 				else //è uno dei settori rimasti
// 					$settl[] = $idsett;
// 			}
// 			//elimina i settori non più selezionati
// 			//TODO che è? una select per selezionare gli elementi da eliminare e eliminarli uno per uno?????
// 			if(count($this->settori) == 0) $where = '';
// 			else $where = " AND idsettore NOT IN ".$db->quoteArray($this->settori);
// 			$rq = $db->select('settori_societa', "idsocieta='$ids' AND idsettore IS NOT NULL $where", 'idsettore');
// 			while($row = $rq->fetch_row())
// 				$db->delete('settori_societa', "idsocieta ='$ids' AND idsettore='$row[0]'");
// 			$val = array('idsocieta'=>$this->getId());
// 			foreach ($this->settori as $sett)
// 			{
// 				if(!in_array($sett, $settl))
// 					$rp = $this->settoreInDatabase($sett, $val);				
// 			}
// 			$this->mod_settori = !$rp;
                }

                return $rp;
        }

}

class SocietaUtil {

        private static $inst = NULL;

        /**

         * @return SocietaUtil

         */
        public static function get() {

                if (self::$inst === NULL)
	     self::$inst = new SocietaUtil();

                return self::$inst;
        }

        public function getLast($num) {

                $db = Database::get();

                $rs = $db->select('societa', "1 ORDER BY idsocieta DESC LIMIT $num");

                return ModelFactory::listaSql('Societa', $rs);
        }

        /**

         * Restituisce tutte le società registrate presso il comune indicato dall'id

         * @param int $idc l'id del comune

         */
        public function listaComune($idc) {

                $db = Database::get();

                $rs = $db->select('societa', "idcomune='$idc'");

                return ModelFactory::listaSql('Societa', $rs);
        }

}

?>