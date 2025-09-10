<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateAppFeeAPIRequest;
use App\Http\Requests\API\UpdateAppFeeAPIRequest;
use App\Models\CustomFee;
use App\Repositories\AppFeeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;

/**
 * Class AppFeeAPIController
 */
class AppFeeAPIController extends AppBaseController
{
    private AppFeeRepository $appFeeRepository;

    public function __construct(AppFeeRepository $appFeeRepo)
    {
        $this->appFeeRepository = $appFeeRepo;
    }

    /**
     * Display a listing of the AppFees.
     * GET|HEAD /app-fees
     */
    public function index(Request $request): JsonResponse
    {
        $appFees = $this->appFeeRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($appFees->toArray(), 'App Fees retrieved successfully');
    }

    /**
     * Store a newly created AppFee in storage.
     * POST /app-fees
     */
    public function store(CreateAppFeeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $appFee = $this->appFeeRepository->create($input);

        return $this->sendResponse($appFee->toArray(), 'App Fee saved successfully');
    }

    /**
     * Display the specified AppFee.
     * GET|HEAD /app-fees/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var CustomFee $appFee */
        $appFee = $this->appFeeRepository->find($id);

        if (empty($appFee)) {
            return $this->sendError('App Fee not found');
        }

        return $this->sendResponse($appFee->toArray(), 'App Fee retrieved successfully');
    }

    /**
     * Update the specified AppFee in storage.
     * PUT/PATCH /app-fees/{id}
     */
    public function update($id, UpdateAppFeeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var CustomFee $appFee */
        $appFee = $this->appFeeRepository->find($id);

        if (empty($appFee)) {
            return $this->sendError('App Fee not found');
        }

        $appFee = $this->appFeeRepository->update($input, $id);

        return $this->sendResponse($appFee->toArray(), 'AppFee updated successfully');
    }

    /**
     * Remove the specified AppFee from storage.
     * DELETE /app-fees/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var CustomFee $appFee */
        $appFee = $this->appFeeRepository->find($id);

        if (empty($appFee)) {
            return $this->sendError('App Fee not found');
        }

        $appFee->delete();

        return $this->sendSuccess('App Fee deleted successfully');
    }
}
