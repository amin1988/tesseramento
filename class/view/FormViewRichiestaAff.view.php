<script src="<?php echo _PATH_ROOT_; ?>js/utility.js"></script>
<?php
if (!defined("_BASE_DIR_"))
    exit();

include_formview('FormView');

abstract class FormViewRichiestaAff extends ViewWithForm
{

    protected $ctrl;
    protected $admin;

    /**

     * @param mixed $ctrl

     * @param bool $admin

     */
    public function __construct($ctrl, $admin)
    {

        $this->ctrl = $ctrl;

        $this->admin = $admin;

        $this->form = new FormView($this->ctrl->getForm());
    }

    public function stampaCss()
    {

        echo ".obbligatorio { font-weight: bold; }\n";
    }

    /**

     * @param string $label etichetta del campo

     * @param FormView $fv

     * @param string $nome nome del campo

     */
    private function stampaRiga($label, $fv, $nome, $attr = NULL, $help = '')
    {

        $err = $this->ctrl->getErrore($nome);

        echo '<div class="control-group';

        if ($err != '')
            echo ' error';

        echo '">';

        $el = $fv->getElem($nome);

        $obbl = $el->getFormElem()->isObbligatorio();

        echo "\n<label class=\"control-label";

        if ($obbl)
            echo ' obbligatorio';

        echo "\" for=\"form_$nome\">";

        if ($obbl)
            echo '* ';

        echo "$label:</label>\n";

        echo '<div class="controls">';

        $el->stampa($attr);

        echo ' <span class="help-inline">';

        echo "$help $err</span></div></div>\n";
    }

    public function stampa()
    {

        $fv = $this->form;

        $fv->stampaInizioForm();
        ?>

        <div class="form-horizontal">

            <div class="control-group">

                <div class="controls obbligatorio">* Campi obbligatori</div>

            </div>

        <?php
        if ($this->admin)
            $maxlen = 20;
        else
            $maxlen = 15;

        $this->stampaRiga('Federazione', $fv, FormAffiliazione::FEDER);

        $this->stampaRiga('Nome societ&agrave;', $fv, FormAffiliazione::NOME);

        $this->stampaRiga('Nome breve', $fv, FormAffiliazione::NOME_BREVE, array('maxlength' => $maxlen), "(max. $maxlen caratteri)");

        $this->stampaRiga('Data costituzione', $fv, FormAffiliazione::DATA_COST);

        $this->stampaRiga('Partita IVA', $fv, FormAffiliazione::P_IVA);

        $this->stampaRiga('Regione', $fv, FormAffiliazione::ID_REG);

        $this->stampaRiga('Provincia', $fv, FormAffiliazione::ID_PROV);

        $this->stampaRiga('Comune', $fv, FormAffiliazione::ID_COMUNE);

        $this->stampaRiga('Sede legale', $fv, FormAffiliazione::SEDE_LEG);

        $this->stampaRiga('CAP', $fv, FormAffiliazione::CAP);

        $this->stampaRiga('Telefono', $fv, FormAffiliazione::TEL);

        $this->stampaRiga('Fax', $fv, FormAffiliazione::FAX);

        $this->stampaRiga('Email', $fv, FormAffiliazione::EMAIL);

        $this->stampaRiga('Sito Web', $fv, FormAffiliazione::WEB);



        $err = $this->ctrl->getErrore(FormAffiliazione::SETTORE);

        echo '<div class="control-group';

        if ($err != '')
            echo ' error';

        echo '">'
        ?>

            <label class="control-label obbligatorio">* Settori:</label>

            <div class="controls"><?php
            foreach (Settore::elenco() as $idsett => $sett)
            {
                $settore_attivo = $sett->getAttivo();
                if (empty($settore_attivo))
                {
                    continue;
                }
                echo "<label class=\"checkbox sett_$idsett\">";
                //$fv->stampa(FormAffiliazione::SETTORE, $idsett,'onclick="onlyOne(this)');
                $ns = $sett->getNome();
              // echo " $ns";
                print $ns.'<input type="checkbox" name="settore" onclick="onlyOne(this)">

';

                //se qualcuno è loggato mostro le note dei settori

                if ($sett->getNote() !== NULL && Auth::getUtente() !== NULL)
                {

                    $note = $sett->getNote();

                    echo "<span class=\"text-info\"> $note</span>";
                }

                echo '</label>';
            }



            if ($err != '')
                echo "<span class=\"help-inline\">$err</span>";
            ?></div>

        </div>

        <div class="control-group">

            <div class="controls"><?php $this->stampaPulsanti($fv); ?></div>

        </div>

        </div>

                <?php
                $fv->stampaFineForm();
            }

//function stampa()

            /**

             * @param FormView $fv

             */
            protected abstract function stampaPulsanti($fv);
        }
        