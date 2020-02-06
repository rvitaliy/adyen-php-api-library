<?php

namespace Adyen\Tests\Util;

use Adyen\Util\Util;

class UtilTest extends \PHPUnit\Framework\TestCase
{
    public function testSha256Invalid()
    {
        $this->expectException('Adyen\AdyenException');
        $this->expectExceptionMessage('Invalid HMAC key: INVALID');
        Util::calculateSha256Signature("INVALID", array('key' => 'value'));
    }

    public function testSha256()
    {
        $signature = Util::calculateSha256Signature("123ABC", array(
            'akey' => 'val\\ue',
            'ckey' => 'val:ue',
            'bkey' => '1'
        ));
        $this->assertEquals("YtbpYcrdbvk0RSVwTwENMzomS0LYtiItMwXhI5tohXs=", $signature);
    }

    public function testFormatAmountThreeDecimals()
    {
        $amount = 15.021;
        $currency = "TND";
        $formattedAmount = Util::formatAmount($amount, $currency);
        $this->assertEquals(15021, $formattedAmount);
    }

    public function testFormatAmountTwoDecimals()
    {
        $amount = 15.02;
        $currency = "EUR";
        $formattedAmount = Util::formatAmount($amount, $currency);
        $this->assertEquals(1502, $formattedAmount);
    }

    public function testFormatAmountZeroDecimals()
    {
        $amount = 15021;
        $currency = "IDR";
        $formattedAmount = Util::formatAmount($amount, $currency);
        $this->assertEquals(15021, $formattedAmount);
    }

    public function testNotificationRequestItemHmac()
    {
        $params = json_decode('{
	            "pspReference": "7914073381342284",
	            "merchantAccountCode": "TestMerchant",
	            "merchantReference": "TestPayment-1407325143704",
	            "amount": {
	                "value": 1130,
	                "currency": "EUR"
	            },
	            "eventCode": "AUTHORISATION",
	            "success": "true"
	        }', true);
        $key = "DFB1EB5485895CFA84146406857104ABB4CBCABDC8AAF103A624C8F6A3EAAB00";
        $hmacCalculation = Util::calculateNotificationHMAC($params, $key);
        $expectedHmac = "Ny9gS2veKo5E4w8/OL6xz1/wvT/hYkAXy1xNc/QvO4I=";
        $this->assertTrue($hmacCalculation != "");
        $this->assertEquals($hmacCalculation, $expectedHmac);
        $params['additionalData'] = array(
            'hmacSignature' => $hmacCalculation
        );
        $hmacValidate = Util::isValidNotificationHMAC($params, $key);
        $this->assertTrue($hmacValidate);
    }

    /**
     * @param string $expectedResult
     * @param string $pspReference
     * @param string $checkoutEnvironment
     * @dataProvider checkoutEnvironmentsProvider
     *
     */
    public function testGetPspReferenceSearchUrl($expectedResult, $pspReference, $checkoutEnvironment)
    {
        $pspSearchUrl = Util::getPspReferenceSearchUrl($pspReference, $checkoutEnvironment);
        $this->assertEquals($expectedResult, $pspSearchUrl);
    }

    public static function checkoutEnvironmentsProvider()
    {
        return array(
            array(
                'https://ca-live.adyen.com/ca/ca/accounts/showTx.shtml?pspReference=7914073381342284',
                '7914073381342284',
                'live'
            ),
            array(
                'https://ca-test.adyen.com/ca/ca/accounts/showTx.shtml?pspReference=883580976999434D',
                '883580976999434D',
                'test'
            )
        );
    }
}
