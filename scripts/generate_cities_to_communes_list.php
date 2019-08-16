<?php

require_once __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__.'/../belgium/sqlite/belgium.sqlite',
    'prefix' => '',
]);

$capsule->setAsGlobal();

$cities = Capsule::table('communes')
    ->join('cities', 'communes.id', '=', 'cities.commune_id')
    ->join('arrondissements', 'arrondissements.id', '=', 'communes.arrondissement_id')
    ->leftJoin('provinces', 'provinces.id', '=', 'arrondissements.province_id')
    ->select([
        'cities.name_fr AS city_fr',
        'cities.name_nl AS city_nl',
        'communes.name_fr AS commune_fr',
        'communes.name_nl AS commune_nl',
        'communes.nis_code AS commune_nis_code',
        'communes.lang AS commune_lang',
        'provinces.iso3166-2 AS province_iso',
    ])
    ->orderBy('arrondissements.id')
    ->orderBy('communes.id')
    ->orderBy('cities.id')
    ->get();

$citiesToCommunes = [];

foreach ($cities as $city) {

    if ($city->commune_fr === 'NULL') { $city->commune_fr = null; }
    if ($city->commune_nl === 'NULL') { $city->commune_nl = null; }

    $provinceISO = $city->province_iso ?? 'BE-BRU';

    $lang = in_array($city->commune_lang, ['fr', 'nl']) ? $city->commune_lang : 'fr';
    $nis_code = $city->commune_nis_code;

    if (!array_key_exists($provinceISO, $citiesToCommunes)) {
        $citiesToCommunes[$provinceISO] = [];
    }

    if (!array_key_exists($nis_code, $citiesToCommunes[$provinceISO])) {
        $citiesToCommunes[$provinceISO][$nis_code] = [
            'name_fr' => $city->commune_fr,
            'name_nl' => $city->commune_nl,
            'nis_code' => $city->commune_nis_code,
            'cities' => [],
        ];
    }

    $citiesToCommunes[$provinceISO][$nis_code]['cities'][] = $city->{'city_'.$lang};
}

file_put_contents(
    __DIR__.'/../belgium/php/communes_with_cities.json',
    json_encode($citiesToCommunes)
);

$json = json_encode($citiesToCommunes, JSON_PRETTY_PRINT);
echo "<pre>{$json}</pre>";
