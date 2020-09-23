<?php

/**
 * Realurl configuration.
 */
declare(strict_types=1);

namespace Checkitsedo\Checkitcalendarize\Hooks;

use HDNET\Autoloader\Annotation\Hook;
use Checkitsedo\Checkitcalendarize\Service\Url\RealUrl;

/**
 * Realurl configuration.
 */
class RealurlConfiguration extends AbstractHook
{
    /**
     * Add the realurl configuration.
     *
     * @param $params
     * @param $pObj
     *
     * @return array
     * @Hook("TYPO3_CONF_VARS|SC_OPTIONS|ext/realurl/class.tx_realurl_autoconfgen.php|extensionConfiguration")
     */
    public function addCheckitcalendarizeConfiguration($params, &$pObj)
    {
        return \array_merge_recursive($params['config'], [
            'postVarSets' => [
                '_DEFAULT' => [
                    'event' => [
                        [
                            'GETvar' => 'tx_checkitcalendarize_calendar[index]',
                            'userFunc' => RealUrl::class . '->convert',
                        ],
                    ],
                    'event-page' => [
                        [
                            'GETvar' => 'tx_checkitcalendarize_calendar[@widget_0][currentPage]',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
