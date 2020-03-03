<?php

session_start();

require_once 'config.inc.php';



include_model('Federazione','Societa');

include_view('SaldoPagamentiTot','SaldoPagamentiSett');



$tmpl = get_template();

$tmpl->setAttr(TMPL_ATTR_SEZ, SEZ_SOC_PAGAMENTI);

$tmpl->setTitolo('Pagamenti');



$idsoc = Auth::getUtente()->getIDSocieta();

$soc = Societa::fromId($idsoc);

$soc_str = "Soc. ".$soc->getNomeBreve();

if($soc->getIdFederazione() != 1)

{

	$fed = Federazione::fromId($soc->getIdFederazione());

	$str = "Fed. ".$fed->getNome()." - ".$soc_str;

}

else 

	$str = $soc_str;







$anno_att = date('Y');

$rinnovo = in_rinnovo();

if ($rinnovo)

	$as = $anno_att.'/'.($anno_att+1);

else 

	$as = ($anno_att-1).'/'.$anno_att;



$tmpl->addBody("<div class=\"row\"><div class=\"well well-small span8 offset2\"><strong>BANCA POPOLARE DI MILANO</strong><br>\n"

		." intestato a <em>FIAM  (Federazione Italiana Arti Marziali a.s.d.)</em><br>\n"

		."IBAN <strong>IT19 R 05034 03300 000000005372</strong><br>\nCausale <em>\"$str - Tesseramento $as \"</em></div></div>");



if ($rinnovo) {

// 	//tabs

// 	$tmpl->addBody('<br><ul class="nav nav-tabs"><li><a href="#attuale" data-toggle="tab">Anno ' . $anno_att

// 			. '</a></li> <li class="active"><a href="#rinnovo" data-toggle="tab">Anno ' . ($anno_att+1)

// 			. '</a></li></ul>');

// 	$tmpl->addBody('<div class="tab-content"><div class="tab-pane" id="attuale"><h2>Anno '.$anno_att.'</h2>',

// 			new SaldoPagamentiTot($idsoc, 0), new SaldoPagamentiSett($idsoc, 0),

// 			'</div><div class="tab-pane active" id="rinnovo"><h2>Anno '.($anno_att+1).'</h2>',

// 			new SaldoPagamentiTot($idsoc, 1), new SaldoPagamentiSett($idsoc, 1),

// 			'</div></div>');

	

	//doppia schermata

	$tmpl->addBody('<br><div class="row-fluid"><div class="span6 well"><h2>Anno '.$anno_att.'</h2>',

			new SaldoPagamentiTot($idsoc, 0), new SaldoPagamentiSett($idsoc, 0),

			'</div><div class="span6 well"><h2>Anno '.($anno_att+1).'</h2>',

			new SaldoPagamentiTot($idsoc, 1), new SaldoPagamentiSett($idsoc, 1),

			'</div></div>');

	

} else {

	$tmpl->addBody(new SaldoPagamentiTot($idsoc, 0), new SaldoPagamentiSett($idsoc, 0));

}

$tmpl->stampa();