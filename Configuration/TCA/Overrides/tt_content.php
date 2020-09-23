<?php

declare(strict_types=1);

use Checkitsedo\Checkitcalendarize\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$hideTable = (bool)ConfigurationUtility::get('disableDefaultEvent');
if (!$hideTable) {
    ExtensionManagementUtility::addToInsertRecords('tx_checkitcalendarize_domain_model_event');
}
