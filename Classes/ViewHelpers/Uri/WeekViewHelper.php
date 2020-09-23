<?php

/**
 * Uri to the week.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\ViewHelpers\Uri;

/**
 * Uri to the week.
 */
class WeekViewHelper extends \Checkitsedo\Checkitcalendarize\ViewHelpers\Link\WeekViewHelper
{
    /**
     * Render the uri to the given week.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
