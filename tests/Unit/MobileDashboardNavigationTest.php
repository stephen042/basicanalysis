<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MobileDashboardNavigationTest extends TestCase
{
    public function test_mobile_navigation_has_exactly_four_valid_destinations_and_safe_area_spacing(): void
    {
        $layout = File::get(resource_path('views/layouts/dash.blade.php'));
        $start = strpos($layout, '<!-- Four-item mobile navigation');
        $end = strpos($layout, '</nav>', $start);

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        $navigation = substr($layout, $start, $end - $start);
        preg_match_all("/'route' => '([^']+)'/", $navigation, $routes);
        preg_match_all("/'label' => '([^']+)'/", $navigation, $labels);

        $this->assertSame([
            'deposits',
            'copy-trading.index',
            'tradinghistory',
            'compound-investment.create',
        ], $routes[1]);
        $this->assertSame(['Fund', 'Copy Trading', 'History', 'Compounding'], $labels[1]);

        foreach ($routes[1] as $route) {
            $this->assertTrue(Route::has($route), "Mobile destination route [$route] must exist.");
        }

        $this->assertStringContainsString('env(safe-area-inset-bottom', $layout);
        $this->assertStringContainsString('76px + env(safe-area-inset-bottom', $layout);

        $sidebar = File::get(resource_path('views/user/sidebar.blade.php'));
        $this->assertStringContainsString('Swap remains implemented but is hidden from user navigation', $sidebar);
        $this->assertTrue(Route::has('assetbalance'), 'Swap routes remain available for later re-enabling.');
    }
}
