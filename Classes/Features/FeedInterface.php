<?php

/**
 * Feed interface.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\Features;

/**
 * Feed interface.
 */
interface FeedInterface
{
    /**
     * Get the feed title.
     *
     * @return string
     */
    public function getFeedTitle(): string;

    /**
     * Get the feed abstract.
     *
     * @return string
     */
    public function getFeedAbstract(): string;

    /**
     * Get the feed content.
     *
     * @return string
     */
    public function getFeedContent(): string;

    /**
     * Get the feed location.
     *
     * @return string
     */
    public function getFeedLocation(): string;
}
