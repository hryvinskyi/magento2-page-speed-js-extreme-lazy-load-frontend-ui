<?php
/**
 * Copyright (c) 2022. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Model;

use Hryvinskyi\PageSpeedApi\Api\Finder\JsInterface as JsFinderInterface;
use Hryvinskyi\PageSpeedApi\Api\Html\ReplaceIntoHtmlInterface;
use Hryvinskyi\PageSpeedApi\Model\ModificationInterface;
use Hryvinskyi\PageSpeedJsExtremeLazyLoad\Api\ConfigInterface;
use Hryvinskyi\PageSpeedJsExtremeLazyLoad\Model\CanScriptLazyLoadingInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class LazyLoadModification implements ModificationInterface
{
    private ConfigInterface $config;
    private JsFinderInterface $jsFinder;
    private ReplaceIntoHtmlInterface $replaceIntoHtml;
    private CanScriptLazyLoadingInterface $canScriptLazyLoading;
    private RequestInterface $request;

    /**
     * @param ConfigInterface $config
     * @param JsFinderInterface $jsFinder
     * @param ReplaceIntoHtmlInterface $replaceIntoHtml
     * @param CanScriptLazyLoadingInterface $canScriptLazyLoading
     * @param RequestInterface $request
     */
    public function __construct(
        ConfigInterface $config,
        JsFinderInterface $jsFinder,
        ReplaceIntoHtmlInterface $replaceIntoHtml,
        CanScriptLazyLoadingInterface $canScriptLazyLoading,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->jsFinder = $jsFinder;
        $this->replaceIntoHtml = $replaceIntoHtml;
        $this->canScriptLazyLoading = $canScriptLazyLoading;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function execute(string &$html): void
    {
        // @TODO Refactor to have a list to chacking if need disable or enable this functionality
        if ($this->config->isEnabled() === false) {
            return;
        }

        if (in_array(trim($this->request->getRequestUri(), '/'), $this->config->getExcludeByUri(), true) === true) {
            return;
        }

        if ($this->config->isApplyForPageTypes() === true
            && in_array($this->request->getFullActionName(), $this->config->getApplyForPageTypes(), true) === false) {
            return;
        }

        if ($this->config->isDisableForPageTypes() === true
            && in_array($this->request->getFullActionName(), $this->config->getDisableForPageTypes(), true) === true) {
            return;
        }

        // Add predefined marker attributes to RequireJS script tags
        $this->addScriptMarkerAttributes($html);

        $tagList = $this->jsFinder->findAll($html);

        $replaceData = [];
        foreach ($tagList as $tag) {
            if ($this->canScriptLazyLoading->execute($tag) === false) {
                continue;
            }

            $replaceAttributes = [
                'type' => 'lazyload',
                'src' => null
            ];

            if (isset($tag->getAttributes()['src'])) {
                $replaceAttributes['data-lazy-source'] = $tag->getAttributes()['src'];
            }

            $replaceData[] = [
                'start' => $tag->getStart(),
                'end' => $tag->getEnd(),
                'content' => $tag->getContentWithUpdatedAttribute($replaceAttributes),
            ];
        }

        foreach (array_reverse($replaceData) as $replaceElementData) {
            $html = $this->replaceIntoHtml->execute(
                $html,
                $replaceElementData['content'],
                $replaceElementData['start'],
                $replaceElementData['end']
            );
        }
    }

    /**
     * Add predefined marker attributes to RequireJS script tags
     *
     * @param string $html
     * @return void
     */
    private function addScriptMarkerAttributes(string &$html): void
    {
        // Add predefined marker attributes to requirejs/require.js script tags
        $html = preg_replace_callback(
            '/<script([^>]*src=["\'][^"\']*requirejs\/require(?:\.min)?\.js[^"\']*["\'][^>]*)>/i',
            function ($matches) {
                $scriptTag = $matches[0];
                // Only add attribute if it doesn't already exist
                if (!str_contains($scriptTag, 'data-execute-marker=')) {
                    $scriptTag = str_replace('<script', '<script data-execute-marker="__lazyLoadMarker_require_js"', $scriptTag);
                }
                return $scriptTag;
            },
            $html
        );

        // Add predefined marker attributes to mage/requirejs/static.js script tags
        $html = preg_replace_callback(
            '/<script([^>]*src=["\'][^"\']*mage\/requirejs\/static(?:\.min)?\.js[^"\']*["\'][^>]*)>/i',
            function ($matches) {
                $scriptTag = $matches[0];
                // Only add attribute if it doesn't already exist
                if (!str_contains($scriptTag, 'data-execute-marker=')) {
                    $scriptTag = str_replace('<script', '<script data-execute-marker="__lazyLoadMarker_static_js"', $scriptTag);
                }
                return $scriptTag;
            },
            $html
        );
        $html = preg_replace_callback(
            '/<script([^>]*src=["\'][^"\']*requirejs-config(?:\.min)?\.js[^"\']*["\'][^>]*)>/i',
            function ($matches) {
                $scriptTag = $matches[0];
                // Only add attribute if it doesn't already exist
                if (!str_contains($scriptTag, 'data-execute-marker=')) {
                    $scriptTag = str_replace('<script', '<script data-execute-marker="__lazyLoadMarker_require_config_js"', $scriptTag);
                }
                return $scriptTag;
            },
            $html
        );
    }
}
