<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use Infrangible\Core\Helper\Stores;
use Infrangible\ETrusted\Model\Cache\Type\ETrusted;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
abstract class Api
    extends Base
{
    /** @var AccessToken */
    protected $accessToken;

    public function __construct(
        ETrusted $cache,
        SerializerInterface $serializer,
        Stores $storeHelper,
        Json $json,
        Arrays $arrays,
        LoggerInterface $logging,
        AccessToken $accessToken)
    {
        parent::__construct($cache, $serializer, $storeHelper, $json, $arrays, $logging);

        $this->accessToken = $accessToken;
    }
}
