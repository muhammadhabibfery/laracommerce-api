<?php

namespace App\Http\Controllers\API;

use Closure;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
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
     * @param Request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $orders = OrderResource::collection($this->getProductsByMerchantId($request, 6))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  Order  $order
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        $order = $order->load([
            'products' => fn ($query) => $query->where('merchant_account_id', $this->merchantId)->paginate(6),
            'totalPriceProductsMerchant' => fn ($query) => $query->where('merchant_account_id', $this->merchantId)
        ]);

        $order = (new OrderResource($order))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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

    private function getProductsByMerchantId(Request $request, int $number): LengthAwarePaginator
    {
        return Order::withWhereHas(
            'totalPriceProductsMerchant',
            fn ($query) => $query->where('merchant_account_id', request()->user()->merchantAccount->id)
        )->latest()
            ->paginate($number)
            ->appends($request->query());
    }
}
