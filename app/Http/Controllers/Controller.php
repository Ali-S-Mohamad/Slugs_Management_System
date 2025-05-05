<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function success($data = null, string $message = '', int $status = 200): JsonResponse
    {
        $payload = ['status' => 'success'];
        if ($message !== '') {
            $payload['message'] = $message;
        }
        if (! is_null($data)) {
            $payload['data'] = $data;
        }
        return response()->json($payload, $status);
    }

    protected function error(string $message = '', array $errors = [], int $status = 422): JsonResponse
    {
        $payload = [
            'status'  => 'error',
            'message' => $message,
        ];
        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }
        return response()->json($payload, $status);
    }
}
