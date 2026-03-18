<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\IP;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

class IPTest extends TestCase
{
    // checkCIDRv4

    public function testCheckCIDRv4Match(): void
    {
        $this->assertTrue(IP::checkCIDRv4('192.168.1.100', '192.168.1.0/24'));
    }

    public function testCheckCIDRv4NoMatch(): void
    {
        $this->assertFalse(IP::checkCIDRv4('10.0.0.1', '192.168.1.0/24'));
    }

    public function testCheckCIDRv4HostRoute(): void
    {
        $this->assertTrue(IP::checkCIDRv4('192.168.1.1', '192.168.1.1/32'));
    }

    public function testCheckCIDRv4HostRouteNoMatch(): void
    {
        $this->assertFalse(IP::checkCIDRv4('192.168.1.2', '192.168.1.1/32'));
    }

    // checkCIDRv6

    public function testCheckCIDRv6Match(): void
    {
        $this->assertTrue(IP::checkCIDRv6('2001:db8::1', '2001:db8::/32'));
    }

    public function testCheckCIDRv6NoMatch(): void
    {
        $this->assertFalse(IP::checkCIDRv6('2001:db9::1', '2001:db8::/32'));
    }

    public function testCheckCIDRv6HostRoute(): void
    {
        $this->assertTrue(IP::checkCIDRv6('::1', '::1/128'));
    }

    // checkCIDR

    public function testCheckCIDRRoutesV4(): void
    {
        $this->assertTrue(IP::checkCIDR('10.0.0.1', '10.0.0.0/8'));
    }

    public function testCheckCIDRRoutesV6(): void
    {
        $this->assertTrue(IP::checkCIDR('2001:db8::1', '2001:db8::/32'));
    }

    public function testCheckCIDRMixedVersionThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        IP::checkCIDR('192.168.1.1', '2001:db8::/32');
    }

    // checkCIDRArray

    public function testCheckCIDRArrayMatchesFirst(): void
    {
        $cidrs = ['10.0.0.0/8', '192.168.0.0/16'];
        $this->assertTrue(IP::checkCIDRArray('10.1.2.3', $cidrs));
    }

    public function testCheckCIDRArrayMatchesSecond(): void
    {
        $cidrs = ['10.0.0.0/8', '192.168.0.0/16'];
        $this->assertTrue(IP::checkCIDRArray('192.168.1.1', $cidrs));
    }

    public function testCheckCIDRArrayNoMatch(): void
    {
        $cidrs = ['10.0.0.0/8', '192.168.0.0/16'];
        $this->assertFalse(IP::checkCIDRArray('172.16.0.1', $cidrs));
    }
}
