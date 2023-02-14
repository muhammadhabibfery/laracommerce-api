<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\MerchantAccount;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantAccountResource;
use Symfony\Component\HttpFoundation\Response;

class ProductCustomerController extends Controller
{
    public function getMerchant(MerchantAccount $merchantAccount)
    {
        // return $merchantAccount->load(['products']);
        $merchantAccount = (new MerchantAccountResource($merchantAccount->load(['products'])))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $merchantAccount);
    }

    /**
     * wrap a result into json response.
     *
     * @param  int $code
     * @param  string $message
     * @param  array $resource
     * @return JsonResponse
     */
    private function wrapResponse(int $code, string $message, ?array $resource = []): JsonResponse
    {
        $result = [
            'code' => $code,
            'message' => $message
        ];

        if (count($resource)) {
            $result = array_merge($result, ['data' => $resource['data']]);

            if (count($resource) > 1)
                $result = array_merge($result, ['pages' => ['links' => $resource['links'], 'meta' => $resource['meta']]]);
        }

        return response()->json($result, $code);
    }
}
