<?php

/**
 * External service.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\Service\TimeTable;

use Checkitsedo\Checkitcalendarize\Domain\Model\Configuration;
use Checkitsedo\Checkitcalendarize\Service\IcsReaderService;
use Checkitsedo\Checkitcalendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * External service.
 */
class ExternalTimeTable extends AbstractTimeTable
{

    /**
     * Modify the given times via the configuration.
     *
     * @param array         $times
     * @param Configuration $configuration
     *
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function handleConfiguration(array &$times, Configuration $configuration)
    {
        $url = $configuration->getExternalIcsUrl();
        if (!GeneralUtility::isValidUrl($url)) {
            HelperUtility::createFlashMessage(
                'Configuration with invalid ICS URL: ' . $url,
                'Index ICS URL',
                FlashMessage::ERROR
            );

            return;
        }

        $icsReaderService = GeneralUtility::makeInstance(IcsReaderService::class);
        $externalTimes = $icsReaderService->getTimes($url);
        foreach ($externalTimes as $time) {
            $time['pid'] = $configuration->getPid();
            $time['state'] = $configuration->getState();
            $times[$this->calculateEntryKey($time)] = $time;
        }
    }
}
