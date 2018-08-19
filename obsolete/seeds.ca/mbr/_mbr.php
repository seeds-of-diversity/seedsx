<?

// Canada, not France.
// Can language be set with Canada?
// Instructions

require(SEEDCOMMON."mbr/mbrOrder.php");


$kfrdef_mbrPending = $kfrdef_MbrOrders;  // rename this variable

$kfrdef_mbrPendingOBSOLETE =
    array( "Tables"=>array( array( "Table" => 'mbr_order_pending',
                                   "Fields" => array( array("col"=>"mail_firstname",  "type"=>"S"),
                                                      array("col"=>"mail_lastname",   "type"=>"S"),
                                                      array("col"=>"mail_company",    "type"=>"S"),
                                                      array("col"=>"mail_addr",       "type"=>"S"),
                                                      array("col"=>"mail_city",       "type"=>"S"),
                                                      array("col"=>"mail_prov",       "type"=>"S"),
                                                      array("col"=>"mail_postcode",   "type"=>"S"),
                                                      array("col"=>"mail_country",    "type"=>"S"),
                                                      array("col"=>"mail_phone",      "type"=>"S"),
                                                      array("col"=>"mail_email",      "type"=>"S"),
                                                      array("col"=>"mail_lang",       "type"=>"I"),
                                                      array("col"=>"mail_eBull",      "type"=>"I", "default"=>1),
                                                      array("col"=>"mail_where",      "type"=>"S"),
                                                      array("col"=>"mbr_type",        "type"=>"S"),
                                                      array("col"=>"donation",        "type"=>"I"),
                                                      array("col"=>"pub_ssh_en",      "type"=>"I"),
                                                      array("col"=>"pub_ssh_fr",      "type"=>"I"),
                                                      array("col"=>"pub_nmd",         "type"=>"I"),
                                                      array("col"=>"pub_shc",         "type"=>"I"),
                                                      array("col"=>"pub_rl",          "type"=>"I"),
                                                      array("col"=>"notes",           "type"=>"S"),
                                                      array("col"=>"pay_total",       "type"=>"I"),
                                                      array("col"=>"pay_type",        "type"=>"I"),
                                                      array("col"=>"pay_status",      "type"=>"I"),
                                                      array("col"=>"pp_name",         "type"=>"S"),
                                                      array("col"=>"pp_txn_id",       "type"=>"S"),
                                                      array("col"=>"pp_receipt_id",   "type"=>"S"),
                                                      array("col"=>"pp_payer_email",  "type"=>"S"),
                                                      array("col"=>"pp_payment_status","type"=>"S"),
                                                      array("col"=>"sExtra",           "type"=>"S") ) ) ) );




$mbr_MbrTypes =     array( "reg1"     => array( "n"=>40, "l_EN"=>"One Year Membership",                  "l_FR"=>"L'adhésion (1 an)" ),
                           "reg3"     => array( "n"=>75, "l_EN"=>"Three Year Membership",                "l_FR"=>"L'adhésion (3 ans)" ),
                           "fixed"    => array( "n"=>25, "l_EN"=>"One Year Membership (student/senior)", "l_FR"=>"L'adhésion (étudiant ou retraité)" ),
                           "overseas" => array( "n"=>50, "l_EN"=>"One Year Membership (overseas)",       "l_FR"=>"L'adhésion (outre-mer)" )
                         );
$mbr_Pubs_NMDChange = 600;
$mbr_Pubs =         array( array( "ssh_en", "How to Save Your Own Seeds", 12 ),
                           array( "ssh_fr", "La conservation des semences du patrimoine", 12 ),
                           array( "nmd",    "Niche Market Development and Business Planning", 6 ),
                           array( "shc",    "Selling Heritage Crops", 8 ),
                           array( "rl",     "Resource List of Seed Companies", 2 )
                         );


define("MBR_PT_CHEQUE",   "1");
define("MBR_PT_PAYPAL",   "2");

$mbr_PayType =      array( MBR_PT_CHEQUE => "Cheque",
                           MBR_PT_PAYPAL => "PayPal"
                         );


define("MBR_PS_NEW",       "0");    //0
define("MBR_PS_CONFIRMED", "1");
define("MBR_PS_PAID",      "2");    //2
define("MBR_PS_FILLED",    "3");    //3
define("MBR_PS_CANCELLED", "4");    //1
define("MAX_MBR_PS", "4" );



$mbr_PayStatus =    array( MBR_PS_NEW       => "New, not confirmed",
                           MBR_PS_CONFIRMED => "Payment pending",
                           MBR_PS_PAID      => "Paid",
                           MBR_PS_FILLED    => "Paid, Order Filled",
                           MBR_PS_CANCELLED => "Cancelled"
                         );



