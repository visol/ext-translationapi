<?php

namespace Sinso\Translationapi\Controller;

/*
 * This file is part of the Sinso/Translationapi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use Sinso\Translationapi\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class XliffController extends ActionController
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

        return $this->jsonResponse((string)json_encode($labels));
    }

}
