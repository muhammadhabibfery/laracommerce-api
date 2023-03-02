<?php

namespace App\Http\Controllers\API;

use ErrorException;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\FinanceResource;
use App\Http\Requests\API\FinanceRequest;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\MerchantAccountResource;
use App\Notifications\WithDrawRequestNotification;
use Illuminate\Database\Eloquent\Collection;

class WithDrawController extends Controller
{
    private const WD_DESCRIPTION = 'withdraw of merchant funds',
        WD_TYPE = 'KREDIT',
        CREATE_SUCCESS = 'The withdraw request has been sent.',
        FAILED = 'Failed to get service, please try again.';

    /**
     * Send the resource needed for the frontend.
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        $merchantAccount = request()->user()
            ->merchantAccount;

        $merchantAccount =  (new MerchantAccountResource($merchantAccount->load(['banking'])))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $merchantAccount);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  FinanceRequest  $request
     * @return JsonResponse
     */
    public function store(FinanceRequest $request)
    {
        $user = $request->user();
        $validatedData = $this->mergeData($request->validatedWD());

        if ($wd = $user->finances()->create($validatedData)) {
            if (!$user->merchantAccount->update(Arr::only($validatedData, ['balance'])))
                throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);

            Notification::send($this->getStaff(), new WithDrawRequestNotification($wd, $user));

            $wd = (new FinanceResource($wd))
                ->response()
                ->getData(true);

            return $this->wrapResponse(Response::HTTP_CREATED, self::CREATE_SUCCESS, $wd);
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
     * merge data into an array
     *
     * @param  array $validatedData
     * @return array
     */
    private function mergeData(array $validatedData): array
    {
        $additonalData = [
            'description' => self::WD_DESCRIPTION,
            'type' => self::WD_TYPE,
            'status' => 'PENDING',
            'balance' => $validatedData['balance'] - $validatedData['amount']
        ];

        return array_merge($validatedData, $additonalData);
    }

    /**
     * Query to get users who have staff role.
     *
     * @return Collection
     */
    private function getStaff(): Collection
    {
        // return User::whereJsonContains('role', 'ADMIN')->firstOrFail();
        return User::where('role', json_encode(["STAFF"]))->get();
    }
}
