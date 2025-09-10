<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateFeesAPIRequest;
use App\Http\Requests\API\UpdateFeesAPIRequest;
use App\Models\Operator;
use App\Repositories\FeesRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;

/**
 * Class FeesAPIController
 */
class FeesAPIController extends AppBaseController
{
    private FeesRepository $feesRepository;

    public function __construct(FeesRepository $feesRepo)
    {
        $this->feesRepository = $feesRepo;
    }

    /**
     * Display a listing of the Fees.
     * GET|HEAD /fees
     */
    public function index(Request $request): JsonResponse
    {
        $fees = $this->feesRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($fees->toArray(), 'Fees retrieved successfully');
    }


    /**
     * Display the specified Fees.
     * GET|HEAD /fees/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var Operator $fees */
        $fees = $this->feesRepository->find($id);

        if (empty($fees)) {
            return $this->sendError('Fees not found');
        }

        return $this->sendResponse($fees->toArray(), 'Fees retrieved successfully');
    }




}
