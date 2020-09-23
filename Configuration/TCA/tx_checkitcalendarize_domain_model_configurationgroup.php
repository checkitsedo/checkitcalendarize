<?php

declare(strict_types=1);

use HDNET\Autoloader\Utility\ArrayUtility;
use HDNET\Autoloader\Utility\ModelUtility;
use Checkitsedo\Checkitcalendarize\Domain\Model\ConfigurationGroup;

$base = ModelUtility::getTcaInformation(ConfigurationGroup::class);

$custom = [
    'ctrl' => [
        'searchFields' => 'uid,title',
    ],
    'types' => [
        '1' => [
            'showitem' => \str_replace('configurations,', 'configurations,checkitcalendarize_info,', $base['types']['1']['showitem']),
        ],
    ],
    'columns' => [
        'title' => [
            'config' => [
                'eval' => 'trim,required',
            ],
        ],
        'configurations' => [
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_checkitcalendarize_domain_model_configuration',
                'minitems' => 1,
                'maxitems' => 100,
            ],
        ],
        'checkitcalendarize_info' => [
            'label' => 'LLL:EXT:checkitcalendarize/Resources/Private/Language/locallang.xlf:tca.information',
            'config' => [
                'type' => 'user',
                'renderType' => 'calendarizeInfoElement',
                'parameters' => [
                    'items' => 10,
                ],
            ],
        ],
    ],
];

return ArrayUtility::mergeRecursiveDistinct($base, $custom);
