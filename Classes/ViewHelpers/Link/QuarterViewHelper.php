<?php

/**
 * Link to the quarter.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\ViewHelpers\Link;

use Checkitsedo\Checkitcalendarize\Utility\DateTimeUtility;

/**
 * Link to the quarter.
 */
class QuarterViewHelper extends AbstractLinkViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('date', \DateTime::class, '', true);
        $this->registerArgument('pageUid', 'int', '', false, 0);
    }

    /**
     * Render the link to the given quarter.
     *
     * @return string
     */
    public function render()
    {
        if (!\is_object($this->arguments['date'])) {
            $this->logger->error('Do not call year viewhelper without date');

            return $this->renderChildren();
        }
        $date = $this->arguments['date'];
        $additionalParams = [
            'tx_checkitcalendarize_calendar' => [
                'year' => $date->format('Y'),
                'quarter' => DateTimeUtility::getQuartar($date),
            ],
        ];

        return parent::renderLink($this->getPageUid($this->arguments['pageUid']), $additionalParams);
    }
}
