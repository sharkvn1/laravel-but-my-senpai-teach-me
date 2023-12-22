<?php

namespace App\Providers;

use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerUuid();
        $uuid = config('app.uuid');
        if (!$uuid) {
            $uuid = (string)Str::uuid();
        }
        Log::withContext(['uuid' => config('app.uuid'), 'path' => request()->path()]);

        DB::listen(function ($query) use ($uuid) {
            if ($query->sql instanceof Expression) {
                $rawSql = $query->sql->getValue();
            } else {
                $rawSql = $query->sql;
            }
            Log::debug('SQL', [
                'Sql' => $rawSql,
                'Bindings' => $query->bindings,
                'Timing' => $query->time,
            ]);
        });

        // Incoming request
        Event::listen(function (RouteMatched $event) use ($uuid) {
            $request = $event->request;
            Log::info('REQUEST', [
                'url' => $request->fullUrl(),
                'ip' => $request->ips(),
                'headers' => $request->header(),
                'params' => $request->all(),
                'method' => $request->method(),
            ]);
        });

        // Api response
        Event::listen(function (RequestHandled $event) use ($uuid) {
            $response = $event->response;
            if ($response instanceof JsonResponse) {
                Log::info('RESPONSE', [
                    'headers' => $response->headers->all(),
                    'status' => $response->status(),
                    'data' => $response->getData()
                ]);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register Uuid for tracking events log
     *
     * @return void
     */
    protected function registerUuid(): void
    {
        $uuid = (string)Str::uuid();
        config(['app.uuid' => $uuid]);
    }
}
