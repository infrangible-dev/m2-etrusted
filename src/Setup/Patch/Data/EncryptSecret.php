<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Setup\Patch\Data;

use FeWeDev\Base\Variables;
use Infrangible\ETrusted\Model\AccessToken;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Encrypts the secrets of the API client that version 2 saved as plain text.
 */
class EncryptSecret implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EncryptorInterface $encryptor
     * @param Variables $variables
     */
    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected EncryptorInterface $encryptor,
        protected Variables $variables
    ) {
    }

    /**
     * Returns the patches that must run before.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Returns the former names of the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Encrypts the saved secrets that are not encrypted yet.
     *
     * Encrypted values start with the versions of key and cipher, for example "0:3:".
     *
     * @return $this
     */
    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $tableName = $this->moduleDataSetup->getTable('core_config_data');

        $rows = $connection->fetchPairs(
            $connection->select()->from($tableName, ['config_id', 'value'])->where(
                'path = ?',
                AccessToken::CONFIG_SECRET
            )
        );

        foreach ($rows as $configId => $value) {
            $value = $this->variables->stringValueOrDefault($value);

            if ($value === '' || preg_match('/^\d+:\d+:/', $value) === 1) {
                continue;
            }

            $connection->update(
                $tableName,
                ['value' => $this->encryptor->encrypt($value)],
                ['config_id = ?' => $configId]
            );
        }

        return $this;
    }
}
