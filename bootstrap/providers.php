<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
];

if ($app->environment('local', 'development')) {
    $providers[] = App\Providers\TelescopeServiceProvider::class;
}

return $providers;
