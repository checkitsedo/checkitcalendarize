<?php

/**
 * Time service.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\Service\TimeTable;

use Checkitsedo\Checkitcalendarize\Domain\Model\Configuration;
use Checkitsedo\Checkitcalendarize\Service\RecurrenceService;
use Checkitsedo\Checkitcalendarize\Utility\ConfigurationUtility;
use Checkitsedo\Checkitcalendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Time service.
 */
class TimeTimeTable extends AbstractTimeTable
{
    /**
     * Modify the given times via the configuration.
     *
     * @param array         $times
     * @param Configuration $configuration
     */
    public function handleConfiguration(array &$times, Configuration $configuration)
    {
        $startTime = $configuration->isAllDay() ? null : $configuration->getStartTime();
        $endTime = $configuration->isAllDay() ? null : $configuration->getEndTime();
        $baseEntry = [
            'pid' => $configuration->getPid(),
            'start_date' => $configuration->getStartDate(),
            'end_date' => $configuration->getEndDate() ?: $configuration->getStartDate(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'all_day' => $configuration->isAllDay(),
            'open_end_time' => $configuration->isOpenEndTime(),
            'state' => $configuration->getState(),
        ];
        if (!$this->validateBaseEntry($baseEntry)) {
            return;
        }
        $times[$this->calculateEntryKey($baseEntry)] = $baseEntry;
        $tillDateConfiguration = $this->getTillDateConfiguration($configuration, $baseEntry);
        $this->addFrequencyItems($times, $configuration, $baseEntry, $tillDateConfiguration);
        $this->addRecurrenceItems($times, $configuration, $baseEntry, $tillDateConfiguration);
        $this->respectDynamicEndDates($times, $configuration);
        $this->removeBaseEntryIfNecessary($times, $configuration, $baseEntry, $tillDateConfiguration);
    }

    /**
     * Respect the selection of dynamic enddates.
     *
     * @param array         $times
     * @param Configuration $configuration
     */
    protected function respectDynamicEndDates(array &$times, Configuration $configuration)
    {
        switch ($configuration->getEndDateDynamic()) {
            case Configuration::END_DYNAMIC_1_DAY:
                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('+1 day');
                    }

                    return $entry;
                };
                break;
            case Configuration::END_DYNAMIC_1_WEEK:
                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('+1 week');
                    }

