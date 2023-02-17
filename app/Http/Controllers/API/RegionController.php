<?php

namespace App\Http\Controllers\API;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Resources\ProvinceResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RegionController extends Controller
{
    public array $couriers =
    [
        ['id' => 'jne', 'name' => 'JNE'],
        ['id' => 'tiki', 'name' => 'TIKI'],
        ['id' => 'pos', 'name' => 'POS']
    ];

    /**
     * Get all provinces.
     *
     * @return JsonResponse
     */
    public function getAllProvinces(): JsonResponse
    {
        $provinces = ProvinceResource::collection(Province::latest()->get())
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $provinces);
    }

    /**
     * Get the cities by province id.
     *
     * @param  int|null $id
     * @return JsonResponse
     */
    public function getTheCitiesByProvinceId(?int $id = null)
    {
        if (empty($id)) throw new ModelNotFoundException('City not found.', Response::HTTP_NOT_FOUND);

        $cities = City::where('province_id', $id)
            ->latest()
            ->get();

        $cities = CityResource::collection($cities)
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $cities);
    }

    /**
     * Get all couriers.
     *
     * @return JsonResponse
     */
    public function getAllCouriers(): JsonResponse
    {
        $data = ['data' => $this->couriers];
        return $this->wrapResponse(Response::HTTP_OK, 'Success', $data);
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
            'data' => $resource['data']
        ];

        return response()->json($result, $code);
    }
}
