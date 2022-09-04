<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class OAuthException extends \Exception
{
    /**
     * Render the exception as an HTTP response.
     *
     * @param  Request  $request
     * @return Response
     */
    public function render(Request $request) : Response
    {
        Log::error($this->getMessage() . PHP_EOL . $this->getTraceAsString());
        return response()->view(
            'oauth.error',
            [
                'title' => 'OAuth Error',
                'message' => $this->getMessage()
            ],
            $this->getCode()
        );
    }
}
