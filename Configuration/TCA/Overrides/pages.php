<?php

declare(strict_types=1);

$icon = 'apps-pagetree-folder-contains-calendarize';

$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    0 => 'Checkitcalendarize',
    1 => 'checkitcalendarize',
    2 => $icon,
];
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-calendarize'] = $icon;
