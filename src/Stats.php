<?php

namespace JamesAusten\PhpZscore;

/**
 * Function implemenations for Stats packages
 *
 * @package JamesAusten\PhpZscore
 */
class Stats
{
    /**
     * Calculate the mean for a set of data
     *
     * @param array $data
     *
     * @return float|int
     */
    public static function mean(array $data)
    {
        return array_sum($data) / count($data);
    }

    /**
     * Calculate standard deviation for a set of data
     *
     * @param array $data
     * @param bool  $sample
     *
     * @return float|int
     */
    public static function stdDev(array $data, bool $sample = false)
    {
        $len = $sample ? count($data) - 1 : count($data);
        $mean = self::mean($data);
        $variance = 0.0;

        foreach ($data as $datum) {
            $variance += self::stdSquare($datum, $mean);
        }

        $variance /= $len;

        return sqrt($variance);
    }

    /**
     * Calculate square of value - mean
     *
     * @param $x
     * @param $mean
     *
     * @return float|int
     */
    public static function stdSquare($x, $mean)
    {
        return ($x - $mean) ** 2;
    }
}
