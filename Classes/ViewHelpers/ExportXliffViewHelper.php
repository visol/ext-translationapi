<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Sinso\Translationapi\ViewHelpers;

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
     *
     * @param string $extensionKey
     * @param string $prefix
     * @param bool $omitPrefix
     * @param bool $expand
     * @return string
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
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        if (self::isFrontendMode()) {
            $languageKey = $GLOBALS['TSFE']->lang;
        } else {
            $languageKey = $GLOBALS['LANG']->lang;
        }

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
