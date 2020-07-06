<?php

namespace JamesAusten\PhpZscore\Tests;

use JamesAusten\PhpZscore\ZScore;
use PHPUnit\Framework\TestCase;

class ZScoreTest extends TestCase
{
    private array $data = [1, 7, 1.1, 1, 0.9, 1, 1, 1.1, 1, 0.9, 1, 1.1, 1, 1, 0.9, 1, 1, 1.1, 1, 1, 1, 1, 1.1, 0.9, 1,
                           1.1, 1, 1, 0.9, 1, 1.1, 1, 1, 1.1, 1, 0.8, 0.9, 1, 1.2, 0.9, 1, 1, 1.1, 1.2, 1, 1.5, 10, 3,
                           2, 5, 3, 2, 1, 1, 1, 0.9, 1, 1, 3, 2.6, 4, 3, 3.2, 2, 1, 1, 0.8, 4, 4, 2, 2.5, 1, 1, 1];

    /** @test */
    public function can_calculate_zscores_30l_5t_0i(): void
    {
        $expected = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 0, 0,
                     0, 1, 1, 1, 1, 0, 0, 0];

        $zScore = new ZScore($this->data, [
            'lag'       => 30,
            'threshold' => 5,
            'influence' => 0,
        ]);

        $result = $zScore->calculate();

//        $this->printDataTable($result);

        $this->assertArrayIsEqual($expected, $result);
    }

    private function assertArrayIsEqual($expected, $actual): void
    {
        // Early exit conditions
        if (!is_array($expected) || !is_array($actual) || ($len = count($expected)) !== count($actual)) {
            $this->assertTrue(false);
        }

        // Check that the expected values match the actual results
        for ($i = 0, $len; $i < $len; $i++) {
            if ($expected[$i] != $actual[$i]) {
                $this->assertTrue(false);
            }
        }

        $this->assertTrue(true);
    }

    private function printDataTable($result): void
    {
        echo "i\tData\tSignal\n----------------------\n";

        for ($i = 0, $iMax = count($result); $i < $iMax; $i++) {
            echo $i . "\t" . $this->data[$i] . "\t" . $result[$i] . "\n";
        }
    }
}
