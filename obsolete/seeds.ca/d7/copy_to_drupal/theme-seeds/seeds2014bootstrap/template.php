<?php

/**
 * @file
 * template.php
 */

function seeds2014bootstrap_preprocess_page( &$variables ) {
    $variables['foo'] = "bar";
}

function seeds2014bootstrap_preprocess_block( &$variables )
{
    if( @$variables['block_html_id'] == 'block-book-navigation') {
        //var_dump($variables);
        $variables['classes_array'][] = 'well-sm';
    }
}