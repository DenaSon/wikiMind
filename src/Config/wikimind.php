<?php

    /*
    |--------------------------------------------------------------------------
    |                           DenaSon/WikiMind Configuration
    |--------------------------------------------------------------------------
    */


   return [
       'api' => [
           'base_url' => 'https://www.wikidata.org/w/api.php',
           'sparql_url' => 'https://query.wikidata.org/sparql',
           'timeout' => 15,
           'retry' => [3, 200], // [api attempts, delay]
       ],
   ];




