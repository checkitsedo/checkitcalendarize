<?php

/**
 * AbstractBookingRequest.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\Domain\Model\Request;

use Checkitsedo\Checkitcalendarize\Domain\Model\AbstractModel;

/**
 * AbstractBookingRequest.
 */
abstract class AbstractBookingRequest extends AbstractModel
{
    /**
     * Index.
     *
     * @var \Checkitsedo\Checkitcalendarize\Domain\Model\Index
     */
    protected $index;

    /**
     * Get index.
     *
     * @return \Checkitsedo\Checkitcalendarize\Domain\Model\Index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set index.
     *
     * @param \Checkitsedo\Checkitcalendarize\Domain\Model\Index $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }
}
