<?php

namespace App\Services\RateLimiting;

// I can also make a contract so for different situations i can implement different strategies of retrial
// For example, prism retry resolver could have rules built around PrismException and PrismRateLimitException
// For other specific providers / packages I can create custom resolvers if needed
class RetryResolver {}
