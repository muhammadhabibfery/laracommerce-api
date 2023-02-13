<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use Symfony\Component\HttpFoundation\Response;

class LandingPageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = CategoryResource::collection(getRandomCategories())
            ->response()
            ->getData(true);

        $products = ProductResource::collection(getTopSellerProducts())
            ->response()
            ->getData(true);

        $resource = [
            'categories' => $categories,
            'products' => $products
        ];

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $resource);
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
            'message' => $message,
            'data' => []
        ];

        foreach ($resource as $key => $res) {
            if (count($res) > 1) {
                $result['data'][$key]['data'] = $res['data'];

                $result['data'][$key] = array_merge($result['data'][$key], ['pages' => ['links' => $res['links'], 'meta' => $res['meta']]]);
            } else {
                $result['data'][$key] = $res['data'];
            }
        }

        return response()->json($result, $code);
    }
}
