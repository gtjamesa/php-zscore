<?php

namespace JamesAusten\PhpZscore\Tests;

use JamesAusten\PhpZscore\ZScore;
use PHPUnit\Framework\TestCase;

class ZScoreTest extends TestCase
{
    private array $data = [1, 7, 1.1, 1, 0.9, 1, 1, 1.1, 1, 0.9, 1, 1.1, 1, 1, 0.9, 1, 1, 1.1, 1, 1, 1, 1, 1.1, 0.9, 1,
                           1.1, 1, 1, 0.9, 1, 1.1, 1, 1, 1.1, 1, 0.8, 0.9, 1, 1.2, 0.9, 1, 1, 1.1, 1.2, 1, 1.5, 10, 3,
                           2, 5, 3, 2, 1, 1, 1, 0.9, 1, 1, 3, 2.6, 4, 3, 3.2, 2, 1, 1, 0.8, 4, 4, 2, 2.5, 1, 1, 1];

    private array $expected = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                               0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0,
                               1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0];

    /** @test */
    public function can_calculate_zscores_30l_5t_0i(): void
    {
        $zScore = new ZScore($this->data, [
            'lag'       => 30,
            'threshold' => 5,
            'influence' => 0,
        ]);

        $result = $zScore->calculate();

//        $this->printDataTable($result);

        $this->assertSame($this->expected, $result);
    }

    /** @test */
    public function can_add_to_previous_results(): void
    {
        // Supply first 64 points
        $zScore = new ZScore(array_slice($this->data, 0, -10), [
            'lag'       => 30,
            'threshold' => 5,
            'influence' => 0,
        ]);

        // Gather the final 4 points
        $add = array_slice($this->data, -10);

        // Run initial calculation
        $zScore->calculate();

        // Assert that adding the final 10 points provides the same expected signals
        $this->assertSame(0, $zScore->add($add[0]));
        $this->assertSame(0, $zScore->add($add[1]));
        $this->assertSame(0, $zScore->add($add[2]));
        $this->assertSame(1, $zScore->add($add[3]));
        $this->assertSame(1, $zScore->add($add[4]));
        $this->assertSame(1, $zScore->add($add[5]));
        $this->assertSame(1, $zScore->add($add[6]));
        $this->assertSame(0, $zScore->add($add[7]));
        $this->assertSame(0, $zScore->add($add[8]));
        $this->assertSame(0, $zScore->add($add[9]));
    }

    /** @test */
    public function can_reserialize_and_continue_functioning(): void
    {
        // Supply first 64 points
        $zScore = new ZScore(array_slice($this->data, 0, -10), [
            'lag'       => 30,
            'threshold' => 5,
            'influence' => 0,
        ]);

        // Gather the final 4 points
        $add = array_slice($this->data, -10);

        // Run initial calculation
        $zScore->calculate();

        // Assert that adding the final 10 points provides the same expected signals
        $this->assertSame(0, $zScore->add($add[0]));
        $this->assertSame(0, $zScore->add($add[1]));
        $this->assertSame(0, $zScore->add($add[2]));
        $this->assertSame(1, $zScore->add($add[3]));

        $serialized = serialize($zScore);
        unset($zScore); // Clear object from memory
        $zScore = unserialize($serialized);

        $this->assertSame(1, $zScore->add($add[4]));
        $this->assertSame(1, $zScore->add($add[5]));
        $this->assertSame(1, $zScore->add($add[6]));
        $this->assertSame(0, $zScore->add($add[7]));
        $this->assertSame(0, $zScore->add($add[8]));
        $this->assertSame(0, $zScore->add($add[9]));

        $this->assertSame(4003, strlen($serialized));
    }

    /** @test */
    public function can_reserialize_shrinked_object(): void
    {
        // Supply first 64 points
        $zScore = new ZScore(array_slice($this->data, 0, -10), [
            'lag'       => 30,
            'threshold' => 5,
            'influence' => 0,
        ]);

        $add = array_slice($this->data, -10);

        $zScore->calculate();

        $serialized = serialize($zScore->shrink());
        $zScore = unserialize($serialized);

        $this->assertSame(0, $zScore->add($add[0]));

        $this->assertSame(3081, strlen($serialized));
    }

    private function printDataTable($result): void
    {
        echo "i\tData\tSignal\n----------------------\n";

        for ($i = 0, $iMax = count($result); $i < $iMax; $i++) {
            echo $i . "\t" . $this->data[$i] . "\t" . $result[$i] . "\n";
        }

        echo "\n\n\n";
    }
}
