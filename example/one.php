<?php

/**
 * Šiame pavyzdyje parodoma kaip nusiųsti vieną skelbimą
 * į rinka.lt portalą.
 * Laukelių informacija: http://rinka.lt/asiSpecification
 */

if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));
require_once __DIR__ . '../src/RinkaAsi/RinkaAsi.php';

// Čia turėtumėte įvesti savo prisijungimo vardą (username) ir slaptažodį (password)
$Asi = new RinkaAsi(
    new RinkaAsiConfig(array(
        'username' => 'YOUR_USERNAME',
        'password' => 'YOUR_PASSWORD',
    ))
);

$Categories = $Asi->getCategories();
$ExportDocument = $Asi->createExportDocument();

// Kategorijos nustatymas
$Category = $Categories->getCategory(array(
    "perka",
    "transportas",
    "lengvieji_automobiliai",
));

$Entry = $ExportDocument->createNewInsertEntry($Category);

// Jūsų sistemoje sugeneruotas unikalus ID
$Entry->setLocalId(123);

// Kontaktų pridėjimas
$Entry->addContact('miestas', array('lietuva', 'vilnius'));
$Entry->addContact(array(
    'el_pastas' => 'username@domain.tld',
    'telefonas' => '+37060000000'
));

// Paveikslėlių pridėjimas
$Entry->addImage(array(
    'http://domain.tld/1.jpg',
    'http://domain.tld/2.jpg',
));

// Galiojimo datos
$Entry->setPublishDate(date('Y-m-d'));
$Entry->setPublishUntil(date('Y-m-d', strtotime('+3 MONTH')));

// Nuoroda į skelbimą jūsų puslapyje
$Entry->setSourceUrl('http://your_domain.tld/advertisiment/1234');

// Laukelių pridėjimas

    // Markė
    $Entry->setFieldValue('marke', array('ac', 'aro', 'dodge', 'ferrari', 'honda', 'isuzu', 'jeep', 'lincoln', 'mercedes_benz', 'moskvich', 'nissan', 'oldsmobile', 'peugeot', 'plymouth', 'renault', 'santana', 'saturn', 'smart', 'ssang_yong'));

    // Modelis
    $Entry->setFieldValue('modelis', 'tekstinis laukas');

    // Pagaminimo data (nuo-iki)
    $Entry->setFieldValue('pagaminimo_data', 123, 123);

    // Darbinis tūris, cm³ (nuo-iki)
    $Entry->setFieldValue('darbinis_turis_cm', 123, 123);

    // Galia, AG/kW
    $Entry->setFieldValue('galia_ag_kw', 123, 123, 'kw');

    // Kuro tipas
    $Entry->setFieldValue('kuro_tipas', 'dujos');

    // Pavarų dėžė
    $Entry->setFieldValue('pavaru_deze', '');

    // Kėbulas
    $Entry->setFieldValue('kebulas', array('hecbekas', 'sedanas'));

    // Durų skaičius
    $Entry->setFieldValue('duru_skaicius', '');

    // Spalva
    $Entry->setFieldValue('spalva', '');

    // Kiti ypatumai
    $Entry->setFieldValue('kiti_ypatumai', array('naudotas', 'dauztas', 'remontuotinas', 'su_defektais', 'pavaru_dezes_defektas', 'sankabos_defektas', 'variklio_defektas', 'vaziuokles_defektas'));

    // Papildoma informacija
    $Entry->setFieldValue('papildoma_informacija', 'tekstinis laukas');

    // Kiek mokės
    $Entry->setFieldValue('kiek_mokes', 123, 'pln');


// Sugeneruoto dokumento siuntimas į serverį
$Asi->submitExportDocument($ExportDocument);