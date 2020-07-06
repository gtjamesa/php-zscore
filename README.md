# PHP smoothed peak detection algorithm (ZScore)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gtjamesa/php-zscore.svg?style=flat-square)](https://packagist.org/packages/gtjamesa/php-zscore)
[![Total Downloads](https://img.shields.io/packagist/dt/gtjamesa/php-zscore.svg?style=flat-square)](https://packagist.org/packages/gtjamesa/php-zscore)

This package is the implementation of the "[Robust peak detection algorithm (using z-scores)](https://stackoverflow.com/a/22640362/6029703)" algorithm by [Jean-Paul](https://www.linkedin.com/in/jpgvb).

## Installation

You can install the package via composer:

```bash
composer require gtjamesa/php-zscore
```

## Usage

Parameters to configure the algorithm can be set using a fluent interface, or optionally be specified as the second parameter when creating the object.

``` php
// Create object using fluent interface
$zScore = (new ZScore($this->data))->lag(30)
            ->threshold(5)
            ->influence(0);

// Create object using options as an array
$zScore = new ZScore($data, [
    'lag'       => 30,
    'threshold' => 5,
    'influence' => 0,
]);

// Calculate peak signals for the given dataset
// Returns [0, 1, 0, 0, ..., -1, 0, 0, 1]
$results = $zScore->calculate();
```

The algorithm will iterate the entire supplied dataset on first calculation. This will be inefficient to run on a real-time stream of incoming data, as it will recalculate every signal for the dataset. 

Additional signals for incoming datapoints can be added to the dataset after it's creation by calling the `add()` method:

```php
$signal = $zScore->add(155); // returns -1, 0 or 1
```

#### Saving/loading

The `ZScore` class can be serialized/unserialized so that the previously calculated data may be saved to disk or cache, to be reloaded at a later time. You may optionally call the `shrink()` method before serialization to ignore the dataset, as it is unnecessary for future signal calculations.

```php
$serialized = serialize($zScore); // 3766 bytes
$serializedSmall = serialize($zScore->shrink()); // 3081 bytes
```

#### Algorithm configuration parameters

***`lag`***: the lag parameter determines how much your data will be smoothed and how adaptive the algorithm is to changes in the long-term average of the data. The more [stationary](https://en.wikipedia.org/wiki/Stationary_process) your data is, the more lags you should include (this should improve the robustness of the algorithm). If your data contains time-varying trends, you should consider how quickly you want the algorithm to adapt to these trends. i.e., if you put `lag` at 10, it takes 10 'periods' before the algorithm's threshold is adjusted to any systematic changes in the long-term average. So choose the `lag` parameter based on the trending behaviour of your data and how adaptive you want the algorithm to be.

***`influence`***: this parameter determines the influence of signals on the algorithm's detection threshold. If put at 0, signals have no influence on the threshold, such that future signals are detected based on a threshold that is calculated with a mean and standard deviation that is not influenced by past signals. Another way to think about this is that if you put the influence at 0, you implicitly assume stationarity (i.e. no matter how many signals there are, the time series always returns to the same average over the long term). If this is not the case, you should put the influence parameter somewhere between 0 and 1, depending on the extent to which signals can systematically influence the time-varying trend of the data. e.g., if signals lead to a [structural break](https://en.wikipedia.org/wiki/Structural_break) of the long-term average of the time series, the influence parameter should be put high (close to 1) so the threshold can adjust to these changes quickly.

***`threshold`***: the threshold parameter is the number of standard deviations from the moving mean above which the algorithm will classify a new datapoint as being a signal. For example, if a new datapoint is 4.0 standard deviations above the moving mean and the threshold parameter is set as 3.5, the algorithm will identify the datapoint as a signal. This parameter should be set based on how many signals you expect. For example, if your data is normally distributed, a threshold (or: z-score) of 3.5 corresponds to a signalling probability of 0.00047 (from [this table](https://imgur.com/a/UJlXNJo)), which implies that you expect a signal once every 2128 datapoints (1/0.00047). The threshold therefore directly influences how sensitive the algorithm is and thereby also how often the algorithm signals. Examine your own data and determine a sensible threshold that makes the algorithm signal when you want it to (some trial-and-error might be needed here to get to a good threshold for your purpose).

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email aus.james@gmail.com instead of using the issue tracker.

## Credits

- [James](https://github.com/gtjamesa)
- [Jean-Paul](https://stackoverflow.com/a/22640362/6029703)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