$mbr_Text = array(
    "form_title"
        => array( "EN" => "Membership, Donation and Order Form",
                  "FR" => "Formulaire d'adhésion, don de charité et bon de commande" ),

    "sodc_name"
        => array( "EN" => "Seeds of Diversity Canada",
                  "FR" => "Semences du patrimoine Canada" ),

    "secthdr_membership"
        => array( "EN" => "Membership (New or Renewal)",
                  "FR" => "Adhésion (Nouveau ou Renouvellement)" ),

    "membership"
        => array( "EN" => "Membership",
                  "FR" => "Adhésion" ),

    "Annual_Membership_and_Donation"
        => array( "EN" => "Membership and Donation", // (one year)",
                  "FR" => "Adhésion et don de membre" ), // (un an)" ),

    "One Year Membership form line"
        => array( "EN" => "$40&nbsp;&nbsp;&nbsp;Membership for one year (new or renewal)",
                  "FR" => "40$&nbsp;&nbsp;&nbsp;Adhésion pour un an (nouveau ou renouvellement)" ),

    "Add a Charitable Donation"
        => array( "EN" => "Add a charitable donation",
                  "FR" => "Incluez une donation charitable" ),

    "CRA notice"
        => array( "EN" => "Please note: Due to CRA Regulations, the first $40 of your membership gift is not tax receiptable.<BR>"
                         ."Charitable registration no. 89650 8157 RR0001",
                  "FR" => "Veuillez noter que, dû à la loi sur l'impôt, le premier 40 $ de votre adhésion n'est pas un don de charité, "
                         ."le montant dépassant le premier 40 $ est un don de charité.<BR>"
                         ."Notre numéro d'organisme de charité est le : 89650 8157 RR0001" ),

    "mbr_calendar_year"
        => array( "EN" => "Membership is for the calendar year, January through December. If it is late in the year, ".
                          "your membership will begin in the next new year, unless you request otherwise.",
                  "FR" => "Toutes les adhésions débutent le 1er janvier et se terminent le 31 décembre. ".
                          "Si votre inscription se fait tard au cours de l'année, votre adhésion débutera dès le ".
                          "début de l'année suivante, à moins que vous ne le spécifiez autrement." ),


    "membership_desc"
        => array( "EN" => "<P>Membership includes:</P>".
                          "<UL compact='compact'>".
                          "<LI>a subscription to <I>Seeds of Diversity</I> magazine, published three times annually (Winter, Spring, Autumn)</LI>".
                          "<LI>our annual Seed Exchange Directory, mailed with the Winter magazine. This directory lists ".
                          "the seeds offered from member-to-member in our annual seed exchange (over 2400 varieties!)</LI></UL>",
                  "FR" => "<P>Votre adhésion comprend:</P>".
                          "<UL compact='compact'>".
                          "<LI>l'abonnement à notre revue, <I>Semences du patrimoine</I>, publié trois fois au cours de l'année (en hiver, printemps, et automne)</LI>".
                          "<LI>l'annuaire d'échange de semences, envoyé en hiver avec le premier numéro de la revue. ".
                          "Cet annuaire répertorie toutes les semences offertes par les membres aux membres (quelques ".
                          "2400 variétés!)</LI></UL>" ),

    "secthdr_donation"
        => array( "EN" => "Charitable Donation",
                  "FR" => "Don de charité" ),

    "donation_desc"
        => array( "EN" => "Seeds of Diversity Canada is a registered Canadian charity (89650 8157 RR0001). ".
                          "Please consider making a tax-creditable donation to support our horticultural preservation and educational projects.",
                  "FR" => "Semences du patrimoine Canada (Le Programme Semencier du Patrimoine Canada) est un organisme ".
                          "à statut charitables (89650 8157 RR0001).  Vos dons sont appréciés puisqu'ils nous soutiennent dans nos projets ".
                          "de préservation et d'éducation." ),

    "donation"
        => array( "EN" => "Donation",
                  "FR" => "Don" ),

    "donation_of"
        => array( "EN" => "Donation of",
                  "FR" => "Don de" ),

    "your_address"
        => array( "EN" => "Your Address",
                  "FR" => "Votre Addresse" ),

    "mailing_address"
        => array( "EN" => "Mailing Address",
                  "FR" => "Addresse Postal" ),

    "mail_firstname"
        => array( "EN" => "First Name",
                  "FR" => "Prénom" ),

    "mail_lastname"
        => array( "EN" => "Last Name",
                  "FR" => "Nom" ),

    "mail_company"
        => array( "EN" => "Organization or company",   // (if applicable)"
                  "FR" => "Organisme ou compagnie" ),  // (si applicable)"

    "mail_addr"
        => array( "EN" => "Address",
                  "FR" => "Addresse" ),

    "mail_city"
        => array( "EN" => "City or Town",
                  "FR" => "Ville" ),

    "mail_prov"
        => array( "EN" => "Province/State",
                  "FR" => "Province/État" ),

    "mail_postcode"
        => array( "EN" => "Postal Code / Zip",
                  "FR" => "Code postal" ),

    "mail_phone"
        => array( "EN" => "Telephone (with area code)",
                  "FR" => "Téléphone" ),

    "mail_email"
        => array( "EN" => "Email",
                  "FR" => "Courriel" ),

    "privacy_policy"
        => array( "EN" => "<B>Privacy Policy:</B>  Seeds of Diversity never sells or exchanges membership information ".
                          "with any other organization or company.  Our members' personal contact information is always ".
                          "kept strictly confidential.",
/* ?? */          "FR" => "Toutes les informations relatives aux membres sont traitées de manière confidentielle. ".
                          "Nous ne procurons jamais, ni ne vendons ou échangeons l'information relative à nos membres ".
                          "à d'autres organismes, compagnies ou individus." ),

    "ebull_desc"
        => array( "EN" => "Seeds of Diversity's e-Bulletin is a free periodic email newsletter about seeds, ".
                          "biodiversity and our horticultural conservation projects.",
/* ?? */          "FR" => "L'e-Bulletin de Semences du patrimoine est un communiqué par courriel au sujet des semences, ".
                          "la biodiversité et nos projets horticoles de conservation." ),

    "send_ebull"
        => array( "EN" => "Please send Seeds of Diversity's e-Bulletin to the email address above",
/* ?? */          "FR" => "Ajoutez-moi à la liste du e-Bulletin de Semences du patrimoine" ),

    "no_thanks"
        => array( "EN" => "No thankyou",
/* ?? */          "FR" => "Non" ),


    "mail_where"
        => array( "EN" => "Where did you learn about <nobr>Seeds of Diversity?</nobr>",
                  "FR" => "Comment avez-vous appris l'existence de notre programme?" ),

    "mail_note"
        => array( "EN" => "Send us a Note",
                  "FR" => "Envoyez-nous une note" ),

    "form_end_info"
        => array( "EN" => "<P>All prices include postage and handling, unless indicated otherwise, and all applicable taxes.</P>".
                          "<P>Any questions? Please call 1-866-509-SEED or email ".SEEDStd_EmailAddress("office","seeds.ca")."</P>",
                  "FR" => "<P>Les prix incluent les frais postaux, la manutention et les taxes en vigueur.</P>".
                          "<P>Questions?  Téléphonez 1 866 509-7333 ou envoyez un courriel à ".SEEDStd_EmailAddress("courriel","semences.ca")."</P>" ),

    "incl_postage_etc"
        => array( "EN" => "includes postage and handling unless indicated otherwise,<BR>and all applicable taxes",
                  "FR" => "inclut les frais postaux,<BR>la manutention et les taxes en vigueur" ),

    "Misc Payment"
        => array( "EN" => "Miscellaneous Payment",
/* TODO */        "FR" => "Miscellaneous Payment"),

    "Misc_payment_instructions"
        => array( "EN" => "For payments not itemized on this form, please enter the amount here and "
                          ."provide a detailed explanation in the Notes section below.<BR/>&nbsp;&nbsp;&nbsp;Amount: $",
/* TODO */        "FR" => "$" ),
    "next_button"
        => array( "EN" => "Next",
                  "FR" => "Continuer" ),

    "back_button"
        => array( "EN" => "Back",
                  "FR" => "Précédent" ),

    "order_num"
        => array( "EN" => "Order # ",
                  "FR" => "Ordre no. " ),

    "Thankyou"
        => array( "EN" => "Thankyou",
                  "FR" => "Merci" ),

    "credit_card"
        => array( "EN" => "Credit Card",
                  "FR" => "Carte de crédit" ),

    "cheque_mo"
        => array( "EN" => "Cheque / Money Order",
                  "FR" => "Chèque / Mandat postal" ),

    "copy"
        => array( "EN" => "copy",
                  "FR" => "copie" ),

    "secure_payment_paypal"
        => array( "EN" => "Secure payment through PayPal",
                  "FR" => "Paiement sûr via PayPal" ),

    "to_home"
        => array( "EN" => "Back to home page",
                  "FR" => "À page d'accueil" ),

    "Order_confirmed"
        => array( "EN" => "Order Confirmed",
                  "FR" => "L'ordre est confirmé" ),

    "Order_paid"
        => array( "EN" => "Order Paid",
                  "FR" => "L'ordre est payé" ),

    "Order_filled"
        => array( "EN" => "Order Filled",
                  "FR" => "L'ordre est complet" ),

    "Order_cancelled"
        => array( "EN" => "Order Cancelled",
                  "FR" => "L'ordre est décommandé" ),

    "order_already_confirmed"
        => array( "EN" => "<P>This order (# %1%) has already been confirmed.</P>",
                  "FR" => "<P>Cet ordre (no. %1%) a été déjà confirmé.</P>" ),

    "assistance"
        => array( "EN" => "<P>If you need assistance, please call 1-866-509-SEED or email ".
                          SEEDStd_EmailAddress( "office", "seeds.ca" ).".</P>",
                  "FR" => "<P>Si vous avez besoin d'assistance, téléphonez 1 866 509-7333 ou envoyez un courriel à ".
                          SEEDStd_EmailAddress("courriel","semences.ca")."</P>" ),

/* MBR1 */

    "Method of Payment"
        => array( "EN" => "Method of Payment",
                  "FR" => "Modalité de Paiement" ),

    "Select a method of payment"
        => array( "EN" => "Please select a method of payment",
                  "FR" => "Choisissez un modalité de paiement" ),

    "credit_card_desc"
        => array( "EN" => "Use our secure PayPal page for safe credit card payment.  Your order will be processed ".
                          "within five business days.  Please allow 2-3 weeks for delivery.",
                  "FR" => "Employez notre page sûre de PayPal pour le paiement de carte de crédit. ".
                          "Votre ordre sera traité dans cinq jours d'affaires. ".
                          "Veuillez accorder 2 ou 3 semaines pour la livraison." ),

    "cheque_desc"
        => array( "EN" => "Pay by cheque or money order.  Please allow 4-5 weeks for delivery.",
                  "FR" => "Payer par chèque ou mandat postal.  Veuillez accorder 4 ou 5 semaines pour la livraison." ),

    "to_pay_online"
        => array( "EN" => "<P><B>To Pay Online</B></P>".
                          "<P>Please fill in this form and click <B>Next</B> below.  You will have the option to ".
                          "use our secure PayPal page for safe credit card payment.  Your order will be processed ".
                          "within five business days.  Please allow 2-3 weeks for delivery.</P>",
/* ?? */          "FR" => "<P><B>Pour payer en ligne</B></P>".
                          "<P>Veuillez compléter cette forme et cliquez <B>Continuer</B>. Vous aurez l'option pour ".
                          "employer notre page sûre de PayPal pour le paiement de carte de crédit. ".
                          "Votre ordre sera traité dans cinq jours d'affaires. ".
                          "Veuillez accorder 2 ou 3 semaines pour la livraison.</P>" ),

    "to_pay_cheque"
        => array( "EN" => "<P><B>To Pay by Cheque / Money Order</B></P>".
                          "<P>Please fill in this form and click <B>Next</B> below.  You will have the option to ".
                          "pay by cheque or money order.  Please allow 4-5 weeks for delivery.</P>",
/* ?? */          "FR" => "<P><B>Pour payer par chèque / mandat postal</B></P>".
                          "<P>Veuillez compléter cette forme et cliquez <B>Continuer</B>. Vous aurez l'option pour ".
                          "payer par chèque ou mandat postal.  Veuillez accorder 4 ou 5 semaines pour la livraison.</P>" ),

    "please_check_one"
        => array( "EN" => "Please check one",
                  "FR" => "Cocher une case" ),

    "mbr_reg1"
        => array( "EN" => "One year membership",
                  "FR" => "Cotisation pour un an" ),

    "mbr_reg3"
        => array( "EN" => "Three year membership",
                  "FR" => "Cotisation pour trois ans" ),

    "mbr_fixed"
        => array( "EN" => "Fixed Income (student/senior)",
                  "FR" => "À revenu modeste (étudiant ou retraité)" ),

    "mbr_overseas"
        => array( "EN" => "Overseas (outside Canada and U.S.)",
                  "FR" => "Outre-mer (ailleurs qu'au Canada ou aux E.-U.)" ),

    "mbr_none"
        => array( "EN" => "No membership at this time",
                  "FR" => "Pas d'adhésion maintenant" ),

    "contact_for_bulk_rates"
        => array( "EN" => "Contact our office for discount rates on bulk orders of 10 or more.",
/*****/           "FR" => "" ),

    "see_descriptions_here"
        => array( "EN" => "<A HREF='".SITEROOT."vend/forsale.php' target='_blank'>See descriptions here</A>",
                  "FR" => "<A HREF='".SITEROOT."vend/vendre.php'  target='_blank'>Voir les descriptions en cliquant ici</A>" ),

    "vend_ssh_en"
        => array( "EN" => "<A HREF='".SITEROOT."vend/forsale.php#ssh_e' target='_blank'><IMG src='".SITEIMG."vend/ssh_cv.gif' height='50'></A>",
                  "FR" => "<A HREF='".SITEROOT."vend/vendre.php#ssh_e' target='_blank'><IMG src='".SITEIMG."vend/ssh_cv.gif' height='50'></A>" ),
    "vend_ssh_fr"
        => array( "EN" => "<A HREF='".SITEROOT."vend/forsale.php#ssh_f' target='_blank'><IMG src='".SITEIMG."vend/ssh_f_cv.gif' height='50'></A>",
                  "FR" => "<A HREF='".SITEROOT."vend/vendre.php#ssh_f' target='_blank'><IMG src='".SITEIMG."vend/ssh_f_cv.gif' height='50'></A>" ),
    "vend_nmd"
        => array( "EN" => "<A HREF='".SITEROOT."vend/forsale.php#niche1' target='_blank'><IMG src='".SITEIMG."vend/niche1_cv.gif' height='50'></A>",
                  "FR" => "<A HREF='".SITEROOT."vend/vendre.php#niche1' target='_blank'><IMG src='".SITEIMG."vend/niche1_cv.gif' height='50'></A>" ),
    "vend_shc"
        => array( "EN" => "<A HREF='".SITEROOT."vend/forsale.php#niche2' target='_blank'><IMG src='".SITEIMG."vend/niche2_cv.gif' height='50'></A>",
                  "FR" => "<A HREF='".SITEROOT."vend/vendre.php#niche2' target='_blank'><IMG src='".SITEIMG."vend/niche2_cv.gif' height='50'></A>" ),
    "vend_rl"
        => array( "EN" => "",   // dummy
                  "FR" => "" ),

    "title"
        => array( "EN" => "Title",
                  "FR" => "Titre" ),

    "price"
        => array( "EN" => "Price",
                  "FR" => "Prix" ),
    "quantity"
        => array( "EN" => "Quantity",
                  "FR" => "Quantité" ),

    "pub_ssh_en"
        => array( "EN" => "How to Save Your Own Seeds, 5th edition",
                  "FR" => "How to Save Your Own Seeds (Anglais)" ),

    "pub_ssh_fr"
        => array( "EN" => "La conservation des semences du patrimoine",
                  "FR" => "La conservation des semences du patrimoine" ),

    "pub_nmd"
        => array( "EN" => "Niche Market Development and Business Planning",
                  "FR" => "Niche Market Development and Business Planning (anglais seulement)" ),

    "pub_shc"
        => array( "EN" => "Selling Heritage Crops",
                  "FR" => "Selling Heritage Crops (anglais seulement)" ),
    "pub_rl"
        => array( "EN" => "Resource List of Seed Companies (<A href='".SITEROOT."rl/rl.php' target='_blank'>Free&nbsp;online</A>)",
                  "FR" => "Liste des Sources (<A HREF='".SITEROOT."rl/lr.php' target='_blank'>Disponsibles&nbsp;gratuitement</A>)" ),

    "overseas_instructions"
        => array( "EN" => "Overseas requests (outside Canada and U.S.) please contact our office for details on shipping, ".
                          "pricing and currency.<BR>Phone 1-866-509-SEED (7333) or email ".SEEDStd_EmailAddress("office","seeds.ca"),
/* ?? */          "FR" => "Outre-mer (ailleurs qu'au Canada ou aux E.-U.), contactez-nous pour l'information d'adhésion<BR>".
                          "Téléphonez 1 866 509-7333 ou envoyez un courriel à ".SEEDStd_EmailAddress("courriel","semences.ca") ),


/* MBR2 */
    "name_or_company_needed"
        => array( "EN" => "Name or company name is needed",
                  "FR" => "Nom ou compagnie est nécessaire" ),

    "address_needed"
        => array( "EN" => "Complete address is needed",
                  "FR" => "Addresse complète est nécessaire" ),

    "confirm_order"
        => array( "EN" => "Please Confirm Your Order",
                  "FR" => "Veuillez confirmer votre ordre" ),

    "donation_of"
        => array( "EN" => "Donation of",
                  "FR" => "Don de" ),

    "if_order_not_correct"
        => array( "EN" => "If this order is not correct",
                  "FR" => "Si cet ordre n'est pas correct" ),

    "change"
        => array( "EN" => "Change",
                  "FR" => "Changer" ),

    "click_here_paypal"
        => array( "EN" => "Click here to pay for your order with a major credit card, using our secure payment page ".
                          "hosted by <B>PayPal</B>. Your order will be processed within five business days. ".
                          "Please allow 2-3 weeks for delivery.",
/* ?? */          "FR" => "Cliquez ici pour employer notre page sûre de PayPal pour le paiement de carte de crédit. ".
                          "Votre ordre sera traité dans cinq jours d'affaires. ".
                          "Veuillez accorder 2 ou 3 semaines pour la livraison." ),

    "click_here_cheque"
        => array( "EN" => "Click here to pay with a cheque or money order.<BR/>Please allow 4-5 weeks for delivery.",
/* ?? */          "FR" => "Cliquez ici pour payer par chèque ou mandat postal.<BR/>".
                          "Veuillez accorder 4 ou 5 semaines pour la livraison." ),


/* MBR3 */

    "cheque_instructions"
        => array( "EN" => "<P>Please print this summary page and mail it with a cheque or money order payable to ".
                          "<B>Seeds of Diversity Canada</B>.".
                          "<BLOCKQUOTE><B>Seeds of Diversity Canada<BR>P.O. Box 36 Stn Q<BR>Toronto ON  M4T 2L7</B></BLOCKQUOTE></P>".
                          "<P>Please allow 4 - 5 weeks for delivery</P>",
                  "FR" => "<P>Veuillez faire imprimer ce page et expédier avec un chèque ou mandat postal. ".
                          "Libellez votre chèque au nom de <B>Programme semencier du patrimoine Canada</B>.".
                          "<BLOCKQUOTE>Programme Semencier du Patrimoine Canada<BR>Boîte postale 36, Station Q<BR>Toronto, Ontario M4T 2L7</BLOCKQUOTE></P>".
                          "<P>Veuillez accorder 4 ou 5 semaines pour la livraison.</P>" ),

    "paypal_instructions1"
        => array( "EN" => "<P>Please click the button below to go to PayPal's secure payment page.</P>",
/* dup in mbr2 */ "FR" => "<P>Cliquez ici pour employer notre page sûre de PayPal pour le paiement de carte de crédit.</P>" ),

    "paypal_instructions2"
        => array( "EN" => "<P>Your order will be processed within five business days.  Please allow 2-3 weeks for delivery. ".
                          "We suggest that you print this page for your records.</P>",
/* dup in mbr2 */ "FR" => "<P>Votre ordre sera traité dans cinq jours d'affaires. ".
                          "Veuillez accorder 2 ou 3 semaines pour la livraison. ".
                          "Nous suggérons que vous imprimiez cette page.</P>" ),

    "Pay Now"
        => array( "EN" => "Pay Now",
                  "FR" => "Payer maintenant" ),

    "Pay_by_credit"
        => array( "EN" => "Pay by Credit Card",
                  "FR" => "Payer par carte de crédit" ),

    "pay_by_credit_card_instead"
        => array( "EN" => "Pay by Credit Card Instead",
                  "FR" => "Payer par carte de crédit au lieu" ),

    "pay_by_cheque_instead"
        => array( "EN" => "Pay by Cheque Instead",
                  "FR" => "Payer par chèque au lieu" ),

    "Start_a_New_Order"
        => array( "EN" => "Start a New Order",
                  "FR" => "Commencer un nouvel ordre" ),

    "Start_new_order_link"
        => array( "EN" => "<P><A HREF='".MBR_FORM_URL_EN."'>Start a new order</A></P>", // this works because mbr1 resets the session if ! MBR_PS_NEW
                  "FR" => "<P><A HREF='".MBR_FORM_URL_FR."'>Commencer un nouvel ordre</A></P>" ),

    "postage"
        => array( "EN" => "postage",
                  "FR" => "les frais postaux" ),
);


