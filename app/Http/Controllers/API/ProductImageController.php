<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Support\Arr;
use App\Models\ProductImage;
use App\Traits\ImageHandler;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductImageResource;
use App\Http\Requests\API\ProductImageRequest;
use App\Http\Resources\ProductResource;
use ErrorException;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductImageController extends Controller
{
    use ImageHandler;

    private const CREATE_SUCCESS = 'The product image created successfully.', DELETE_SUCCESS = 'The product image deleted successfully.', FAILED = 'Failed to get service, please try again.';
    public static $directory = 'product-images';

    /**
     * Display a listing of the resource.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function index(Product $product): JsonResponse
    {
        $productImages = ProductImageResource::collection($this->getProductImagesByProductName($product->slug))
            ->response()
            ->getData(true);

        $productImages['product'] = (new ProductResource($product))
            ->response()
            ->getData(true)['data'];

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $productImages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ProductImageRequest  $request
     * @return JsonResponse
     */
    public function store(ProductImageRequest $request): JsonResponse
    {
        $productId = $request->validated()['product_id'];
        $data = Arr::except($request->validatedProductImage(), ['image']);

        if (!$this->checkMaxAmountOfProductImages($productId, 5))
            throw new BadRequestHttpException("The amount of product images has exceeded capacity (max 5 items).");

        if ($productImage = ProductImage::create($data)) {
            $this->createImage($request, $productImage->name, self::$directory);
            $productImage = (new ProductImageResource($productImage))
                ->response()
                ->getData(true);

            return $this->wrapResponse(Response::HTTP_CREATED, self::CREATE_SUCCESS, $productImage);
        }

        throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ProductImage  $productImage
     * @return JsonResponse
     */
    public function destroy(ProductImage $productImage): JsonResponse
    {
        $name = $productImage->name;
        if ($productImage->delete()) {
            $this->deleteImage(self::$directory, $name);

            return $this->wrapResponse(Response::HTTP_OK, self::DELETE_SUCCESS);
        }

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
                $result = array_merge($result, ['product' => $resource['product']]);
        };

        return response()->json($result, $code);
    }

    /**
     * Get product images by product id.
     *
     * @param  string|null $slug
     * @return Collection|null
     */
    public function getProductImagesByProductName(?string $slug = null): Collection|null
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        return ProductImage::where('product_id', $product->id)
            ->get();
    }

    /**
     * Get product by id.
     *
     * @param  int $id
     * @return Product|ModelNotFoundException
     */
    private function getProductById(int $id): Product|ModelNotFoundException
    {
        return Product::findOrFail($id);
    }

    /**
     * Check maximum amount product images (per product).
     *
     * @param  int $id
     * @param  int $max
     * @return bool
     */
    private function checkMaxAmountOfProductImages(int $id, int $max): bool
    {
        $totalProductImages = $this->getProductById($id)
            ->productImages()
            ->count();

        return $totalProductImages < $max;
    }
}
