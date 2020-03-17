<?php

/* SeedLiving Basket Module
 *
 * Copyright (c) 2016 Seeds of Diversity Canada
 *
 */

class SLiv_Basket
{
    private $oSLiv;

    function __construct( SEEDLiving $oSLiv )
    {
        $this->oSLiv = $oSLiv;
    }

    function Count()
    {
        $n = $this->oSLiv->kfdb->Query1( "SELECT count(*) FROM carts WHERE cart_userid='".$this->oSLiv->oUser->GetCurrUID()."'" );

        return( $n );
    }

}