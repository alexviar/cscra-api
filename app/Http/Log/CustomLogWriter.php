<?php

namespace App\Http\Log;

use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Spatie\HttpLogger\LogWriter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CustomLogWriter implements LogWriter
{
    public function logRequest(Request $request)
    {
        $message = $this->formatMessage($this->getMessage($request));
        // $logFile = storage_path('logs/http.log');
        // $monolog = new Logger('log');
        // $monolog->pushHandler(new StreamHandler($logFile), Logger::INFO);
        // $monolog->info($message, compact('bindings', 'time'));
        Log::channel("http")->info($message);
    }

    public function getMessage(Request $request)
    {
        $files = (new Collection(iterator_to_array($request->files)))
            ->map([$this, 'flatFiles'])
            ->flatten();

        return [
            'method' => strtoupper($request->getMethod()),
            'user_id' => $request->user() ? $request->user()->id : 'guest',
            'client_ip_address' => $request->ip(),
            'uri' => $request->getPathInfo(),
            'body' => $request->except(config('http-logger.except')),
            'headers' => $request->headers->all(),
            'files' => $files,
        ];
    }

    protected function formatMessage(array $message)
    {
        $bodyAsJson = json_encode($message['body']);
        $headersAsJson = json_encode($message['headers']);
        $files = $message['files']->implode(',');

        return "{$message['method']} {$message['uri']} - User Id: {$message['user_id']} - Client Ip Address: {$message['client_ip_address']} - Body: {$bodyAsJson} - Headers: {$headersAsJson} - Files: ".$files;
    }

    public function flatFiles($file)
    {
        if ($file instanceof UploadedFile) {
            return $file->getClientOriginalName();
        }
        if (is_array($file)) {
            return array_map([$this, 'flatFiles'], $file);
        }

        return (string) $file;
    }
}
