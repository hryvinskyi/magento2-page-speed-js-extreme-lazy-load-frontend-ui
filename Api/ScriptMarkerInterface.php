<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Api;

/**
 * Interface for generating script markers for lazy loading functionality
 */
interface ScriptMarkerInterface
{
    /**
     * Generate unique script marker based on file path
     *
     * @param string $filePath The file path to generate marker for
     * @return string Generated script marker identifier
     */
    public function generate(string $filePath): string;

    /**
     * Generate marker script content for injection into merged files
     *
     * @param string $marker The marker identifier
     * @return string JavaScript code to inject
     */
    public function generateMarkerScript(string $marker): string;

    /**
     * Check if script marker functionality is enabled
     *
     * @return bool True if enabled, false otherwise
     */
    public function isEnabled(): bool;
}