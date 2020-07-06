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

    public function calculate(): array
    {
        $len = count($this->data);
        $lagData = array_slice($this->data, 0, $this->lag);

        $this->signals = [];
        $this->avgFilter = [];
        $this->stdFilter = [];
        $this->filteredY = [];

        $this->avgFilter[$this->lag - 1] = Stats::mean($lagData);
        $this->stdFilter[$this->lag - 1] = Stats::stdDev($lagData, true);

        for ($i = 0; $i < $len; $i++) {
            $this->filteredY[$i] = $this->data[$i];
            $this->signals[$i] = 0;
        }

        for ($i = $this->lag; $i < $len; $i++) {

            if (abs($this->data[$i] - $this->avgFilter[$i - 1]) > $this->threshold * $this->stdFilter[$i - 1]) {
                if ($this->data[$i] > $this->avgFilter[$i - 1]) {
                    $this->signals[$i] = 1;
                } else {
                    $this->signals[$i] = -1;
                }

                $this->filteredY[$i] = $this->influence * $this->data[$i] + (1 - $this->influence) * $this->filteredY[$i - 1];
            } else {
                $this->signals[$i] = 0;
                $this->filteredY[$i] = $this->data[$i];
            }

            $lagData = array_slice($this->filteredY, $i - $this->lag, $this->lag);

            $this->avgFilter[$i] = Stats::mean($lagData);
            $this->stdFilter[$i] = Stats::stdDev($lagData, true);
        }

        return $this->signals;
    }

    private function mergeOptions(array $options): void
    {
        $options = array_merge($this->defaultOptions, $options);

        $this->lag = (int)$options['lag'];
        $this->threshold = (float)$options['threshold'];
        $this->influence = (float)$options['influence'];
    }
}
