<?php

namespace App\Virtual\Resources;

use App\Virtual\Model\CurrencySchema;


/**
 * @OA\Schema(
 *     title="CurrencyResponseResource",
 *     description="Response given for when currencies are requested",
 *     @OA\Xml(
 *         name="CurrencyResponseResource"
 *     )
 * )
 */
class CurrencyResponseResource
{


    /**
     * @OA\Property(
     *      title="success",
     *      description="status of your request",
     *      example=true
     * )
     *
     * @var bool
     */

    public bool $success;

    /**
     * @OA\Property(
     *      title="message",
     *      description="message for the request",
     *      example="currencies retrieves successfully"
     * )
     *
     * @var string
     */
    public string $message;

    /**
     * @OA\Property(
     *      title="data",
     *      description="this is the data",
     *
     * )
     *
     * @var CurrencySchema[]
     */
    private array $data;





}
