<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreatecurrencyAPIRequest;
use App\Http\Requests\API\UpdatecurrencyAPIRequest;
use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;

/**
 * Class currencyAPIController
 */
class currencyAPIController extends AppBaseController
{
    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepo)
    {
        $this->currencyRepository = $currencyRepo;
    }

    /**
     * @OA\Get(
     *     path="/currencies",
     *     operationId="currencies",
     *     tags={"Global"},
     *     summary="This request is use to have all currencies ",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="App\Virtual\Resources\CurrencyResponseResource")
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * ),
     * Display a listing of the currencies.
     * GET|HEAD /currencies
     */
    public function index(Request $request): JsonResponse
    {
        $currencies = $this->currencyRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($currencies->toArray(), 'Currencies retrieved successfully');
    }


    /**
     * Display the specified currency.
     * GET|HEAD /currencies/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var Currency $currency */
        $currency = $this->currencyRepository->find($id);

        if (empty($currency)) {
            return $this->sendError('Currency not found');
        }

        return $this->sendResponse($currency->toArray(), 'Currency retrieved successfully');
    }




}
