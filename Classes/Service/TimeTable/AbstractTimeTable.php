<?php

/**
 * Abstract time table service.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\Service\TimeTable;

use Checkitsedo\Checkitcalendarize\Domain\Model\Configuration;
use Checkitsedo\Checkitcalendarize\Domain\Model\ConfigurationGroup;
use Checkitsedo\Checkitcalendarize\Service\AbstractService;
use Checkitsedo\Checkitcalendarize\Service\TimeTableService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract time table service.
 */
abstract class AbstractTimeTable extends AbstractService
{
    /**
     * Time table service.
     *
     * @var \Checkitsedo\Checkitcalendarize\Service\TimeTableService
     */
    protected $timeTableService;

    /**
     * Inject time table service.
     *
     * @param \Checkitsedo\Checkitcalendarize\Service\TimeTableService $timeTableService
     */
    public function injectTimeTableService(TimeTableService $timeTableService)
    {
        $this->timeTableService = $timeTableService;
    }

    /**
     * Modify the given times via the configuration.
     *
     * @param array         $times
     * @param Configuration $configuration
     */
    abstract public function handleConfiguration(array &$times, Configuration $configuration);

    /**
     * Build a single time table by group.
     *
     * @param ConfigurationGroup $group
     *
     * @return array
     */
    protected function buildSingleTimeTableByGroup(ConfigurationGroup $group)
    {
        // @todo move to DI
        $this->timeTableService = GeneralUtility::makeInstance(TimeTableService::class);
        return $this->timeTableService->getTimeTablesByConfigurationIds($group->getConfigurationIds());
    }

    /**
     * Calculate a hash for the key of the given entry.
     * This prevent double entries in the index.
     *
     * @param array $entry
     *
     * @return string
     */
    protected function calculateEntryKey(array $entry)
    {
        // crc32 may be faster but have more collision-potential
        return \hash('md5', \json_encode($entry));
    }
}
