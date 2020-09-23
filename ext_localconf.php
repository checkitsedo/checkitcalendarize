<?php

/**
 * General ext_localconf file.
 */
if (!\defined('TYPO3_MODE')) {
    die('Access denied.');
}

\HDNET\Autoloader\Loader::extLocalconf('Checkitsedo', 'checkitcalendarize', \Checkitsedo\Checkitcalendarize\Register::getDefaultAutoloader());
\Checkitsedo\Checkitcalendarize\Register::extLocalconf(\Checkitsedo\Checkitcalendarize\Register::getGroupCalendarizeConfiguration());

if (!(bool) \Checkitsedo\Checkitcalendarize\Utility\ConfigurationUtility::get('disableDefaultEvent')) {
    \Checkitsedo\Checkitcalendarize\Register::extLocalconf(\Checkitsedo\Checkitcalendarize\Register::getDefaultCalendarizeConfiguration());
    /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \Checkitsedo\Checkitcalendarize\Command\ImportCommandController::class,
        'importCommand',
        \Checkitsedo\Checkitcalendarize\Slots\EventImport::class,
        'importCommand'
    );

    $signalSlotDispatcher->connect(
       \Checkitsedo\Checkitcalendarize\Controller\BookingController::class,
        'bookingAction',
        \Checkitsedo\Checkitcalendarize\Slots\BookingCountries::class,
        'bookingSlot'
    );
    $signalSlotDispatcher->connect(
        \Checkitsedo\Checkitcalendarize\Controller\BookingController::class,
        'sendAction',
        \Checkitsedo\Checkitcalendarize\Slots\BookingCountries::class,
        'sendSlot'
    );
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Checkitsedo.checkitcalendarize',
    'Calendar',
    [
        'Calendar' => 'list,past,latest,year,quater,month,week,day,detail,search,result,single,shortcut',
        'Booking' => 'booking,send',
    ],
    [
        'Calendar' => 'search,result',
        'Booking' => 'booking,send',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\Checkitsedo\Checkitcalendarize\Updates\CalMigrationUpdate::class] = \Checkitsedo\Checkitcalendarize\Updates\CalMigrationUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\Checkitsedo\Checkitcalendarize\Updates\NewIncludeExcludeStructureUpdate::class] = \Checkitsedo\Checkitcalendarize\Updates\NewIncludeExcludeStructureUpdate::class;

$GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder']['record'] = \Checkitsedo\Checkitcalendarize\Typolink\DatabaseRecordLinkBuilder::class;


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:checkitcalendarize/Configuration/TsConfig/ContentElementWizard.txt">');

$icons = [
    'ext-calendarize-wizard-icon' => 'Resources/Public/Icons/Extension.svg',
];
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($icons as $identifier => $path) {
    $iconRegistry->registerIcon(
        $identifier,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:checkitcalendarize/' . $path]
    );
}

if (class_exists(\TYPO3\CMS\Core\Routing\Aspect\PersistedPatternMapper::class)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['EventMapper'] = \Checkitsedo\Checkitcalendarize\Routing\Aspect\EventMapper::class;
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1591803668] = [
    'nodeName' => 'calendarizeInfoElement',
    'priority' => 40,
    'class' => \Checkitsedo\Checkitcalendarize\Form\Element\CalendarizeInfoElement::class,
];
