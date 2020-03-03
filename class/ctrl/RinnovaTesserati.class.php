<?phpif (!defined('_BASE_DIR_'))    exit();include_model('Tesserato', 'Societa', 'Settore', 'Tipo', 'Polizza');include_form('Form');include_form(FORMELEM_CHECK, FORMELEM_LIST, FORMELEM_GRADO);//error_reporting(E_ALL);//ini_set('display_errors', 1);class RinnovaTesseratiCtrl{// 	const F_RINNOVA = 'rinnova';// 	const F_TIPO = 'tipo';// 	const F_GRADO = 'grado';    private $rinnovo;    /* @var$soc Societa */    private $soc;    private $form;    private $ltess = NULL;    private $sett = NULL;    private $tipi = NULL;    private $fase;    private $err = array();    private $poliz;    private $dati_poliz;    /**     * @param Settore $sa     * @param Settore $sb     */    public static function ordinaSett($sa, $sb)    {        return strcmp($sa->getNome(), $sb->getNome());    }    /**     * @param int $idsoc     * @param bool $rinnovo [def:false] true per considerare solo i tipi rinnovati      * @param callable $callback [opz] funzione da chiamare in caso di operazione effettuata correttamente     */    public function __construct($idsoc, $rinnovo = false, $callback = NULL, $fase)    {        $this->rinnovo = $rinnovo;        $this->soc = new Societa($idsoc);        $this->form = new FormRinnovo($this->soc);        $this->fase = $fase;        $this->poliz = new Polizza('polizze_stipulate');        $this->dati_poliz = $_POST['polizze_assicurative_tess'];               if ($this->form->isInviato())        {            $valido = $this->form->isValido();            $this->form->salvaTesseratiOk();            if (!empty( $this->dati_poliz))            {                foreach ( $this->dati_poliz as $codTess => $array_id_poliz)                {                    foreach ($array_id_poliz as $id_polizza)                    {                                              $array_dati_polizza = array("id_polizza" => $id_polizza, "idtesserato" => $codTess, "idsocieta" => $idsoc, "data_stipula" => date("d-m-Y"), "data_da" => NULL, "data_a" => NULL, "tipo_polizza" => "4");                        $this->poliz->salvaPolizzaTesserati($array_dati_polizza);                       $array_assicurazioni = $this->poliz->getAllPolizRichieste();                        $this->poliz->creaFileExcelPoliz($array_assicurazioni);                                                                  }                                    }                          }            if ($valido)            {                if (is_callable($callback))                    call_user_func($callback);            }            else            {                foreach ($this->form->getErrori() as $nome => $e)                {                    switch ($e)                    {                        case FORMERR_RINNOVO_GRADO:                            $msg = "Grado non selezionato";                            break;                        case FORMERR_RINNOVO_TIPI:                            $msg = "Nessuna qualifica selezionata";                            break;                        default:                            $msg = "Errore";                    }                    $this->err[$nome] = $msg;                }            }        }        $this->form->init($this->getTesserati());    }    public function getIdSocieta()    {        return $this->soc->getId();    }    public function getForm()    {        return $this->form;    }    /**     * Restitusice l'errore di un elemento     * @param string $nome     * @param mixed $key     * @return string|NULL     */    public function getErrore($nome, $key)    {        $ek = $nome . FormElem::keyToString($key);        if (isset($this->err[$ek]))            return $this->err[$ek];        else            return NULL;    }    public function isTesseratoErrato($idt)    {        return $this->form->isTesseratoErrato($idt);    }    /**     *      * @return Tesserato[]     */    public function getTesserati()    {        if ($this->ltess === NULL)        {            if ($this->rinnovo)                $this->ltess = Tesserato::getNonRinnovati($this->soc->getId(), $this->fase);            else                $this->ltess = Tesserato::getNonAttivi($this->soc->getId(), $this->fase);            uasort($this->ltess, array('Tesserato', 'compare'));        }        return $this->ltess;    }    public function getSettori()    {        if ($this->sett === NULL)        {            $this->sett = Settore::listaId($this->soc->getIDSettori());            uasort($this->sett, array(__CLASS__, 'ordinaSett'));        }        return $this->sett;    }    /**     *      * @return Tipo[]     */    public function getTipi($idsett)    {        if ($this->tipi === NULL)        {            $this->tipi = array();            foreach ($this->soc->getIDSettori() as $ids)            {                $this->tipi[$ids] = Tipo::getFromSettore($ids);            }        }        if (isset($this->tipi[$idsett]))            return $this->tipi[$idsett];        else            return array();    }    /**     * Restituisce true se un tesserato ha un tipo attivo     * @param Tesserato $tes     * @param integer $idtipo     */    public function isTipoAttivo($tes, $idtipo)    {        $qu = $tes->getQualificheUltime();        return isset($qu[$idtipo]);    }    public function getFase()    {        return $this->fase;    }}/** * Nessun tipo selezionato  */define('FORMERR_RINNOVO_TIPI', 'rinnovo_tipi');/** * Grado non selezionato */define('FORMERR_RINNOVO_GRADO', 'rinnovo_grado');class FormRinnovo extends Form{    const RINNOVA = 'rinnova';    const TIPO = 'tipo';    const GRADO = 'grado';    private $soc;    private $tessOk = NULL;    private $tessErr = NULL;    public function __construct($soc)    {        parent::__construct('rinnovatess');        $this->soc = $soc;    }    public function init($tesslist)    {        $ltipo = Tipo::getTipi();        foreach ($tesslist as $idt => $tess)        {            /* @var $tess Tesserato */            $uq = $tess->getQualificheUltime();            $ef = new FormElem_Check(self::RINNOVA, $this, $idt);            foreach ($ltipo as $idti => $tipo)            {                $chkVal = isset($uq[$idti]);                if ($chkVal)                {                    $q = $uq[$idti];                } else                {                    $q = NULL;                }                $check = new FormElem_Check(self::TIPO, $this, array($idt, $idti), $chkVal);                $checked = $check->getDefault();                new FormElem_Grado(self::GRADO, $this, $idti, $q, $idt);            }        }    }    function checkValidita($valido)    {        if (!$valido)            return false;        $ok = true;        $seltes = $this->getSentKeys(FormRinnovo::RINNOVA);        $this->tessOk = array();        foreach ($seltes as $idtess)        {            $err = false;            $tess = new Tesserato($idtess);            if (!$tess->esiste())                continue;            if ($tess->getIDSocieta() != $this->soc->getId())                continue;            $uq = $tess->getQualificheUltime();            $seltipo = $this->getSentKeys(FormRinnovo::TIPO, $idtess);            $count = 0;            foreach ($seltipo as $idtipo)            {                $tipo = Tipo::fromId($idtipo);                if ($tipo === NULL)                    continue;                $count++;                if (isset($uq[$idtipo]))                    $q = $uq[$idtipo];                else                    $q = NULL;                $eg = new FormElem_Grado(self::GRADO, $this, $idtipo, $q, $tess->getId());                $fe = $eg->getGrado();// 				$fe = new FormElem_List(FormRinnovo::GRADO, $this, array($idtess,$idtipo));                $grado = Grado::fromId($fe->getValoreId());                if ($grado === NULL) //grado non selezionato                {                    $this->err[$fe->getNomeKey()] = FORMERR_RINNOVO_GRADO;                    $err = true;                } elseif ($grado->getIDTipo() != $idtipo) //grado errato                {                    $this->err[$fe->getNomeKey()] = FORMERR_RINNOVO_GRADO;                    $err = true;                } else                {                    $this->tessOk[$idtess]['qual'][$idtipo] = $eg;                }            }            if ($count == 0)            {                //nessun tipo selezionato                $this->err[self::RINNOVA . FormElem::keyToString($idtess)] = FORMERR_RINNOVO_TIPI;                $err = true;            }            if ($err)            {                unset($this->tessOk[$idtess]);                $this->tessErr[$idtess] = true;            } else                $this->tessOk[$idtess]['tess'] = $tess;            $ok &=!$err;        }        return $ok;    }    public function isTesseratoErrato($idt)    {        if ($this->tessErr === NULL)            $this->isValido();        return isset($this->tessErr[$idt]);    }    public function salvaTesseratiOk()    {        if ($this->tessOk === NULL)            $this->isValido();        foreach ($this->tessOk as $val)        {            $tess = $val['tess'];            foreach ($val['qual'] as $idtipo => $eg)            {                /* @val $eg FormElem_Grado */                /* @val $t Tesserato */                $t = $val['tess'];                $t->setQualifica($idtipo, $eg->getGrado()->getValoreId());                $q = $t->getQualificaTipo($idtipo);                if ($q !== NULL)                {                    $extra = $q->getDatiExtra();                    if ($extra !== NULL)                    {                        foreach ($extra->getChiavi() as $key)                        {                            $el = $eg->getExtra($key);                            if ($el !== NULL)                                $extra->set($key, $el->getValoreId());                        }                    }                }            }            $tess->salva();        }    }    public function getTessOk()    {        return $this->tessOk;    }    public function getTessErr()    {        return $this->tessErr;    }}