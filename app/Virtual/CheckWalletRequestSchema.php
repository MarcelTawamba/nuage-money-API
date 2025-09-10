<?php

namespace App\Virtual;



/**
 * @OA\Schema(
 *      title="Get wallet balance",
 *      description="Get wallet balance",
 *      type="object",
 *      required={"service","currency"}
 * )
 */

class CheckWalletRequestSchema
{
    /**
     * @OA\Property(
     *      title="service",
     *      description="client_id of the service",
     *      example="client_id of the service"
     * )
     *
     * @var string
     */
    public string $service;

    /**
     * @OA\Property(
     *      title="currency",
     *      description="currency give the currency code",
     *      example="xaf"
     * )
     *
     * @var string
     */
    public string $currency;


}
