<?php
/**
 * Copyright (c) 2022. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Plugin;

use Hryvinskyi\PageSpeedApi\Api\Finder\JsInterface as JsFinderInterface;
use Hryvinskyi\PageSpeedApi\Api\Html\ReplaceIntoHtmlInterface;
use Hryvinskyi\PageSpeedJsExtremeLazyLoad\Api\ConfigInterface;
use Hryvinskyi\PageSpeedJsExtremeLazyLoad\Model\CanScriptLazyLoadingInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\ResponseInterface;
use Magento\PageCache\Controller\Block\Esi;

/**
 * Plugin to apply JS lazy-load modifications to ESI block responses.
 *
 * ESI blocks rendered via the Esi controller bypass the normal Layout::renderResult()
 * pipeline, so the ModifyOutputHtml plugin never fires. This plugin intercepts the
 * ESI controller's execute() method to apply lazy-loading directly to ESI HTML.
 *
 * Only lazy-loading is applied — other pipeline modifications (JS merge, CSS operations)
 * are skipped because they expect full-page HTML structure. Page-level checks (URI exclusion,
 * page type filtering) are also skipped since they reference the original page request,
 * not the ESI subrequest.
 */
class EsiModifyOutputPlugin
{
    /**
     * @param ConfigInterface $config
     * @param JsFinderInterface $jsFinder
     * @param ReplaceIntoHtmlInterface $replaceIntoHtml
     * @param CanScriptLazyLoadingInterface $canScriptLazyLoading
     * @param ResponseInterface $response
     */
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly JsFinderInterface $jsFinder,
        private readonly ReplaceIntoHtmlInterface $replaceIntoHtml,
        private readonly CanScriptLazyLoadingInterface $canScriptLazyLoading,
        private readonly ResponseInterface $response
    ) {
    }

    /**
     * Apply JS lazy-load modifications to the ESI response body after execution.
     *
     * @param Esi $subject
     * @param mixed $result
     *
     * @return mixed
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterExecute(Esi $subject, mixed $result): mixed
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        if (!$this->response instanceof HttpResponse) {
            return $result;
        }

        $content = (string)$this->response->getContent();

        if ($content === '') {
            return $result;
        }

        $this->applyLazyLoad($content);
        $this->response->setContent($content);

        return $result;
    }

    /**
     * Apply lazy-load transformation to script tags in the HTML fragment.
     *
     * @param string $html
     *
     * @return void
     */
    private function applyLazyLoad(string &$html): void
    {
        $tagList = $this->jsFinder->findAll($html);

        $replaceData = [];
        foreach ($tagList as $tag) {
            if ($this->canScriptLazyLoading->execute($tag) === false) {
                continue;
            }

            $replaceAttributes = [
                'type' => 'lazyload',
                'src' => null,
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
