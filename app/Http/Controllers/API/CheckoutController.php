<?php

namespace App\Http\Controllers\API;

use ErrorException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Kavist\RajaOngkir\Facades\RajaOngkir;
use App\Http\Requests\API\CheckoutRequest;
use App\Traits\MidtransPayment;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    use MidtransPayment;

    /**
     * Update a user's shipping address.
     *
     * @param  CheckoutRequest $request
     * @return JsonResponse
     */
    public function shipping(CheckoutRequest $request): JsonResponse
    {
        if (!$request->user()->update($request->validated()))
            throw new ErrorException('Failed to update the shipping address.', Response::HTTP_INTERNAL_SERVER_ERROR);

        $user = (new UserResource($request->user()->load(['city'])))
            ->response()
            ->getData(true)['data'];

        return $this->wrapResponse(Response::HTTP_OK, 'The shipping address has been updated', $user);
    }

    /**
     * Process the checkout cart and get the costs of courier services.
     *
     * @param CheckoutRequest $request
     * @return JsonResponse
     */
    public function process(CheckoutRequest $request): JsonResponse
    {
        $result = $this->getCourierCosts($request->validated('data'), $request->user()->city_id);

        return $this->wrapResponse(Response::HTTP_OK, 'The costs of courier services has been added', $result);
    }

    /**
     * Validate the coupon code.
     *
     * @param  CheckoutRequest $request
     * @return JsonResponse
     */
    public function couponValidate(CheckoutRequest $request): JsonResponse
    {
        $coupon = $request->getCouponByName($request->validated('coupon'));
        $coupon = ['value' => $coupon->discount_amount, 'discounAmount' => currencyFormat($coupon->discount_amount)];

        return $this->wrapResponse(Response::HTTP_OK, "The coupon has been added", $coupon);
    }

    /**
     * Submit the checkout cart, and store to database.
     *
     * @param  CheckoutRequest $request
     * @return JsonResponse
     */
    public function submit(CheckoutRequest $request): JsonResponse
    {
        $data = $request->validated('data');
        $courierServicesCosts = $this->getCourierCosts($data, $request->user()->city_id);
        $totalCosts = $this->getTotalCosts($data, $courierServicesCosts);

        try {
            DB::beginTransaction();
            $order = $this->storeCart($data, $request->user()->id, $totalCosts);
            $paymentLink = ['paymentLink' => $this->createPaymentLink($order)];
            DB::commit();

            return $this->wrapResponse(Response::HTTP_OK, 'The payment link successfully created', $paymentLink);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Courier Costs
     *
     * @param  array $data
     * @param  int $userAddress
     * @return array
     */
    private function getCourierCosts(array $data, int $userAddress): array
    {
        foreach ($data as $data_key => $d) {
            $params = [
                'origin' => $this->getProductAddress($d['cart'][0]['id']),
                'destination' => $userAddress,
                'weight' => $this->getTotal($d['cart'])['totalWeight'],
                'courier' => $d['courier']
            ];

            foreach ($d['cart'] as $cart_key => $cart)
                $data[$data_key]['cart'][$cart_key]['price'] = currencyFormat($cart['price']);

            $data[$data_key]['courierServices'] = $this->fetchRajaOngkir($params);

            $data[$data_key]['total'] = $this->getTotal($d['cart']);
        }

        return $data;
    }

    /**
     * Fetch to raja ongkir package.
     *
     * @param  array $data
     * @return array
     */
    private function fetchRajaOngkir(array $data): array
    {
        try {
            $result = RajaOngkir::ongkosKirim($data)
                ->get();
            foreach ($result[0]['costs'] as $costs_key => $costs) {
                foreach ($costs['cost'] as $cost_key => $cost)
                    $result[0]['costs'][$costs_key]['cost'][$cost_key]['value'] = currencyFormat($cost['value']);
            }

            return $result[0]['costs'];
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage());
        }
    }

    /**
     * Validate the costs of courier services, and calculate it.
     *
     * @param  array $data
     * @param  array $courierServicesCosts
     * @return array
     */
    private function getTotalCosts(array $data, array $courierServicesCosts): array
    {
        $errorMessage = "Cost of courier service not available";
        $totalCosts = 0;
        $courierServices = [];

        foreach ($data as $data_key => $d) {
            foreach ($courierServicesCosts[$data_key]['courierServices'] as $cs) {
                if ($d['courierService'] === $cs['service']) {
                    if (isset($cs['cost'][0]['value'])) {
                        $cost = integerFormat($cs['cost'][0]['value']);
                        $etd = $cs['cost'][0]['etd'] . ' Hari';
                        if ($cost < 1) throw new ErrorException($errorMessage, Response::HTTP_NOT_FOUND);
                        $totalCosts += $cost;
                        $courierServiceAsString = $d['courier'] . ',' . $d['courierService']  . '(' . $cs['description'] . ')' . ',' . $etd . ',' . $cs['cost'][0]['value'];
                        $courierServices[] = $courierServiceAsString;
                        break;
                    } else {
                        throw new ErrorException($errorMessage, Response::HTTP_NOT_FOUND);
                    }
                } else {
                    throw new ErrorException($errorMessage, Response::HTTP_NOT_FOUND);
                }
            }
        }

        return [$totalCosts, $courierServices];
    }

    /**
     * Store the cart into database.
     *
     * @param  array $data
     * @param  int $userId
     * @param  array $totalCost
     * @return Order
     */
    private function storeCart(array $data, int $userId, array $totalCost): Order
    {
        $totalPrice = head($totalCost);
        $courierServices = last($totalCost);
        array_push($courierServices, currencyFormat($totalPrice));

        foreach ($data as $d) {
            if (isset($d['coupon'])) $coupon[] = $d['coupon'];

            foreach ($d['cart'] as $cart) {
                $orderProduct[$cart['id']] = [
                    'quantity' => $cart['quantity'],
                    'total_price' => $cart['price'] * $cart['quantity']
                ];
                $totalPrice += (int) $cart['price'] * $cart['quantity'];
            }
        }

        if (!$order = Order::create([
            'user_id' => $userId,
            'invoice_number' => 'LARACOMMERCE-' . date('dmy') . Str::random(12),
            'total_price' => $totalPrice,
            'coupons' => json_encode($coupon),
            'courier_services' => json_encode($courierServices),
            'status' => 'IN_CART'
        ])) throw new ErrorException('Failed to create new order', Response::HTTP_INTERNAL_SERVER_ERROR);

        $order->products()
            ->attach($orderProduct);

        return $order;
    }

    /**
     * Get product address.
     *
     * @param  int $merchantId
     * @return int
     */
    private function getProductAddress(int $merchantId): int
    {
        return Product::findOrFail($merchantId)
            ->MerchantAccount
            ->user
            ->city_id;
    }

    /**
     * Get total price, weight, and quantity of product.
     *
     * @param  array $cart
     * @return array
     */
    private function getTotal(array $cart): array
    {
        $totalPrice = 0;
        $totalWeight = 0;
        $totalQuantity = 0;

        foreach ($cart as $key => $c) {
            $totalPrice += (int) $c['price'] * $c['quantity'];
            $totalWeight += $c['weight'] * $c['quantity'];
            $totalQuantity += $c['quantity'];
        }

        return ['totalPrice' => currencyFormat($totalPrice), 'totalWeight' => $totalWeight, 'totalQuantity' => $totalQuantity];
    }
}
