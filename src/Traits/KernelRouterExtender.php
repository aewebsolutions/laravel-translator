<?php

namespace Translator\Traits;

trait KernelRouterExtender
{

    /**
     * Override. This hack redifines router property and reappends middlewares
     * to new router.
     */
    protected function dispatchToRouter()
    {
        $this->router = $this->app['router'];

        foreach ($this->routeMiddleware as $key => $middleware) {
            $this->router->middleware($key, $middleware);
        }

        return parent::dispatchToRouter();
    }

}
