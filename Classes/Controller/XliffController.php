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

use Psr\Http\Message\ResponseInterface;
use Sinso\Translationapi\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * XLIFF controller.
 */
class XliffController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    public function exportAction(): ResponseInterface
    {
        $extensionKey = $this->request->getArgument('extensionKey');
        $languageKey = $this->request->getArgument('languageKey');
        $prefix = $this->request->hasArgument('prefix') ? $this->request->getArgument('prefix') : '';

        $labels = LocalizationUtility::getLabels($extensionKey, $prefix, $languageKey);

        $omitPrefix = GeneralUtility::_GET('omitPrefix');
        if ($omitPrefix === 'yes' && !empty($prefix)) {
            $labels = LocalizationUtility::stripPrefix($labels, $prefix);
        }

        $expand = GeneralUtility::_GET('expand');
        if ($expand === 'yes') {
            $labels = LocalizationUtility::expandKeys($labels);
        }

        return $this->jsonResponse((string) json_encode($labels));
    }

}
