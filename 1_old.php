<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule('crm')) {
    die('CRM module not loaded');
}

$date = new DateTime();
$date->modify('-14 days');

$arFilter = [
    '<DATE_CREATE' => $date->format('Y-m-d H:i:s'),
    '>=UF_CRM_1111111111' => 12999,
    '@STAGE_ID' => ['NEW', 'IN_PROCESS', 'WON']
];

$res = CCrmDeal::GetList(
    [],
    $arFilter,
    false,
    false,
    ['ID','DATE_CREATE','STAGE_ID']
);

while ($item = $res->Fetch()) {

    file_put_contents(
        $_SERVER["DOCUMENT_ROOT"] . "/logs/info.log",
        print_r($item, true) . PHP_EOL,
        FILE_APPEND
    );

    $deal = new CCrmDeal();

    $deal->Update($item['ID'], [
        'STAGE_ID' => 'LOSE'
    ]);
}
