<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function success(
        int $status = Response::HTTP_OK,
        ?string $message = null,
        mixed $data = null,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    protected function created(
        string $message = 'Created successfully.',
        mixed $data = null,
        array $meta = []
    ): JsonResponse {
        return $this->success(
            status: Response::HTTP_CREATED,
            message: $message,
            data: $data,
            meta: $meta
        );
    }

    protected function error(
        int $status = Response::HTTP_BAD_REQUEST,
        string $message = 'Something went wrong.',
        mixed $data = null,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    protected function notFound(
        string $message = 'Resource not found.'
    ): JsonResponse {
        return $this->error(
            status: Response::HTTP_NOT_FOUND,
            message: $message
        );
    }

    protected function validationError(
        mixed $errors,
        string $message = 'Validation failed.'
    ): JsonResponse {
        return $this->error(
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            message: $message,
            data: $errors
        );
    }
}