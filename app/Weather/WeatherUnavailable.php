<?php

namespace App\Weather;

use RuntimeException;

/**
 * The weather source could not be reached or returned unusable data.
 */
class WeatherUnavailable extends RuntimeException {}