function mbr_header( $mL )
/*************************
 */
{
    return;

    echo "<HTML><HEAD>";
    echo "<TITLE>".$mL->S('form_title')." - ".$mL->S('sodc_name')."</TITLE>";
    mbr_header_style();
    echo "</HEAD><BODY bgcolor='white'>";
    echo "<IMG SRC='".SITEIMG."logo_".$mL->GetLang().".gif'>";
    echo "<HR color=green>";
    echo "<BR>";
}


function mbr_header_style()
/**************************
 */
{
    ?>
    <style type='text/css'>
        body, p, input, td, th
                              { font-family: verdana,arial,helvetica,sans-serif;
                                font-size: 10pt;
                              }
        #mbr_form1            { font-size:x-small;
                              }
        #mbr_form1 h3         { color: green;
                              }

        .mbr_form1col_order   {
                              }

        .mbr_form1col_contactinfo {
                                border-left: medium ridge #CCCCCC;
                                border-bottom: medium ridge #CCCCCC;
                                padding-left: 2em;
                                padding-bottom: 2em;
                              }
        .mbr_form_box         {
                              }

        .mbr_form_boxheader   { background-color:#AAAAAA;  color: white; text-align:center; padding:4px; font-weight:bold; font-size:11pt;
                              }
        .mbr_form_boxbody     { background-color:#EEEEEE; padding:4px;
                              }
        .mbr_form_boxbody td, .mbr_form_boxbody input
                              { font-size:9pt; font-family: verdana,helvetica,sans-serif;
                              }
        .mbr_form_help        { font-size:8pt; margin-bottom:1em;
                              }
        .mbr_form_help p      { font-size:8pt;
                              }
        .form_sect_title      { font-size: medium;
                                font-weight: bold;
                                color:green;
                              }
        .form_sect_body       { font-size: medium;
                                margin-left: 3em;
                              }
        .form_sect_help       { font-size:8pt; }

        .instructions h3      { color:green; }
        .form_items           { font-size:12px; }
        .form_items_small     { font-size:9px; }

        #table_mbr2           { border-width: 1px 1px 1px 1px;
                                border: grey solid thin;
                              }
        #table_mbr2 th        { color:white;
                                background-color:green;

                              }
        #table_mbr2 td        { border: grey solid thin;
                                padding: 3px 3px 3px 3px;
                              }

    </style>
    <?
}

