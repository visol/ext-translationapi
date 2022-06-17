<?php

namespace Sinso\Translationapi\Utility;

/*
 * This file is part of the Sinso/Translationapi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class LocalizationUtility
{
    public static function getLabels(string $extensionKey, string $prefix = '', string $languageKey = 'default'): array
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
     */
    public static function stripPrefix(array $labels, string $prefix): array
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
     */
    public static function expandKeys(array $labels): array
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
     */
    protected static function extractXliffLabels(string $languageFileName, string $languageKey = 'default'): array
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
