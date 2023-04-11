<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class CustomValidationException extends ValidationException
{
    public function render($request): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Validation Errors',
            'status' => false,
            'errors' => $this->validator->errors()->all(),
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }
}
