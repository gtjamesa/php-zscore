<?php

namespace JamesAusten\PhpZscore;

/**
 * Implementation of Jean-Paul's ZSCORE algorithm
 *
 * @see     https://stackoverflow.com/a/22640362/6029703
 * @package JamesAusten\PhpZscore
 */
class ZScore
{
    private array $data;
    private int $len;
    private int $lag;
    private float $threshold;
    private float $influence;

    private array $signals;
    private array $avgFilter;
    private array $stdFilter;
    private array $filteredY;

    private array $defaultOptions = [
        'lag'       => 5,
        'threshold' => 3.5,
        'influence' => 0.5,
    ];

    public function __construct($data, array $options = [])
    {
        $this->data = $data;

        $this->mergeOptions($options);
    }

    /**
     * Calculate all signals for a given dataset
     *
     * @return array
     */
    public function calculate(): array
    {
        $this->len = count($this->data);
        $lagData = array_slice($this->data, 0, $this->lag);

        $this->signals = [];
        $this->avgFilter = [];
        $this->stdFilter = [];
        $this->filteredY = [];

        $this->avgFilter[$this->lag - 1] = Stats::mean($lagData);
        $this->stdFilter[$this->lag - 1] = Stats::stdDev($lagData, true);

        for ($i = 0; $i < $this->len; $i++) {
            $this->filteredY[$i] = $this->data[$i];
            $this->signals[$i] = 0;
        }

        for ($i = $this->lag; $i < $this->len; $i++) {
            $this->calcSignal($this->data[$i], $i);
        }

        return $this->signals;
    }

    /**
     * Calculate signal
     *
     * @param $point
     * @param $i
     *
     * @return int
     */
    private function calcSignal($point, $i): int
    {
        if (abs($point - $this->avgFilter[$i - 1]) > $this->threshold * $this->stdFilter[$i - 1]) {
            if ($point > $this->avgFilter[$i - 1]) {
                $this->signals[$i] = 1;
            } else {
                $this->signals[$i] = -1;
            }

            $this->filteredY[$i] = $this->influence * $point + (1 - $this->influence) * $this->filteredY[$i - 1];
        } else {
            $this->signals[$i] = 0;
            $this->filteredY[$i] = $point;
        }

        $lagData = array_slice($this->filteredY, $i - $this->lag, $this->lag);

        $this->avgFilter[$i] = Stats::mean($lagData);
        $this->stdFilter[$i] = Stats::stdDev($lagData, true);

        return $this->signals[$i];
    }

    /**
     * Calculate and return signal for incoming point
     *
     * @param $point
     *
     * @return int
     */
    public function add($point): int
    {
        $this->data[] = $point;
        return $this->calcSignal($point, $this->len++);
    }

    /**
     * Disables serialising the original dataset
     *
     * @return \JamesAusten\PhpZscore\ZScore
     */
    public function shrink(): ZScore
    {
        $this->shrink = true;
        return $this;
    }

    private function mergeOptions(array $options): void
    {
        $options = array_merge($this->defaultOptions, $options);

        $this->lag = (int)$options['lag'];
        $this->threshold = (float)$options['threshold'];
        $this->influence = (float)$options['influence'];
    }

    /**
     * Serialize object, optionally ignoring original supplied data as it is redundant for future calculations
     * To reduce the size of the serialized, call the `shrink` method
     *      serialize($zScore->shrink())
     *
     * @return string
     */
    public function serialize()
    {
        $data = [
            'len'       => $this->len,
            'lag'       => $this->lag,
            'threshold' => $this->threshold,
            'influence' => $this->influence,
            'signals'   => $this->signals,
            'avgFilter' => $this->avgFilter,
            'stdFilter' => $this->stdFilter,
            'filteredY' => $this->filteredY,
        ];

        if (!$this->shrink) {
            $data['data'] = $this->data;
        }

        return serialize($data);
    }

    /** @noinspection UnserializeExploitsInspection */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->data = $data['data'] ?? [];
        $this->len = $data['len'];
        $this->lag = $data['lag'];
        $this->threshold = $data['threshold'];
        $this->influence = $data['influence'];
        $this->signals = $data['signals'];
        $this->avgFilter = $data['avgFilter'];
        $this->stdFilter = $data['stdFilter'];
        $this->filteredY = $data['filteredY'];
    }
}
