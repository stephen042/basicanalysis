<?php

return [
    // The legacy compound investment plan uses 27.8% monthly. Keep it
    // configurable without trusting a value submitted by the browser.
    'monthly_return' => (float) env('COMPOUND_MONTHLY_RETURN', 27.8),
    'durations' => [1, 3, 6, 12],
];
