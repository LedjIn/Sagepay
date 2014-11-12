<?php

namespace Ledjin\Sagepay\Tests\Api\Signature;

use Ledjin\Sagepay\Api\Signature\Validator;

/**
 * @author a2xchi <a2x-chip@ledj.in>
 * @coversDefaultClass \Ledjin\Sagepay\Api\Signature\Validator
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeInstantiableAndImplementsValidatorInterface()
    {
        $rc = new \ReflectionClass('\Ledjin\Sagepay\Api\Signature\Validator');

        $this->assertTrue($rc->isInstantiable());

        $this->assertTrue(
            $rc->implementsInterface('\Ledjin\Sagepay\Api\Signature\ValidatorInterface')
        );
    }

    /**
     * @test
     */
    public function shouldBeInstantiableWithoutAdditionalParams()
    {
        try {
            $validator = new Validator();
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail();
        }
    }

    public function shouldGiveAbilityToGetDefaultNeededParams()
    {
        $validator = new Validator();
        $this->assertTrue(is_array($available = $validator->getAvailableParams()));
        $this->assertGreaterThan(0, count($available));
    }

    /**
     * @test
     */
    public function shouldDetectTampering()
    {
        $validator = new Validator();
        $validator->setParams(self::getTestParams());
        $this->assertFalse($validator->tamperingDetected(self::getTestSignature()));
    }

    public static function getTestParams()
    {
        $validator = new Validator();
        $params = $validator->getAvailableParams();
        $initialValue = 'teststring';
        $count = 0;

        foreach ($params as $key => $value) {
            $params[$key] = $initialValue . $count;

            $count++;
        }

        return $params;
        $expected = strtoupper(md5(implode("", $params)));
    }

    public static function getTestSignature()
    {
        $params = self::getTestParams();

        return $expected = strtoupper(md5(implode("", $params)));
    }
}
