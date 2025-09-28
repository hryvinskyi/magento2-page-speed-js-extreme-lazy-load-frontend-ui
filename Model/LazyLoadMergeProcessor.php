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
use Hryvinskyi\PageSpeedJsMergeFrontendUi\Api\Data\ProcessContentDataInterface;
use Hryvinskyi\PageSpeedJsMergeFrontendUi\Api\MergeProcessorInterface;

/**
 * Lazy load merge processor that adds script markers to merged files
 *
 * This processor extends the merge functionality to add lazy loading markers
 */
class LazyLoadMergeProcessor implements MergeProcessorInterface
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
     * @inheritDoc
     */
    public function processContent(ProcessContentDataInterface $data): string
    {
        $processedContent = $data->getContent();

        // Add marker script to the last file if lazy loading is enabled
        if ($data->isLastFile() && $this->config->isEnabled()) {
            $marker = $this->scriptMarker->generate($data->getMergedFilePath());
            $markerScript = $this->scriptMarker->generateMarkerScript($marker);
            $processedContent .= $markerScript;
        }

        return $processedContent;
    }

    /**
     * @inheritDoc
     */
    public function processAttributes(array $attributes, string $mergedFilePath): array
    {
        // Add marker attribute if lazy loading is enabled
        if ($this->config->isEnabled()) {
            $marker = $this->scriptMarker->generate($mergedFilePath);
            $attributes['data-execute-marker'] = $marker;
        }

        return $attributes;
    }
}