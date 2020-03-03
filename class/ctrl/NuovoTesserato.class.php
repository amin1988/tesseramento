<?phpif (!defined('_BASE_DIR_')) exit();include_model('Tesserato','Societa','Tipo');include_controller('FormTesserato');class NuovoTesseratoCtrl {	/**	 * @var FormTesserato	 */	private $form;	private $err = array();	private $settori;	private $tipi;		/**	 * @param int $idsocieta	 * @param callable $callback chiamato con parametro Tesserato	 */	function __construct($idsocieta, $callback) {		$tes = new Tesserato();		$tes->setIDSocieta($idsocieta);		$disabile = empty($_POST['disabile']) ? 0 : 1;		$soc = new Societa($idsocieta);		$this->settori = $soc->getIDSettori();				$pagati = array();		if (in_rinnovo()) {			include_model('Pagamento');			foreach ($this->settori as $idsett)				$pagati[$idsett] = false;			foreach (PagamentoUtil::get()->settoriRinnovati($idsocieta) as $idsett)				$pagati[$idsett] = true;		} else {			foreach ($this->settori as $idsett)				$pagati[$idsett] = true;		}		foreach ($pagati as $idsett => $pag) {			if ($pag)				$this->tipi[$idsett] = Tipo::getFromSettore($idsett);		}				$f = new FormTesserato('nuovo_tesserato', $tes, true, $pagati);		$this->form = $f;		$f->addSubmit('Inserisci');				if ($f->isInviato())		{			if($f->isValido())			{				$tes = $f->getTesserato();				$tes->salva();                                if  (!empty($disabile)) //se è stato selezionato                                {                                    $tes->updateCampoTesserato('hp', '1', $tes->getId(), $idsocieta);                                }                               				if (is_callable($callback))					call_user_func($callback, $tes);			} else {				foreach ($this->form->getErrori() as $nome => $err) {					switch ($err) {						case FORMERR_OBBLIG:							$msg = 'Campo obbligatorio';							break;						case FORMERR_FORMAT:							$msg = 'Formato non valido';							break;						case FORMERR_DATA_MAX:							$msg = 'La data dev\'essere nel passato';							break;						case FORMERR_CODFIS_COERENZA:							$msg = 'Valore non coerente con il codice fiscale';							$this->err[FormTesserato::COD_FIS] = 'Valore non coerente con i dati inseriti';							break;						case FORMERR_TESSERATO_PRESENTE:							$msg = 'Tesserato già presente in questa società';							$this->err[FormTesserato::NOME] = 'Tesserato già presente in questa società';							$this->err[FormTesserato::COGN] = 'Tesserato già presente in questa società';							$this->err[FormTesserato::DATA_N] = 'Tesserato già presente in questa società';							break;						default:							$msg = "Errore $err";							break;					}					//TODO codice fiscale					$this->err[$nome] = $msg;				}			}			Log::debug('nuovo tesserato senza redirect', $_POST);		}	}		/**	 * @return FormTesserato	 */	public function getForm() {		return $this->form;	}		public function getErrori() {		return $this->err;	}		public function getErrore($nome) {		if (isset($this->err[$nome])) 			return $this->err[$nome];		else			return '';	}		public function getErroreQualifica($idtipo) {		$nome = FormTesserato::GRADO . FormElem::keyToString($idtipo);		return $this->getErrore($nome);	}		/**	 * 	 * @return integer[]	 */	public function getSettori()	{		return $this->settori;	}		/**	 *	 * @param integer $idsett	 */	public function getNomeSettore($idsett) {		return Settore::fromId($idsett)->getNome();	}		/**	 * @param int $idsett	 * @return Tipo[]	 */	public function getTipiSett($idsett) {		if (isset($this->tipi[$idsett]))			return $this->tipi[$idsett];		else 			return array();	}	}