<?php

/**
 * General ext_tables file.
 */
if (!\defined('TYPO3_MODE')) {
    die('Access denied.');
}

\HDNET\Autoloader\Loader::extTables('Checkitsedo', 'checkitcalendarize', \Checkitsedo\Checkitcalendarize\Register::getDefaultAutoloader());
\Checkitsedo\Checkitcalendarize\Register::extTables(\Checkitsedo\Checkitcalendarize\Register::getGroupCalendarizeConfiguration());

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_checkitcalendarize_domain_model_configuration,tx_checkitcalendarize_domain_model_index');

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
    $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',checkitcalendarize';
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'checkitcalendarize',
    'Calendar',
    \Checkitsedo\Checkitcalendarize\Utility\TranslateUtility::getLll('pluginName')
);

// module icon
$folderIcon = 'EXT:checkitcalendarize/Resources/Public/Icons/apps-pagetree-folder-contains-calendarize.svg';
/** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'apps-pagetree-folder-contains-calendarize',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => $folderIcon]
);

$categoryRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Category\CategoryRegistry::class);
$categoryRegistry->add('checkitcalendarize', 'tx_checkitcalendarize_domain_model_pluginconfiguration');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
	TCEMAIN {
		linkHandler {
			tx_checkitcalendarize_domain_model_event {
				handler = TYPO3\CMS\Recordlist\LinkHandler\RecordLinkHandler
				label = Events
				configuration {
					table = tx_checkitcalendarize_domain_model_event
				}
				scanAfter = page
			}
		}
	}
');

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Checkitsedo.checkitcalendarize',
        'web',
        'checkitcalendarize',
        '',
        ['Backend' => 'list,option'],
        [
            // Additional configuration
            'access' => 'user, group',
            'icon' => 'EXT:checkitcalendarize/Resources/Public/Icons/Extension.svg',
            'iconIdentifier' => 'module-my_redirects',
            'labels' => 'LLL:EXT:checkitcalendarize/Resources/Private/Language/locallang_mod.xlf',
            'navigationComponentId' => ''
        ]
    );



$iconPath = \TYPO3\CMS\Core\Utility\PathUtility::getAbsoluteWebPath(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:checkitcalendarize/Resources/Public/Icons/'));
$iconRegistry->registerIcon(
    'apps-calendarize-type-' . \Checkitsedo\Checkitcalendarize\Domain\Model\Configuration::TYPE_TIME,
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => $iconPath . 'Configuration.png']
);
$iconRegistry->registerIcon(
    'apps-calendarize-type-' . \Checkitsedo\Checkitcalendarize\Domain\Model\Configuration::TYPE_GROUP,
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => $iconPath . 'ConfigurationGroupType.png']
);
$iconRegistry->registerIcon(
    'apps-calendarize-type-' . \Checkitsedo\Checkitcalendarize\Domain\Model\Configuration::TYPE_EXTERNAL,
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => $iconPath . 'ConfigurationExternal.png']
);

// Exclude "pages" and obsolete fields
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['checkitcalendarize_calendar'] = 'recursive,select_key,pages';
