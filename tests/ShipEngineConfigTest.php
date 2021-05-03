<?php declare(strict_types=1);

namespace Service;

use PHPUnit\Framework\TestCase;
use ShipEngine\Message\ValidationException;
use ShipEngine\Model\Address\Address;
use ShipEngine\ShipEngineConfig;
use ShipEngine\ShipEngine;
use ShipEngine\Util\Constants\Endpoints;

/**
 * @covers \ShipEngine\Util\Assert
 * @covers \ShipEngine\ShipEngineConfig
 * @covers \ShipEngine\Message\ShipEngineException
 * @covers \ShipEngine\Message\ValidationException
 * @covers \ShipEngine\ShipEngine
 * @covers \ShipEngine\ShipEngine
 * @covers \ShipEngine\ShipEngineConfig
 */
final class ShipEngineConfigTest extends TestCase
{
    private static ShipEngine $shipengine;

    private static ShipEngineConfig $config;

    private static Address $good_address;

    private static string $test_url;

    public static function setUpBeforeClass(): void
    {
        self::$test_url = Endpoints::TEST_RPC_URL;
        self::$config = new ShipEngineConfig(
            array(
                'api_key' => 'baz',
                'base_url' => self::$test_url,
                'page_size' => 75,
                'retries' => 7,
                'timeout' => new \DateInterval('PT15000S'),
                'events' => null
            )
        );
        self::$shipengine = new ShipEngine(
            array(
                'api_key' => 'baz',
                'base_url' => self::$test_url,
                'page_size' => 75,
                'retries' => 7,
                'timeout' => new \DateInterval('PT15000S'),
                'events' => null
            )
        );
        self::$good_address = new Address(
            array(
                'street' => array('4 Jersey St', 'ste 200'),
                'city_locality' => 'Boston',
                'state_province' => 'MA',
                'postal_code' => '02215',
                'country_code' => 'US',
            )
        );
    }

    public function testNoAPIKey(): void
    {
        try {
            new ShipEngineConfig(
                array(
                    'base_url' => self::$test_url,
                    'page_size' => 75,
                    'retries' => 7,
                    'timeout' => new \DateInterval('PT15000S'),
                    'events' => null
                )
            );
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('field_value_required', $error['error_code']);
            $this->assertEquals(
                'A ShipEngine API key must be specified.',
                $error['message']
            );
        }
    }

    public function testEmptyAPIKey(): void
    {
        try {
            new ShipEngineConfig(
                array(
                    'api_key' => '',
                    'base_url' => self::$test_url,
                    'page_size' => 75,
                    'retries' => 7,
                    'timeout' => new \DateInterval('PT15000S'),
                    'events' => null
                )
            );
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('field_value_required', $error['error_code']);
            $this->assertEquals(
                'A ShipEngine API key must be specified.',
                $error['message']
            );
        }
    }

    public function testInvalidRetries(): void
    {
        try {
            new ShipEngineConfig(
                array(
                    'api_key' => 'baz',
                    'base_url' => self::$test_url,
                    'page_size' => 75,
                    'retries' => -7,
                    'timeout' => new \DateInterval('PT15000S'),
                    'events' => null
                )
            );
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('invalid_field_value', $error['error_code']);
            $this->assertEquals(
                'Retries must be zero or greater.',
                $error['message']
            );
        }
    }

    public function testInvalidTimeout(): void
    {
        try {
            new ShipEngineConfig(
                array(
                    'api_key' => 'baz',
                    'base_url' => self::$test_url,
                    'page_size' => 75,
                    'retries' => 7,
                    'timeout' => new \DateInterval('PT0S'),
                    'events' => null
                )
            );
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('invalid_field_value', $error['error_code']);
            $this->assertEquals(
                'Timeout must be greater than zero.',
                $error['message']
            );
        }
    }

    public function testEmptyAPIKeyInMethodCall()
    {
        try {
            self::$shipengine->validateAddress(self::$good_address, array('api_key' => ''));
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('field_value_required', $error['error_code']);
            $this->assertEquals(
                'A ShipEngine API key must be specified.',
                $error['message']
            );
        }
    }

    public function testInvalidRetriesInMethodCall()
    {
        try {
            self::$shipengine->validateAddress(self::$good_address, array('retries' => -7));
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('invalid_field_value', $error['error_code']);
            $this->assertEquals(
                'Retries must be zero or greater.',
                $error['message']
            );
        }
    }

    public function testInvalidTimeoutInMethodCall()
    {
        try {
            $di = new \DateInterval('PT7S');
            $di->invert = 1;
            self::$shipengine->validateAddress(self::$good_address, array('timeout' => $di));
        } catch (ValidationException $e) {
            $error = $e->jsonSerialize();
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertNull($error['request_id']);
            $this->assertEquals('shipengine', $error['source']);
            $this->assertEquals('validation', $error['type']);
            $this->assertEquals('invalid_field_value', $error['error_code']);
            $this->assertEquals(
                'Timeout must be greater than zero.',
                $error['message']
            );
        }
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(ShipEngineConfig::class, self::$config);
    }

    public function testMergeApiKey()
    {
        $config = new ShipEngineConfig(
            array(
                'api_key' => 'baz',
                'base_url' => self::$test_url,
                'page_size' => 75,
                'retries' => 7,
                'timeout' => new \DateInterval('PT15000S'),
                'events' => null
            )
        );
        $update_config = array('api_key' => 'foo');
        $new_config = $config->merge($update_config);
        $this->assertEquals($update_config['api_key'], $new_config->api_key);
    }

    public function testMergeBaseUrl()
    {
        $config = new ShipEngineConfig(
            array(
                'api_key' => 'baz',
                'base_url' => self::$test_url,
                'page_size' => 75,
                'retries' => 7,
                'timeout' => new \DateInterval('PT15000S'),
                'events' => null
            )
        );
        $update_config = array('base_url' => 'https://google.com/');
        $new_config = $config->merge($update_config);
        $this->assertEquals($update_config['base_url'], $new_config->base_url);
    }

    public function testMergePageSize()
    {
        $config = new ShipEngineConfig(
            array(
                'api_key' => 'baz',
                'base_url' => self::$test_url,
                'page_size' => 75,
                'retries' => 7,
                'timeout' => new \DateInterval('PT15000S'),
                'events' => null
            )
        );
        $update_config = array('page_size' => 50);
        $new_config = $config->merge($update_config);
        $this->assertEquals($update_config['page_size'], $new_config->page_size);
    }

    public function testMergeRetries()
    {
        $config = new ShipEngineConfig(
            array(
                'api_key' => 'baz',
                'base_url' => self::$test_url,
                'page_size' => 75,
                'retries' => 7,
                'timeout' => new \DateInterval('PT15000S'),
                'events' => null
            )
        );
        $update_config = array('retries' => 1);
        $new_config = $config->merge($update_config);
        $this->assertEquals($update_config['retries'], $new_config->retries);
    }

    public function testMergeTimeout()
    {
        $config = new ShipEngineConfig(
            array(
                'api_key' => 'baz',
                'base_url' => self::$test_url,
                'page_size' => 75,
                'retries' => 7,
                'timeout' => new \DateInterval('PT15000S'),
                'events' => null
            )
        );
        $update_config = array('timeout' => new \DateInterval('PT25S'));
        $new_config = $config->merge($update_config);
        $this->assertEquals($update_config['timeout'], $new_config->timeout);
    }
}