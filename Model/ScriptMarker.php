<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Model;

use Hryvinskyi\PageSpeedJsExtremeLazyLoad\Api\ConfigInterface;
use Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Api\ScriptMarkerInterface;

/**
 * Script marker generator service following SOLID principles
 *
 * This service provides script marker functionality for lazy loading,
 * replacing the previous private methods in multiple classes.
 */
class ScriptMarker implements ScriptMarkerInterface
{
    private readonly ConfigInterface $config;

    /**
     * @param ConfigInterface $config Configuration service for lazy loading module
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function generate(string $filePath): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $baseName = basename($filePath, '.js');
        $hash = substr(md5($filePath), 0, 8);
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $baseName);

        return '__lazyLoadMarker_' . $cleanName . '_' . $hash;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * @inheritDoc
     */
    public function generateMarkerScript(string $marker): string
    {
        if (!$this->isEnabled() || empty($marker)) {
            return '';
        }

        return sprintf(
            ';(function(){if(window.%s)return;window.%s=true;})();',
            $marker,
            $marker
        );
    }
}