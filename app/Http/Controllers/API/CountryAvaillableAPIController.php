<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateCountryAvaillableAPIRequest;
use App\Http\Requests\API\UpdateCountryAvaillableAPIRequest;
use App\Models\CountryAvaillable;
use App\Repositories\CountryAvaillableRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;

/**
 * Class CountryAvaillableAPIController
 */
class CountryAvaillableAPIController extends AppBaseController
{
    private CountryAvaillableRepository $countryAvaillableRepository;

    public function __construct(CountryAvaillableRepository $countryAvaillableRepo)
    {
        $this->countryAvaillableRepository = $countryAvaillableRepo;
    }

    /**
     * @OA\Get(
     *     path="/country-available",
     *     operationId="country-availlables",
     *     tags={"Global"},
     *     summary="This request is use to have all country availlables ",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\CountryResponseResource")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of the CountryAvaillables.
     * GET|HEAD /country-available
     */
    public function index(Request $request): JsonResponse
    {
        $countryAvaillables = $this->countryAvaillableRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($countryAvaillables->toArray(), 'Country Availlables retrieved successfully');
    }



    /**
     * Display the specified CountryAvaillable.
     * GET|HEAD /country-availlables/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var CountryAvaillable $countryAvaillable */
        $countryAvaillable = $this->countryAvaillableRepository->find($id);

        if (empty($countryAvaillable)) {
            return $this->sendError('Country Availlable not found');
        }

        return $this->sendResponse($countryAvaillable->toArray(), 'Country Availlable retrieved successfully');
    }



}
