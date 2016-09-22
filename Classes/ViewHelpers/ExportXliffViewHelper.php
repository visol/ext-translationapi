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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use Sinso\Translationapi\Utility\LocalizationUtility;

/**
 * Export as JSON.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  tx_translationapi
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ExportXliffViewHelper extends AbstractViewHelper implements CompilableInterface
{

    /**
     * Exports labels from XLIFF as JSON.
     *
     * @param string $extensionKey
     * @param string $prefix
     * @return string
     */
    public function render($extensionKey, $prefix = '')
    {
        return static::renderStatic(
            [
                'extensionKey' => $extensionKey,
                'prefix' => $prefix,
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
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
        if (TYPO3_MODE === 'FE') {
            $languageKey = $GLOBALS['TSFE']->lang;
        } else {
            $languageKey = $GLOBALS['LANG']->lang;
        }

        $labels = LocalizationUtility::getLabels($arguments['extensionKey'], $arguments['prefix'], $languageKey);
        return json_encode($labels);
    }

}
