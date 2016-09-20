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

namespace Swisscom\Translationapi\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * XLIFF controller.
 *
 * @category    Controller
 * @package     TYPO3
 * @subpackage  tx_translationapi
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class XliffController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * Export action.
     *
     * @param string $extensionKey
     * @param string $prefix
     * @return void
     */
    public function exportAction($extensionKey, $prefix = '')
    {
        if (ExtensionManagementUtility::isLoaded($extensionKey)) {
            $extensionPath = ExtensionManagementUtility::extPath($extensionKey);
            $languageFileName = $extensionPath . 'Resources/Private/Language/locallang.xlf';
            if (is_file($languageFileName)) {
                $labels = $this->getLabels($languageFileName, $GLOBALS['TSFE']->lang);
                if (!empty($prefix)) {
                    $labels = array_filter($labels, function ($key) use ($prefix) {
                        return GeneralUtility::isFirstPartOfStr($key, $prefix . '.');
                    }, ARRAY_FILTER_USE_KEY);
                }
                header('Content-Type: application/json');
                return json_encode($labels);
            }
        }

        die('Invalid extension: "' . $extensionKey . '"');
    }

    /**
     * Returns the labels of a given XLIFF file.
     *
     * @param string $languageFileName
     * @param string $languageKey
     * @return array
     */
    protected function getLabels($languageFileName, $languageKey = 'default')
    {
        /** @var $languageFactory \TYPO3\CMS\Core\Localization\LocalizationFactory */
        $languageFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LocalizationFactory::class);
        $LOCAL_LANG = $languageFactory->getParsedData($languageFileName, $languageKey);

        // Overload default language with translation
        if ($languageKey !== 'default') {
            ArrayUtility::mergeRecursiveWithOverrule($LOCAL_LANG['default'], $LOCAL_LANG[$languageKey]);
        }

        // Flatten the array
        $labels = array_map(function ($value) {
            return $value[0]['target'] ?: $value[0]['source'];
        }, $LOCAL_LANG['default']);

        return $labels;
    }

}
