<?php

namespace App\Http\Controllers\API;

use Closure;
use ErrorException;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Http\Requests\API\CouponRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CouponController extends Controller
{
    private const CREATE_SUCCESS = 'The coupon created successfully.', DELETE_SUCCESS = 'The coupon deleted successfully.',  FAILED = 'Failed to get service, please try again.';

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
        $coupons = CouponResource::collection($this->getCouponsBySearch($request, 6))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $coupons);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CouponRequest  $request
     * @return JsonResponse
     */
    public function store(CouponRequest $request): JsonResponse
    {
        if ($coupon = Coupon::create($request->validatedCoupon())) {
            $coupon = (new CouponResource($coupon))
                ->response()
                ->getData(true);

            return $this->wrapResponse(Response::HTTP_CREATED, self::CREATE_SUCCESS, $coupon);
        }

        throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Coupon  $coupon
     * @return JsonResponse
     */
    public function destroy(Coupon $coupon): JsonResponse
    {
        if ($coupon->delete()) return $this->wrapResponse(Response::HTTP_OK, self::DELETE_SUCCESS);

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
     * query coupons by search
     *
     * @param  Request $request
     * @param  int $number
     * @return LengthAwarePaginator
     */
    public function getCouponsBySearch(Request $request, int $number): LengthAwarePaginator
    {
        $coupons = Coupon::where('name', 'LIKE', "%{$request->keyword}%")
            ->where('merchant_account_id', $this->merchantId)
            ->latest()
            ->paginate($number)
            ->appends($request->query());

        if (isset($request->keyword)) {
            if (count($coupons) > 0) return $coupons;

            throw new ModelNotFoundException("Coupon not found.");
        };

        return $coupons;
    }
}