function draw_province( $lang = "EN", $selcode = "" )
/****************************************************
 */
{
    $raCdnEN = array( array( "AB1", "Alberta" ),
                      array( "BC1", "British Columbia" ),
                      array( "MB1", "Manitoba" ),
                      array( "NB1", "New Brunswick" ),
                      array( "NF1", "Newfoundland / Labrador" ),
                      array( "NT1", "Northwest Territories" ),
                      array( "NS1", "Nova Scotia" ),
                      array( "NU1", "Nunavut" ),
                      array( "ON1", "Ontario" ),
                      array( "PE1", "Prince Edward Island" ),
                      array( "QC1", "Quebec" ),
                      array( "SK1", "Saskatchewan" ),
                      array( "YT1", "Yukon Territory" ) );

    $raCdnFR = array( array( "AB1", "Alberta" ),
                      array( "BC1", "Colombie-Britannique" ),
                      array( "PE1", "Île-du-Prince-Édouard" ),
                      array( "MB1", "Manitoba" ),
                      array( "NB1", "Nouveau-Brunswick" ),
                      array( "NS1", "Nouvelle-Écosse" ),
                      array( "NU1", "Nunavut" ),
                      array( "ON1", "Ontario" ),
                      array( "QC1", "Québec" ),
                      array( "SK1", "Saskatchewan" ),
                      array( "NF1", "Terre-Neuve-et-Labrador" ),
                      array( "NT1", "Territoires du Nord-Ouest" ),
                      array( "YT1", "Yukon" ) );

    $raUS    = array( array( "AL2", "Alabama" ),
                      array( "AK2", "Alaska" ),
                      array( "AZ2", "Arizona" ),
                      array( "AR2", "Arkansas" ),
                      array( "CA2", "California" ),
                      array( "CO2", "Colorado" ),
                      array( "CT2", "Connecticut" ),
                      array( "DE2", "Delaware" ),
                      array( "DC2", "District of Columbia" ),
                      array( "FL2", "Florida" ),
                      array( "GA2", "Georgia" ),
                      array( "HI2", "Hawaii" ),
                      array( "ID2", "Idaho" ),
                      array( "IL2", "Illinois" ),
                      array( "IN2", "Indiana" ),
                      array( "IA2", "Iowa" ),
                      array( "KS2", "Kansas" ),
                      array( "KY2", "Kentucky" ),
                      array( "LA2", "Louisiana" ),
                      array( "ME2", "Maine" ),
                      array( "MD2", "Maryland" ),
                      array( "MA2", "Massachusetts" ),
                      array( "MI2", "Michigan" ),
                      array( "MN2", "Minnesota" ),
                      array( "MS2", "Mississippi" ),
                      array( "MO2", "Missouri" ),
                      array( "MT2", "Montana" ),
                      array( "NE2", "Nebraska" ),
                      array( "NV2", "Nevada" ),
                      array( "NH2", "New Hampshire" ),
                      array( "NJ2", "New Jersey" ),
                      array( "NM2", "New Mexico" ),
                      array( "NY2", "New York" ),
                      array( "NC2", "North Carolina" ),
                      array( "ND2", "North Dakota" ),
                      array( "OH2", "Ohio" ),
                      array( "OK2", "Oklahoma" ),
                      array( "OR2", "Oregon" ),
                      array( "PA2", "Pennsylvania" ),
                      array( "RI2", "Rhode Island" ),
                      array( "SC2", "South Carolina" ),
                      array( "SD2", "South Dakota" ),
                      array( "TN2", "Tennessee" ),
                      array( "TX2", "Texas" ),
                      array( "UT2", "Utah" ),
                      array( "VT2", "Vermont" ),
                      array( "VA2", "Virginia" ),
                      array( "WA2", "Washington" ),
                      array( "WV2", "West Virginia" ),
                      array( "WI2", "Wisconsin" ),
                      array( "WY2", "Wyoming" ) );

?>
<SCRIPT language='JavaScript'>
function setCountry() {
    var f = self.document.forms['mbr_form1'];
    var x = f.elements['mail_prov'].selectedIndex;
    var y = f.elements['mail_prov'].options[x].value;
    var z = y.substr( 2, 1 );

    self.document.getElementById('drawProv_country_txt').innerHTML = ((z == '2') ? 'USA' : 'Canada');
}
</SCRIPT>
<?

    echo "<SELECT name='mail_prov'  width=165  onChange='return setCountry()'>";
    echo "<OPTION VALUE=''>".($lang=="EN" ? "Please Select One":"Veuillez faire un choix")."</OPTION>";
    echo "<OPTION VALUE=''>-- Canada --</OPTION>";

    foreach( ($lang == "FR" ? $raCdnFR : $raCdnEN) as $v ) {
        echo "<OPTION VALUE='{$v[0]}'". (($selcode == $v[0]) ? " SELECTED" : "") .">{$v[1]}</OPTION>\n";
    }
    echo "<OPTION VALUE=''>-- U S A --</OPTION>";

    foreach( $raUS as $v ) {
        echo "<OPTION VALUE='{$v[0]}'". (($selcode == $v[0]) ? " SELECTED" : "") .">{$v[1]}</OPTION>\n";
    }
    echo "</SELECT>";
    echo "<BR><SPAN id='drawProv_country_txt'> </SPAN>";
}


