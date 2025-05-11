<?php

use Denason\Wikimind\WikimindInterface;


Route::get('/mind', function (WikimindInterface $wikimind) {
    return $wikimind->entity('Q794');
});

Route::get('mind/search', function () {
    $mind = app(\Denason\Wikimind\WikimindInterface::class);
    return $mind->search('کهگیلویه و بویراحمد');
});

Route::get('mind/labels', function () {
    $mind = app(\Denason\Wikimind\WikimindInterface::class);

    return [
        'label' => $mind->label('Q794'),
        'desc' => $mind->description('Q794'),
        'aliases' => $mind->aliases('Q794')
    ];
});


Route::get('mind/properties', function () {
    $mind = app(\Denason\Wikimind\WikimindInterface::class);

    return [
        'properties' => $mind->properties('Q937'),
        'birthDate' => $mind->propertyValue('Q937', 'P569'),
    ];

});


Route::get('mind/media', function () {
    $mind = app(\Denason\Wikimind\WikimindInterface::class);
    return [
        'wiki_link' => $mind->sitelink('Q937'),
        'fa_link' => $mind->sitelink('Q937', 'fawiki'),
        'image' => $mind->getImage('Q937')
    ];
});

Route::get('mind/claims', function () {
    $mind = app(\Denason\Wikimind\WikimindInterface::class);

    $claims = $mind->claims('Q3143042');
    return array_keys($claims);
});

Route::get('mind/infobox', function () {

    return wikiMind()->structuredInfo('Q3143042', 'fa', 17);

});

Route::get('mind/filter', function () {

    $mind = app(WikimindInterface::class);


    $result = $mind->pickInfo('Q3143042', ['P30'], 'fa');

    return response()->json($result);
});

Route::get('mind/pickinfo', function () {
    $mind = app(\Denason\Wikimind\WikimindInterface::class);

    $result = $mind->pickInfoByName('iran', ['P47'], 'fa');

    return response()->json($result);
});


Route::get('mind/shortProfile', function () {
    return $mind = wikiMind()->shortProfile('Q3143042', 'fa');
});

Route::get('mind/smartSuggest', function () {
    return wikiMind()->smartSuggest('Iran', 'en', 'item', 10);
});


Route::get('mind/mindQuery', function () {


    $streetEntity = \Denason\Wikimind\Facades\Wikimind::getEntityId('street'); // Q79007

    return wikiMindQuery()
        ->lang('fa')
        ->where('street', 'P31', $streetEntity)
        ->where('street', 'P17', 'Q794')
        ->select(['streetLabel'])
        ->filter('!BOUND(?place)')
        ->distinct()
        ->limit(50)
        ->get('collection');



});









