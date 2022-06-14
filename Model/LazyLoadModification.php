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

class LazyLoadModification implements ModificationInterface
{
    private ConfigInterface $config;
    private JsFinderInterface $jsFinder;
    private ReplaceIntoHtmlInterface $replaceIntoHtml;
    private CanScriptLazyLoadingInterface $canScriptLazyLoading;

    /**
     * @param ConfigInterface $config
     * @param JsFinderInterface $jsFinder
     * @param ReplaceIntoHtmlInterface $replaceIntoHtml
     * @param CanScriptLazyLoadingInterface $canScriptLazyLoading
     */
    public function __construct(
        ConfigInterface $config,
        JsFinderInterface $jsFinder,
        ReplaceIntoHtmlInterface $replaceIntoHtml,
        CanScriptLazyLoadingInterface $canScriptLazyLoading
    ) {
        $this->config = $config;
        $this->jsFinder = $jsFinder;
        $this->replaceIntoHtml = $replaceIntoHtml;
        $this->canScriptLazyLoading = $canScriptLazyLoading;
    }

    /**
     * @inheritdoc
     */
    public function execute(string &$html): void
    {
        if ($this->config->isEnabled() === false) {
            return;
        }

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
}