function mbr_dollar( $d, $lang = "EN" ) { return( SEEDStd_Dollar($d,$lang) ); }
function dollar( $d, $lang = "EN" )     { return( SEEDStd_Dollar($d,$lang) ); }


function mbr_order_summary( $kfr, $mL )
/**************************************
 */
{
    global $mbr_MbrTypes, $mbr_Pubs, $mbr_PayType, $mbr_PayStatus, $mbr_Pubs_NMDChange;

    echo "<TABLE id='table_mbr2' border='1' cellpadding='10' cellspacing='0'>";
    echo "<TR><TH colspan=3>".$mL->S('order_num').$kfr->Key()."&nbsp;&nbsp;:&nbsp;&nbsp;".$kfr->value('_created');
    echo "<BR><BR>".$mL->S('mailing_address')."</TH></TR>";
    echo "<TR><TD colspan=3>";
    if( !$kfr->IsEmpty('mail_firstname') || !$kfr->IsEmpty('mail_lastname') )
        echo "<B>".$kfr->value('mail_firstname')." ".$kfr->value('mail_lastname')."</B><BR>";
    if( !$kfr->IsEmpty('mail_company') )
        echo "<B>".$kfr->value('mail_company')."</B><BR>";
    echo $kfr->value('mail_addr')."<BR>";
    echo $kfr->value('mail_city')." ".$kfr->value('mail_prov')." ".$kfr->value('mail_postcode')."<BR>";
    echo $kfr->value('mail_country')."<BR>";
    if( !$kfr->IsEmpty('mail_phone') )
        echo $kfr->value('mail_phone')."<BR>";
    if( !$kfr->IsEmpty('mail_email') )
        echo $kfr->value('mail_email')."<BR>";
    if( !$kfr->IsEmpty('notes') ) {
        echo "<BR/><B>Notes:</B><BR/>".str_replace("\n","<BR/>",$kfr->value('notes'))."<BR/>";
    }
    echo "</TD></TR>";

    /* Membership
     */
    if( !$kfr->IsEmpty('mbr_type') ) {
        $raMbrType = @$mbr_MbrTypes[ $kfr->value('mbr_type') ];
        echo "<TR><TH colspan=3>".$mL->S('membership')."</TH></TR>";
        if( is_array($raMbrType) ) {
            echo "<TR><TD colspan=2>".$raMbrType["l_".$mL->GetLang()]."</TD><TD>".dollar($raMbrType['n'],$mL->GetLang())."</TD></TR>";
        } else {
            echo "<TR><TD colspan=3><FONT color=red>Error: unknown membership type ".$kfr->value('mbr_type')."</FONT></TD></TR>";
        }
    }

    /* Donation
     */
    if( $kfr->value('donation') ) {
        echo "<TR><TH colspan=3>".$mL->S('secthdr_donation')."</TH></TR>";
        echo "<TR><TD colspan=2>".$mL->S('donation_of')." ".dollar($kfr->value('donation'),$mL->GetLang()).".  <I><B>".$mL->S('Thankyou')."!</B></I></TD><TD>".dollar($kfr->value('donation'),$mL->GetLang())."</TD></TR>";
    }

    /* Publications
     */
    $s = "";
    foreach( $mbr_Pubs as $k ) {
        $n = $kfr->value( 'pub_'.$k[0] );
        if( $n ) {
            if( $k[0] == 'nmd' && $kfr->Key() < $mbr_Pubs_NMDChange ) { // kluge - changed price from 5.5 to 6
                $s .= "<TR><TD>{$k[1]}</TD><TD>$n ".($n > 1 ? "copies" : $mL->S('copy'))." @ ".dollar(5.5,$mL->GetLang())."</TD><TD>".dollar($n * 5.5,$mL->GetLang())."</TD></TR>";
                continue;
            }

            $s .= "<TR><TD>{$k[1]}</TD><TD>$n ".($n > 1 ? "copies" : $mL->S('copy'))." @ ".dollar($k[2],$mL->GetLang())."</TD><TD>".dollar($n * $k[2],$mL->GetLang())."</TD></TR>";
        }
    }
    if( !empty($s) ) {
        echo "<TR><TH colspan=3>Publications</TH></TR>";
        echo $s;
    }

    /* Misc and Special
     */
    $s = $kfr->value( 'sExtra' );
    if( !empty($s) ) {
        $ra = SEEDStd_ParmsURL2RA( $s );
        foreach( $ra as $k => $v ) {
            if( $k == "fMisc" ) {
                echo "<TR><TH colspan=3>".$mL->S("Misc Payment")."</TH></TR>";
                echo "<TR><TD colspan='2'>".$mL->S("Misc Payment")."</TD><TD>".dollar($v)."</TD><TR>";
            }
            if( $k == 'nPubEverySeed' ) {
                $shipping = @$ra['nPubEverySeed_shipping'];
                echo "<TR><TH colspan=3>Publications</TH></TR>"
                    ."<TR><TD>Every Seed Tells a Tale</TD><TD>$v ".($v > 1 ? "copies" : $mL->S('copy'))." @ $35 + ".dollar($shipping)." ".$mL->S('postage')."</TD><TD>".dollar($v*35+$shipping)."</TD></TR>";
            }
            if( $k == 'nPubEverySeed_shipping' ) {
                // this is processed in nPubEverySeed
            }
            if( $k == 'nTorontoReg' && ($v = intval($v)) ) {
                echo "<TR><TH colspan=3>Registrations</TH></TR>";
                echo "<TR><TD>25th Anniversary Celebration (Toronto) - $v registrant".($v>1 ? "s" : "")."</TD><TD>&nbsp;</TD><TD>".dollar($v*35)."</TD><TR>";
            }
    	}
    }


    /* Total Payment
     */
    echo "<TR><TH colspan=3>&nbsp;</TH></TR>";
    echo "<TR><TD colspan=2><B>Total</B> (".$mL->S('incl_postage_etc').")</TD><TD>".($kfr->value('mail_country')=='Canada' ? "Cdn " : "US ").dollar($kfr->value('pay_total'),$mL->GetLang())."</TD></TR>";

    if( defined("MBR_ADMIN") && MBR_ADMIN ) {
        echo "<TR><TD colspan=1>Payment Status</TD><TD colspan=2>";
        echo @$mbr_PayType[$kfr->value('pay_type')]." - ".@$mbr_PayStatus[$kfr->value('pay_status')];
        echo "</TD></TR>";
        echo "<TR><TD colspan=3>&nbsp</TD></TR>";
        echo "<TR><TH colspan=3>Other Information</TH></TR>";
        echo "<TR><TD colspan=3><B>e-Bulletin:</B> ".($kfr->value('mail_eBull') ? "Y" : "N")."</TD></TR>";
        echo "<TR><TD colspan=3><B>Preferred Language:</B> ".($kfr->value('mail_lang') ? "French" : "English")."</TD></TR>";
        echo "<TR><TD colspan=3><B>Where did you hear about Seeds of Diversity:</B><BR>".$kfr->value('mail_where')."</TD></TR>";
        echo "<TR><TD colspan=3><B>Notes:</B><BR>".str_replace("\n","<BR>",$kfr->value('notes'))."</TD></TR>";
    }

    echo "</TABLE>";
}


function mbr_dberr_die()
/***********************
 */
{
    echo "<P>A database error has occurred.  Unable to store this transaction.  ";
    echo "Please report this error to ".SEEDStd_EmailAddress( "office", "seeds.ca" ).".  We apologise for this inconvenience.</P>";
    exit;
}

function mbr_enforce_statusNew_die( $kfr, $mL )         // not used - not sure that the link forces a new order anymore
/**********************************************
 */
{
    if( $kfr->value('pay_status') != MBR_PS_NEW ) {
        echo "<H2>".$mL->S('Order_confirmed')."</H2>".$mL->S('order_already_confirmed', array($kfr->Key()));
        echo $mL->S('assistance');
        echo $mL->S('Start_new_order_link');
        exit;
    }
}



?>
