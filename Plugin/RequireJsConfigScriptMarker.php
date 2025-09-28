<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Plugin;

use Hryvinskyi\PageSpeedJsExtremeLazyLoad\Api\ConfigInterface;
use Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Api\ScriptMarkerInterface;
use Magento\Framework\RequireJs\Config;

/**
 * Plugin to add script markers to RequireJS min resolver code
 */
class RequireJsConfigScriptMarker
{
    private readonly ConfigInterface $config;
    private readonly ScriptMarkerInterface $scriptMarker;

    /**
     * @param ConfigInterface $config Configuration for lazy loading
     * @param ScriptMarkerInterface $scriptMarker Script marker generator
     */
    public function __construct(
        ConfigInterface $config,
        ScriptMarkerInterface $scriptMarker
    ) {
        $this->config = $config;
        $this->scriptMarker = $scriptMarker;
    }

    /**
     * Add script marker to RequireJS main config
     *
     * @param Config $subject
     * @param string $result
     * @return string
     */
    public function afterGetConfig(Config $subject, string $result): string
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        // Add predefined script marker for requirejs/require.js
        $requireMarkerScript = $this->scriptMarker->generateMarkerScript('__lazyLoadMarker_require_config_js');

        return $result . $requireMarkerScript;
    }
}