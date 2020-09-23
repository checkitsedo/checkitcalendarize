<?php
declare(strict_types=1);

use Checkitsedo\Checkitcalendarize\Command\CleanupCommandController;
use Checkitsedo\Checkitcalendarize\Command\ImportCommandController;
use Checkitsedo\Checkitcalendarize\Command\ReindexCommandController;

return [
    'checkitcalendarize:cleanup' => [
        'class' => CleanupCommandController::class,
        'schedulable' => true
    ],
    'checkitcalendarize:import' => [
        'class' => ImportCommandController::class,
        'schedulable' => true
    ],
    'checkitcalendarize:reindex' => [
        'class' => ReindexCommandController::class,
        'schedulable' => true
    ],
];
