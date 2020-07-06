<?php

namespace JamesAusten\PhpZscore\Tests;

use JamesAusten\PhpZscore\Stats;
use PHPUnit\Framework\TestCase;

class StatsTest extends TestCase
{
    /** @test */
    public function can_calculate_mean(): void
    {
        $mean = Stats::mean([2, 4, 4, 4, 5, 5, 7, 9]);
        $this->assertSame(5, $mean);
    }

    /** @test */
    public function can_calculate_stdDev(): void
    {
        $stdDev = Stats::stdDev([2, 4, 4, 4, 5, 5, 7, 9]);
        $this->assertSame(2.0, $stdDev);
    }

    /** @test */
    public function can_calculate_stdDev_from_sample(): void
    {
        $stdDev = Stats::stdDev([2, 4, 4, 4, 5, 5, 7, 9], true);
        $this->assertSame(2.14, round($stdDev, 2));
    }
}
