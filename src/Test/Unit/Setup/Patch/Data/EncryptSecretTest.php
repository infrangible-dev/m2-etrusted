<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Test\Unit\Setup\Patch\Data;

use FeWeDev\Base\Variables;
use Infrangible\ETrusted\Setup\Patch\Data\EncryptSecret;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the secrets saved as plain text are encrypted.
 */
class EncryptSecretTest extends TestCase
{
    /**
     * Plain secrets are encrypted, encrypted and empty ones are left.
     *
     * @return void
     */
    public function testApply(): void
    {
        $updates = [];

        $select = $this->createStub(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();

        $connection = $this->createStub(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->method('fetchPairs')->willReturn(
            ['1' => 'plain-secret', '2' => '0:3:already-encrypted', '3' => '', '4' => null]
        );
        $connection->method('update')->willReturnCallback(
            function (string $table, array $bind, array $where) use (&$updates): int {
                $updates[] = [$table, $bind, $where];

                return 1;
            }
        );

        $setup = $this->createStub(ModuleDataSetupInterface::class);
        $setup->method('getConnection')->willReturn($connection);
        $setup->method('getTable')->willReturnArgument(0);

        $encryptor = $this->createStub(EncryptorInterface::class);
        $encryptor->method('encrypt')->willReturnCallback(
            fn(string $value): string => sprintf('0:3:encrypted(%s)', $value)
        );

        (new EncryptSecret($setup, $encryptor, new Variables()))->apply();

        $this->assertSame(
            [['core_config_data', ['value' => '0:3:encrypted(plain-secret)'], ['config_id = ?' => 1]]],
            $updates
        );
    }
}
