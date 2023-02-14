<?php

namespace App\Http\Controllers\API;

use Closure;
use ErrorException;
use App\Traits\ImageHandler;
use Illuminate\Http\Request;
use App\Models\MerchantAccount;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\MerchantAccountResource;
use App\Http\Requests\API\MerchantAccountRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MerchantAccountController extends Controller
{
    use ImageHandler;

    private const CREATE_SUCCESS = 'The merchant account created successfully.', UPDATE_SUCCESS = 'The merchant account updated successfully.', FAILED = 'Failed to get service, please try again.';
    public static $directory = 'merchant-images';

    public function __construct()
    {
        $this->middleware(function (Request $request, Closure $next) {
            if (checkRole(['CUSTOMER'], $request->user()->role)) return $next($request);

            throw new BadRequestHttpException("You already have merchant account.");
        })->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  MerchantAccountRequest  $request
     * @return JsonResponse
     */
    public function store(MerchantAccountRequest $request): JsonResponse
    {
        if ($merchantAccount = MerchantAccount::create($request->validatedMerchantAccount())) {
            $this->createImage($request, $merchantAccount->image, self::$directory);

            if (!$request->user()->update(['role' => 'MERCHANT'])) throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);

            $merchantAccount = (new MerchantAccountResource($merchantAccount))
                ->response()
                ->getData(true);

            return $this->wrapResponse(Response::HTTP_CREATED, self::CREATE_SUCCESS, $merchantAccount);
        }

        throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Display the specified resource.
     *
     * @param  MerchantAccount  $merchantAccount
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $merchantAccount = (new MerchantAccountResource($this->getMerchantAccount()->load(['banking', 'user'])))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $merchantAccount);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  MerchantAccountRequest  $request
     * @param  MerchantAccount  $merchantAccount
     * @return JsonResponse
     */
    public function update(MerchantAccountRequest $request): JsonResponse
    {
        $merchantAccount = $this->getMerchantAccount();

        if ($merchantAccount->update($request->validatedMerchantAccount())) {
            $this->createImage($request, $merchantAccount->image, self::$directory);

            $merchantAccount = (new MerchantAccountResource($merchantAccount))
                ->response()
                ->getData(true);

            return $this->wrapResponse(Response::HTTP_OK, self::UPDATE_SUCCESS, $merchantAccount);
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
                $result = array_merge($result, ['pages' => ['links' => $resource['links'], 'meta' => $resource['meta']]]);
        }

        return response()->json($result, $code);
    }

    /**
     * Get merchant account.
     *
     * @return MerchantAccount
     */
    private function getMerchantAccount(): MerchantAccount
    {
        return MerchantAccount::where('user_id', request()->user()->id)->firstOrFail();
    }
}
