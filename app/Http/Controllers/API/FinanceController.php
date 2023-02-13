<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FinanceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class FinanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $finances = FinanceResource::collection($this->getFinancesBySearch($request, 6))
            ->response()
            ->getData(true);

        return $this->wrapResponse(Response::HTTP_OK, 'Success', $finances);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    /**
     * Query to get finances by search.
     *
     * @param  Request $request
     * @param  int $number
     * @return LengthAwarePaginator
     */
    private function getFinancesBySearch(Request $request, int $number): LengthAwarePaginator
    {
        $finances = $request->user()
            ->finances();

        if (isset($request->type)) $finances->where('type', $request->type);

        if (isset($request->status)) $finances->where('status', $request->status);

        return $finances->paginate($number)
            ->appends($request->query());
    }
}
