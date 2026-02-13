<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Plugin;

use Hryvinskyi\PageSpeedJsExtremeLazyLoadFrontendUi\Model\LazyLoadModification;
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
    public function __construct(
        private readonly LazyLoadModification $lazyLoadModification,
        private readonly ResponseInterface $response
    ) {
    }

    /**
     * Apply JS lazy-load modifications to the ESI response body after execution.
     *
     * @param Esi $subject
     * @return void
     */
    public function afterExecute(Esi $subject): void
    {
        $content = (string)$this->response->getContent();
        $this->lazyLoadModification->execute($content);
        $this->response->setContent($content);
    }
}
