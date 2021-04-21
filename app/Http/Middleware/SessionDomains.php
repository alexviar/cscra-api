<?php

namespace App\Http\Middleware;

use Closure;

class SessionDomains
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->getHost() === 'cajasaludcaminos-env.eba-pxqt9yrh.us-east-2.elasticbeanstalk.com'){
            config([
                'session.domain' => '.cajasaludcaminos-env.eba-pxqt9yrh.us-east-2.elasticbeanstalk.com'
            ]);
        }
        return $next($request);
    }
}