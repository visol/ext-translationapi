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

namespace Sinso\Translationapi\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Localization utility.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_translationapi
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class LocalizationUtility
{

    /**
     * @param string $extensionKey
     * @param string $prefix
     * @param string $languageKey
     * @return array
     * @throws \RuntimeException
     */
    public static function getLabels($extensionKey, $prefix = '', $languageKey = 'default')
    {
        if (ExtensionManagementUtility::isLoaded($extensionKey)) {
            $extensionPath = ExtensionManagementUtility::extPath($extensionKey);
            $languageFileName = $extensionPath . 'Resources/Private/Language/locallang.xlf';
            if (is_file($languageFileName)) {
                $labels = static::extractXliffLabels($languageFileName, $languageKey);
                if (!empty($prefix)) {
                    $labels = array_filter($labels, function ($key) use ($prefix) {
                        return GeneralUtility::isFirstPartOfStr($key, $prefix . '.');
                    }, ARRAY_FILTER_USE_KEY);
                }

                // Sort by key, because it's prettier
                ksort($labels);

                return $labels;
            }
        }

        throw new \RuntimeException('Invalid extension: "' . $extensionKey . '"', 1474533380);
    }

    /**
     * Strips a prefix from the labels.
     *
     * @param array $labels
     * @param string $prefix
     * @return array
     */
    public static function stripPrefix(array $labels, $prefix)
    {
        $ret = [];
        $stripLength = strlen($prefix) + 1;

        foreach ($labels as $key => $value) {
            $ret[substr($key, $stripLength)] = $value;
        }

        return $ret;
    }

    /**
     * Expands the labels' keys.
     *
     * @param array $labels
     * @return array
     */
    public static function expandKeys(array $labels)
    {
        $ret = [];

        foreach ($labels as $key => $value) {
            $subkeys = explode('.', $key);
            $ref = &$ret;
            foreach ($subkeys as $subkey) {
                if (!isset($ref[$subkey])) {
                    $ref[$subkey] = [];
                }
                $ref = &$ref[$subkey];
            }
            $ref = $value;
        }

        return $ret;
    }

    /**
     * Returns the labels of a given XLIFF file.
     *
     * @param string $languageFileName
     * @param string $languageKey
     * @return array
     */
    protected static function extractXliffLabels($languageFileName, $languageKey = 'default')
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
