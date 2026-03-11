<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Container;

if (!Loader::includeModule('crm')) {
    die('CRM module not loaded');
}

$factory = Container::getInstance()->getFactory(1038);

if (!$factory) {
    die('Factory not found');
}

$date = new \Bitrix\Main\Type\DateTime();
$date->add('-14 days');

$items = $factory->getItems([
    'filter' => [
        '<DATE_CREATE' => $date,
        '>=UF_CRM_1111111111' => 12999,
        '@STAGE_ID' => ['NEW', 'IN_PROCESS', 'WON']
    ]
]);

foreach ($items as $item) {

    $data = [
        'ID' => $item->getId(),
        'DATE_CREATE' => $item->getCreatedTime()->toString(),
        'STAGE_ID' => $item->getStageId()
    ];

    file_put_contents(
        $_SERVER["DOCUMENT_ROOT"] . "/logs/info.log",
        print_r($data, true) . PHP_EOL,
        FILE_APPEND
    );

    $item->setStageId('LOSE');
    $factory->getUpdateOperation($item)->launch();
}