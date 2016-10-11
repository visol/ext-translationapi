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

namespace Sinso\Translationapi\Controller;

use Sinso\Translationapi\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $labels = LocalizationUtility::getLabels($extensionKey, $prefix, $GLOBALS['TSFE']->lang);

        $omitPrefix = GeneralUtility::_GET('omitPrefix');
        if ($omitPrefix === 'yes' && !empty($prefix)) {
            $ret = [];
            $stripLength = strlen($prefix) + 1;
            foreach ($labels as $key => $value) {
                $ret[substr($key, $stripLength)] = $value;
            }
            $labels = $ret;
        }

        $expand = GeneralUtility::_GET('expand');
        if ($expand === 'yes') {
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
            $labels = $ret;
        }

        header('Content-Type: application/json');
        return json_encode($labels);
    }

}
