<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Closure;
use ErrorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    private const CREATE_SUCCESS = 'The product created successfully.', UPDATE_SUCCESS = 'The product updated successfully.', DELETE_SUCCESS = 'The product deleted successfully.', FAILED = 'Failed to get service, please try again.';

    private int $merchantId;

    public function __construct()
    {
        $this->middleware(function (Request $request, Closure $next) {
            $this->merchantId = $request->user()->merchantAccount->id;
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $products = ProductResource::collection($this->getProductsBySearch($request, 6))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ProductRequest  $request
     * @return JsonResponse
     */
    public function store(ProductRequest $request): JsonResponse
    {
        if ($product = Product::create($request->validatedProduct())) {
            $product = (new ProductResource($product))
                ->response()
                ->getData(true);

            return $this->wrapResponse(Response::HTTP_CREATED, self::CREATE_SUCCESS, $product);
        }

        throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Display the specified resource.
     *
     * @param  Product  $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $product = (new ProductResource($product->load(['category'])))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ProductRequest  $request
     * @param  Product  $product
     * @return JsonResponse
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        if ($product->update($request->validatedProduct())) {
            $product = (new ProductResource($product))
                ->response()
                ->getData(true);

            return $this->wrapResponse(Response::HTTP_OK, self::UPDATE_SUCCESS, $product);
        }

        throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Product  $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        if ($product->delete()) return $this->wrapResponse(Response::HTTP_OK, self::DELETE_SUCCESS);

        throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);
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

    /**
     * query products by search
     *
     * @param  Request $request
     * @param  int $number
     * @return LengthAwarePaginator
     */
    public function getProductsBySearch(Request $request, int $number): LengthAwarePaginator
    {
        $products = Product::where('name', 'LIKE', "%{$request->keyword}%")
            ->where('merchant_account_id', $this->merchantId)
            ->latest()
            ->paginate($number)
            ->appends($request->query());

        if (isset($request->keyword)) {
            if (count($products) > 0) return $products;

            throw new ModelNotFoundException("Product not found.");
        };

        return $products;
    }
}
