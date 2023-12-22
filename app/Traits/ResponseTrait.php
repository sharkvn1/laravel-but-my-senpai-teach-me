<?php

namespace App\Traits;

use App\Constants\Constants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait ResponseTrait
{
    /**
     * @param $data
     * @param string $message
     * @param array $metas
     * @param array $headers
     * @return JsonResponse
     */
    public function responseSuccess(
        $data = null,
        string $message = Constants::RESPONSE_MESSAGE_SUCCESS,
        array $metas = [],
        array $headers = []
    ): JsonResponse {
        if ($data instanceof JsonResource && $data->resource instanceof LengthAwarePaginator) {
            return $this->responsePagination($data, $message, $metas, $headers);
        }
        return $this->responseCustom(Response::HTTP_OK, $message, $data, $metas, $headers);
    }

    /**
     * @param int $statusCode
     * @param null $data
     * @param string $message
     * @param array $metas
     * @param array $headers
     * @return JsonResponse
     */
    public function responseError(
        int $statusCode,
        $data = null,
        string $message = Constants::RESPONSE_MESSAGE_FAIL,
        array $metas = [],
        array $headers = []
    ): JsonResponse {
        return $this->responseCustom($statusCode, $message, $data, $metas, $headers);
    }

    /**
     * @param $data
     * @param string $message
     * @param array $metas
     * @param array $headers
     * @return JsonResponse
     */
    public function responseCreatedSuccess(
        $data = null,
        string $message = Constants::RESPONSE_MESSAGE_SUCCESS,
        array $metas = [],
        array $headers = []
    ): JsonResponse {
        return $this->responseCustom(Response::HTTP_CREATED, $message, $data, $metas, $headers);
    }

    /**
     * @param $data
     * @param string $message
     * @param array $metas
     * @param array $headers
     * @return JsonResponse
     */
    public function responseUpdatedSuccess(
        $data = null,
        string $message = Constants::RESPONSE_MESSAGE_SUCCESS,
        array $metas = [],
        array $headers = []
    ): JsonResponse {
        return $this->responseCustom(Response::HTTP_OK, $message, $data, $metas, $headers);
    }

    // Response pagination
    /**
     * @param JsonResource $data
     * @param string $message
     * @param array $metas
     * @param array $headers
     * @return JsonResponse
     */
    public function responsePagination(
        JsonResource $data,
        string $message = Constants::RESPONSE_MESSAGE_SUCCESS,
        array $metas = [],
        array $headers = []
    ): JsonResponse {
        $metas += [
            'paginate' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'next' => $data->nextPageUrl() ? $data->resource->currentPage() + 1 : null,
                'prev' => $data->previousPageUrl() ? $data->resource->currentPage() - 1 : null,
            ],
        ];
        return $this->responseCustom(Response::HTTP_OK, $message, $data->items(), $metas, $headers);
    }

    /**
     * @param string $message
     * @param $data
     * @return JsonResponse
     */
    public function responseNotFound(
        string $message = Constants::RESPONSE_MESSAGE_SUCCESS,
        $data = null
    ): JsonResponse {
        return $this->responseCustom(Response::HTTP_NOT_FOUND, $message, $data);
    }

    /**
     * @param string $message
     * @param array $errors
     * @return JsonResponse
     */
    public function responseValidationFailed(string $message = '', array $errors = []): JsonResponse
    {
        return $this->responseCustom(Response::HTTP_UNPROCESSABLE_ENTITY, $message, $errors);
    }

    /**
     * @param string $message
     * @param null $data
     * @return JsonResponse
     */
    public function responseServerError(
        string $message = Constants::RESPONSE_SERVER_ERROR,
        $data = null
    ): JsonResponse {
        return $this->responseCustom(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $data);
    }

    /**
     * @return JsonResponse
     */
    public function responseMethodNotAllow(): JsonResponse
    {
        return $this->responseCustom(Response::HTTP_METHOD_NOT_ALLOWED, 'Method not allow');
    }

    /**
     * @return JsonResponse
     */
    public function responseUnauthenticated(
        string $message = Constants::RESPONSE_UNAUTHENTICATED,
        $data = null
    ): JsonResponse {
        return $this->responseCustom(Response::HTTP_UNAUTHORIZED, $message, $data);
    }

    /**
     * @return JsonResponse
     */
    public function responseUnauthorized(
        string $message = Constants::RESPONSE_UNAUTHORIZED,
        $data = null
    ): JsonResponse {
        return $this->responseCustom(Response::HTTP_FORBIDDEN, $message, $data);
    }

    /**
     * @param string $message
     * @param null $data
     * @return JsonResponse
     */
    public function responseBadRequest(
        string $message = Constants::RESPONSE_MESSAGE_FAIL,
        $data = null,
        $traces = [],
        $requestPayload = []
    ): JsonResponse {
        $meta = [
            'payload' => $requestPayload,
            'traces' => array_values($traces),
        ];
        return $this->responseCustom(Response::HTTP_BAD_REQUEST, $message, $data, $meta);
    }

    /**
     * @return JsonResponse
     */
    public function responseRequestTimeout(): JsonResponse
    {
        return $this->responseCustom(
            ResponseAlias::HTTP_REQUEST_TIMEOUT,
            Constants::RESPONSE_SERVER_ERROR,
            ''
        );
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @param $data
     * @param array $headers
     * @return JsonResponse
     */
    private function responseCustom(
        int $statusCode = Response::HTTP_OK,
        string $message = '',
        $data = null,
        $metas = [],
        array $headers = ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
    ): JsonResponse {
        $response = [
            'uuid' => config('app.uuid'),
            'code' => $statusCode,
            'message' => $message,
            'data' => $data,
        ];
        if ($metas) {
            foreach ($metas as $key => $value) {
                $response[$key] = $value;
            }
        }
        $response = response()->json($response, $statusCode, $headers, JSON_UNESCAPED_UNICODE);
        return $response;
    }

    /**
     * @param $path
     * @param $fileName
     * @param $header
     * @return BinaryFileResponse
     */
    protected function downloadFile($path, $fileName, $header = [])
    {
        return response()->download(
            $path,
            $fileName,
            $header
        );
    }
}
