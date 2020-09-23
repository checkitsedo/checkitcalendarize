<?php

/**
 * Uri to the index.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\ViewHelpers\Uri;

/**
 * Uri to the index.
 */
class IndexViewHelper extends \Checkitsedo\Checkitcalendarize\ViewHelpers\Link\IndexViewHelper
{
    /**
     * Render the uri to the given index.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        return $this->lastHref;
    }
}
