<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Model\Cache\Type;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

/**
 * The cache type "etrusted" (System > Cache Management) of the data from eTrusted.
 */
class ETrusted extends TagScope
{
    /** Identifier of the cache type. */
    public const TYPE_IDENTIFIER = 'etrusted';

    /** Tag of all entries of the cache type. */
    public const CACHE_TAG = 'ETRUSTED';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
