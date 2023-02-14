<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\MerchantAccount;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantAccountResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class ProductCustomerController extends Controller
{
    /**
     * Get detail merchant account.
     *
     * @param  MerchantAccount $merchantAccount
     * @return JsonResponse
     */
    public function getMerchant(MerchantAccount $merchantAccount): JsonResponse
    {
        $merchantAccount = (new MerchantAccountResource($merchantAccount->load(['products'])))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $merchantAccount);
    }

    /**
     * Get detail product.
     *
     * @param  Product $product
     * @return JsonResponse
     */
    public function getProduct(Product $product): JsonResponse
    {
        $product = (new ProductResource($product->load(['category', 'merchantAccount'])))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $product);
    }

    /**
     * Search products by keyword (q).
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function searchProducts(Request $request): JsonResponse
    {
        if (empty($request->query('q'))) throw new ModelNotFoundException('Product not found.');

        $products = Product::search($request->query('q'))
            ->paginate(6)
            ->appends($request->query());

        if (count($products) < 1) throw new ModelNotFoundException("Products {$request->q} not found.");

        $products = ProductResource::collection($products)
            ->response()
            ->getData(true);


        return $this->wrapResponse(Response::HTTP_OK, 'Success', $products);
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
