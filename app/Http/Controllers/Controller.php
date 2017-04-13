<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Logs an exception with specified message
     *  - Note: this is done here instead of in Exception or Handler classes due to issues in legacy code that cannot be
     *          addressed at this time
     *
     * @todo - refactor exception logging
     *
     * @param \Exception $e
     * @param string     $message
     */
    public function logError(\Exception $e, $message = 'An error occurred!')
    {
        Log::error(
            $message . '
                Message: ' . $e->getMessage() . '
                File: ' . $e->getFile() . ' (' . $e->getLine() . ')
                Trace:
                    ' . $e->getTraceAsString()
        );
    }
}
