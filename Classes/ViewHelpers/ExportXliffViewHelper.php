<?php

namespace Sinso\Translationapi\ViewHelpers;

/*
 * This file is part of the Sinso/Translationapi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Sinso\Translationapi\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Export as JSON.
 */
class ExportXliffViewHelper extends AbstractViewHelper
{
    /**
     * Exports labels from XLIFF as JSON.
     */
    public function render(string $extensionKey, string $prefix = '', bool $omitPrefix = false, bool $expand = false): string
    {
        return static::renderStatic(
            [
                'extensionKey' => $extensionKey,
                'prefix' => $prefix,
                'omitPrefix' => $omitPrefix,
                'expand' => $expand,
            ],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * Static rendering.
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $languageKey = self::isFrontendMode() ? $GLOBALS['TSFE']->lang : $GLOBALS['LANG']->lang;

        $labels = LocalizationUtility::getLabels($arguments['extensionKey'], $arguments['prefix'], $languageKey);

        if ($arguments['omitPrefix'] && !empty($arguments['prefix'])) {
            $labels = LocalizationUtility::stripPrefix($labels, $arguments['prefix']);
        }

        if ($arguments['expand']) {
            $labels = LocalizationUtility::expandKeys($labels);
        }

        return json_encode($labels);
    }

    /**
     * Returns whether the current mode is Frontend
     */
    protected static function isFrontendMode(): bool
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }

}
