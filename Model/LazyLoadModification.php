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
