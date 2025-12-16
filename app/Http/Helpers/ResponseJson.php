<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ResponseJson
{
    public static function successResponse(string $message, array|Collection|Model $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (object)$data,
            'message' => $message
        ], ResponseAlias::HTTP_OK);
    }

    public static function pageNotFoundResponse(string $message, array|Collection|Model $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => (object)$data,
            'message' => $message
        ], ResponseAlias::HTTP_NOT_FOUND);
    }
    public static function failedResponse(string $message, array|Collection|Model $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => (object) $data,
            'message' => $message
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }
    public static function validationErrorResponse(string $message, array|Collection|Model $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => (object)$data,
            'message' => $message
        ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
    }
    public static function unauthorizeResponse(string $message, array|Collection|Model $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => (object)$data,
            'message' => $message
        ], ResponseAlias::HTTP_UNAUTHORIZED);
    }

    public static function forbiddenResponse(string $message = "Forbidden", array|Collection|Model $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => (object)$data,
            'message' => $message
        ], ResponseAlias::HTTP_FORBIDDEN);
    }

    public static function errorResponse(string $message = "An unexpected error has occurred, the service will be available soon.!", array|Collection|Model $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => (object) $data,
            'message' => $message
        ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }
    public static function tooManyRequestResponse(string $message = "Too many requests. Please try again later."): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => (object)[],
            'message' => $message
        ], ResponseAlias::HTTP_TOO_MANY_REQUESTS);
    }
    public static function serviceUnavailableResponse(string $message = "The service is currently unavailable. Please try again later."): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => (object)[],
            'message' => $message
        ], 503);
    }
}
