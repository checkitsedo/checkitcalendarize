<?php

declare(strict_types=1);

use Checkitsedo\Checkitcalendarize\Register;
use Checkitsedo\Checkitcalendarize\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Category\CategoryRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!(bool)ConfigurationUtility::get('disableDefaultEvent')) {
    Register::extTables(Register::getDefaultCalendarizeConfiguration());

    $categoryRegistry = GeneralUtility::makeInstance(CategoryRegistry::class);
    $categoryRegistry->add('checkitcalendarize', 'tx_checkitcalendarize_domain_model_event');
}
