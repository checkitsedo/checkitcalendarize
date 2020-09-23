<?php

/**
 * RealURL features.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\Features;

/**
 * RealURL features.
 */
interface SpeakingUrlInterface
{
    /**
     * Get the base for the realurl alias.
     *
     * @return string
     */
    public function getRealUrlAliasBase(): string;
}
