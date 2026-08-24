<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NetworkDiscoveryService;
use App\Livewire\AgencyManager;

class NetworkCalculationTest extends TestCase
{
    public function test_agency_manager_router_ip_mask_calculation()
    {
        $manager = new AgencyManager();

        // 10.0.0.x should calculate to 10.0.0.0/8
        $manager->updatedRouterIp('10.0.0.1');
        $this->assertEquals('10.0.0.0/8', $manager->network_address);

        $manager->updatedRouterIp('10.0.0.254');
        $this->assertEquals('10.0.0.0/8', $manager->network_address);

        // 10.110.13.x should calculate to 10.110.13.0/24
        $manager->updatedRouterIp('10.110.13.1');
        $this->assertEquals('10.110.13.0/24', $manager->network_address);

        // Other IPs should default to /24
        $manager->updatedRouterIp('192.168.1.1');
        $this->assertEquals('192.168.1.0/24', $manager->network_address);
    }

    public function test_network_discovery_service_ip_range_generation()
    {
        $service = new NetworkDiscoveryService();

        // Test /8 mask (should generate up to 2048 IPs)
        $range8 = $service->getIpRange('10.0.0.0/8');
        $this->assertCount(2048, $range8);
        $this->assertEquals('10.0.0.1', $range8[0]);
        $this->assertEquals('10.0.8.0', end($range8));

        // Test /24 mask
        $range24 = $service->getIpRange('10.110.13.0/24');
        $this->assertCount(254, $range24);
        $this->assertEquals('10.110.13.1', $range24[0]);
        $this->assertEquals('10.110.13.254', end($range24));

        // Test IP address without mask (should default to /24)
        $rangeLegacy = $service->getIpRange('10.110.82.0');
        $this->assertCount(254, $rangeLegacy);
        $this->assertEquals('10.110.82.1', $rangeLegacy[0]);
        $this->assertEquals('10.110.82.254', end($rangeLegacy));

        // Test 3-octet base
        $rangeBase = $service->getIpRange('192.168.1');
        $this->assertCount(254, $rangeBase);
        $this->assertEquals('192.168.1.1', $rangeBase[0]);
        $this->assertEquals('192.168.1.254', end($rangeBase));

        // Test /22 mask
        $range22 = $service->getIpRange('10.110.0.0/22');
        $this->assertCount(1022, $range22);
        $this->assertEquals('10.110.0.1', $range22[0]);
        $this->assertEquals('10.110.3.254', end($range22));
    }
}