                    return $entry;
                };
                break;
            case Configuration::END_DYNAMIC_END_WEEK:
                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('monday next week');
                        $entry['end_date']->modify('-1 day');
                    }

                    return $entry;
                };
                break;
            case Configuration::END_DYNAMIC_END_MONTH:
                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->modify('last day of this month');
                    }

                    return $entry;
                };
                break;
            case Configuration::END_DYNAMIC_END_YEAR:

                $callback = function ($entry) {
                    if ($entry['start_date'] instanceof \DateTime) {
                        $entry['end_date'] = clone $entry['start_date'];
                        $entry['end_date']->setDate((int)$entry['end_date']->format('Y'), 12, 31);
                    }

                    return $entry;
                };
                break;
        }

        if (!isset($callback)) {
            return;
        }

        $new = [];
        foreach ($times as $hash => $record) {
            $target = $callback($record);
            $new[$this->calculateEntryKey($target)] = $target;
        }
        $times = $new;
    }

    /**
     * Validate the base entry, if there are logica mistakes.
     *
     * @param array $baseEntry
     *
     * @return bool
     */
    protected function validateBaseEntry(array $baseEntry): bool
    {
        $message = null;
        if (!($baseEntry['start_date'] instanceof \DateTimeInterface)) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                'There is no usage for a event configuration without start date?!',
                'No start date?',
                FlashMessage::ERROR
            );
        } elseif ($baseEntry['end_date'] instanceof \DateTimeInterface && $baseEntry['start_date'] > $baseEntry['end_date']) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                LocalizationUtility::translate(
                    'LLL:EXT:checkitcalendarize/Resources/Private/Language/locallang.xlf:wrong.date.message',
                    'checkitcalendarize'
                ),
                LocalizationUtility::translate(
                    'LLL:EXT:checkitcalendarize/Resources/Private/Language/locallang.xlf:wrong.date',
                    'checkitcalendarize'
                ),
                FlashMessage::ERROR
            );
        } elseif ($baseEntry['end_date'] instanceof \DateTimeInterface && !$baseEntry['all_day'] && $baseEntry['start_date']->format('d.m.Y') === $baseEntry['end_date']->format('d.m.Y') && $baseEntry['start_time'] % DateTimeUtility::SECONDS_DAY > $baseEntry['end_time'] % DateTimeUtility::SECONDS_DAY && $baseEntry['end_time'] > 0) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                LocalizationUtility::translate(
                    'LLL:EXT:checkitcalendarize/Resources/Private/Language/locallang.xlf:wrong.time.message',
                    'checkitcalendarize'
                ),
                LocalizationUtility::translate(
                    'LLL:EXT:checkitcalendarize/Resources/Private/Language/locallang.xlf:wrong.time',
                    'checkitcalendarize'
                ),
                FlashMessage::ERROR
            );
        }
        if ($message) {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);

            return false;
        }

        return true;
    }

    /**
     * Add frequency items.
     *
     * @param array         $times
     * @param Configuration $configuration
     * @param array         $baseEntry
     * @param array         $tillDateConfiguration
     */
    protected function addFrequencyItems(array &$times, Configuration $configuration, array $baseEntry, array $tillDateConfiguration)
    {
        $frequencyIncrement = $this->getFrequencyIncrement($configuration);
        if (!$frequencyIncrement) {
            return;
        }
        $amountCounter = $configuration->getCounterAmount();
        $maxLimit = $this->getFrequencyLimitPerItem();
        $lastLoop = $baseEntry;
        for ($i = 0; $i < $maxLimit && (0 === $amountCounter || $i < $amountCounter); ++$i) {
            $loopEntry = $this->createNextLoopEntry($lastLoop, $frequencyIncrement);

            if ($tillDateConfiguration['tillDate'] instanceof \DateTimeInterface && $loopEntry['start_date'] > $tillDateConfiguration['tillDate']) {
                break;
            }

            $lastLoop = $loopEntry;

            if ($tillDateConfiguration['tillDatePast'] instanceof \DateTimeInterface && $loopEntry['end_date'] < $tillDateConfiguration['tillDatePast']) {
                continue;
            }

            $times[$this->calculateEntryKey($loopEntry)] = $loopEntry;
        }
    }

    /**
     * Create the next loop entry.
     *
     * @param array  $loopEntry
     * @param string $modification
     *
     * @return array
     */
    protected function createNextLoopEntry(array $loopEntry, string $modification): array
    {
        /** @var $startDate \DateTime */
        $startDate = clone $loopEntry['start_date'];
        $startDate->modify($modification);
        $loopEntry['start_date'] = $startDate;

        /** @var $endDate \DateTime */
        $endDate = clone $loopEntry['end_date'];
        $endDate->modify($modification);
        $loopEntry['end_date'] = $endDate;

        return $loopEntry;
    }

    /**
     * Get the frequency date increment.
     *
     * @param Configuration $configuration
     *
     * @return string
     */
    protected function getFrequencyIncrement(Configuration $configuration)
    {
        $interval = $configuration->getCounterInterval() <= 1 ? 1 : $configuration->getCounterInterval();
        switch ($configuration->getFrequency()) {
            case Configuration::FREQUENCY_DAILY:
                $intervalValue = '+' . $interval . ' days';
                break;
            case Configuration::FREQUENCY_WEEKLY:
                $intervalValue = '+' . $interval . ' weeks';
                break;
            case Configuration::FREQUENCY_MONTHLY:
                if (Configuration::RECURRENCE_NONE !== $configuration->getRecurrence()) {
                    return false;
                }
                $intervalValue = '+' . $interval . ' months';
                break;
            case Configuration::FREQUENCY_YEARLY:
                if (Configuration::RECURRENCE_NONE !== $configuration->getRecurrence()) {
                    return false;
                }
                $intervalValue = '+' . $interval . ' years';
                break;
            default:
                $intervalValue = false;
        }

        return $intervalValue;
    }

    /**
     * Add recurrence items.
     *
     * @param array         $times
     * @param Configuration $configuration
     * @param array         $baseEntry
     * @param array         $tillDateConfiguration
     */
    protected function addRecurrenceItems(array &$times, Configuration $configuration, array $baseEntry, array $tillDateConfiguration)
    {
        if (Configuration::RECURRENCE_NONE === $configuration->getRecurrence() || Configuration::DAY_NONE === $configuration->getDay()) {
            return;
        }

        $recurrenceService = GeneralUtility::makeInstance(RecurrenceService::class);
        $amountCounter = $configuration->getCounterAmount();
        $maxLimit = $this->getFrequencyLimitPerItem();
        $lastLoop = $baseEntry;
        $intervalCounter = $configuration->getCounterInterval() <= 1 ? 1 : $configuration->getCounterInterval();
        for ($i = 0; $i < $maxLimit && (0 === $amountCounter || $i < $amountCounter); ++$i) {
            $loopEntry = $lastLoop;

            $dateTime = false;
            if (Configuration::FREQUENCY_MONTHLY === $configuration->getFrequency()) {
                $dateTime = $recurrenceService->getRecurrenceForNextMonth(
                    $loopEntry['start_date'],
                    $configuration->getRecurrence(),
                    $configuration->getDay(),
                    $intervalCounter
                );
            } elseif (Configuration::FREQUENCY_YEARLY === $configuration->getFrequency()) {
                $dateTime = $recurrenceService->getRecurrenceForNextYear(
                    $loopEntry['start_date'],
                    $configuration->getRecurrence(),
                    $configuration->getDay(),
                    $intervalCounter
                );
            }
            if (false === $dateTime) {
                break;
            }

            /** @var \DateInterval $interval */
            $interval = $loopEntry['start_date']->diff($dateTime);
            $frequencyIncrement = $interval->format('%R%a days');

            $loopEntry = $this->createNextLoopEntry($loopEntry, $frequencyIncrement);

            if ($tillDateConfiguration['tillDate'] instanceof \DateTimeInterface && $loopEntry['start_date'] > $tillDateConfiguration['tillDate']) {
                break;
            }

            $lastLoop = $loopEntry;

            if ($tillDateConfiguration['tillDatePast'] instanceof \DateTimeInterface && $loopEntry['end_date'] < $tillDateConfiguration['tillDatePast']) {
                continue;
            }

            $times[$this->calculateEntryKey($loopEntry)] = $loopEntry;
        }
    }

    /**
     * Remove the base entry if necessary.
     *
     * @param array         $times
     * @param Configuration $configuration
     * @param array         $baseEntry
     * @param array         $tillDateConfiguration
     */
    protected function removeBaseEntryIfNecessary(array &$times, Configuration $configuration, array $baseEntry, array $tillDateConfiguration)
    {
        $baseEntryKey = $this->calculateEntryKey($baseEntry);
        $tillDate = $configuration->getTillDate();

        if (!isset($times[$baseEntryKey])) {
            return;
        }

        // if the till date is set via the till day feature and if the base entry does not match the till date condition remove it from times
        if (!$tillDate instanceof \DateTimeInterface && $tillDateConfiguration['tillDate'] instanceof \DateTimeInterface && $baseEntry['start_date'] > $tillDateConfiguration['tillDate']) {
            unset($times[$baseEntryKey]);
        } elseif ($tillDateConfiguration['tillDatePast'] instanceof \DateTimeInterface && $baseEntry['end_date'] < $tillDateConfiguration['tillDatePast']) {
            // till date past can only be set via the till date day feature, if the base entry does not match the till date past condition remove it from times
            unset($times[$baseEntryKey]);
        }
    }

    /**
     * @param Configuration $configuration
     * @param array         $baseEntry
     *
     * @return array
     */
    protected function getTillDateConfiguration(Configuration $configuration, array $baseEntry): array
    {
        // get values from item configuration
        $tillDate = $configuration->getTillDate();
        $tillDays = $configuration->getTillDays();
        $tillDaysRelative = $configuration->isTillDaysRelative();
        $tillDaysPast = $configuration->getTillDaysPast();
        $tillDatePast = null;

        // if not set get values from extension configuration
        if (null === $tillDays && null === $tillDaysPast) {
            $tillDays = ConfigurationUtility::get('tillDays');
            $tillDays = MathUtility::canBeInterpretedAsInteger($tillDays) ? (int)$tillDays : null;
            $tillDaysPast = ConfigurationUtility::get('tillDaysPast');
            $tillDaysPast = MathUtility::canBeInterpretedAsInteger($tillDaysPast) ? (int)$tillDaysPast : null;
            $tillDaysRelative = (bool)ConfigurationUtility::get('tillDaysRelative');
        }

        // get base date for till tillDate and tillDatePast calculation
        /** @var \DateTime $tillDaysBaseDate */
        $tillDaysBaseDate = $baseEntry['start_date'];
        if ($tillDaysRelative) {
            $tillDaysBaseDate = DateTimeUtility::resetTime();
        }

        // get actual tillDate
        if (!$tillDate instanceof \DateTimeInterface && (\is_int($tillDays))) {
            --$tillDays; // - 1 day because we already take the current day into account
            $tillDate = clone $tillDaysBaseDate;
            $tillDate->modify('+' . $tillDays . ' day');
        }

        // get actual tillDatePast
        if (\is_int($tillDaysPast)) {
            $tillDatePast = clone $tillDaysBaseDate;
            $tillDatePast->modify('-' . $tillDaysPast . ' day');
        }

        return [
            'tillDate' => $tillDate,
            'tillDatePast' => $tillDatePast,
        ];
    }

    /**
     * Get the limit of the frequency.
     *
     * @return int
     */
    protected function getFrequencyLimitPerItem(): int
    {
        $maxLimit = (int)ConfigurationUtility::get('frequencyLimitPerItem');
        if ($maxLimit <= 0) {
            $maxLimit = 300;
        }

        return $maxLimit;
    }
}
