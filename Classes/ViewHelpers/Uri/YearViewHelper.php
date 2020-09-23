<?php

/**
 * Uri to the year.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\ViewHelpers\Uri;

/**
 * Uri to the year.
 */
class YearViewHelper extends \Checkitsedo\Checkitcalendarize\ViewHelpers\Link\YearViewHelper
{
    /**
     * Render the uri to the given year.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
