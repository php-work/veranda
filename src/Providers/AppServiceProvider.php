<?php
namespace Veranda\Providers;

use Veranda\Support;
use Veranda\Contracts;
//use Veranda\Cabin;
//use Veranda\Loader;
//use Veranda\Luri;
//use Veranda\Routers;
//use Veranda\Foundation\{
//    Providers,
//    Bus,
//    LuclinScheme
//};
use Veranda\Protocol\{
    XHeaders,
    //Operator,
    Request
};

use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Database\Eloquent;
// use Illuminate\Database\Eloquent\{
//     Relations\Relation as Relation
// };
use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\{
    Facades\Queue
};
use Illuminate\Queue\{
    QueueManager,
    Events\JobProcessed,
    Events\JobProcessing,
    Events\JobFailed
};
//use Auth;
//use Log;
//use Validator;

use Illuminate\Support\{
    ServiceProvider
};

class AppServiceProvider extends ServiceProvider
{


    /**
     * Boorstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerResolving();
    }

    protected function registerResolving(): void {
        $this->app->resolving(Request::class, function ($request, $app) {
            $request->confirm();
        });
    }
}
