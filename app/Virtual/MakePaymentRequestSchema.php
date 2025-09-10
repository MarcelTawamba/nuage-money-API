<?php

namespace App\Virtual;



/**
 * @OA\Schema(
 *      title="Make mobile payment request schema",
 *      description="this schema is use to make payment request",
 *      type="object",
 *      required={"service","currency","country","ref_id","amount","msidn"}
 * )
 */

class MakePaymentRequestSchema
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

    /**
     * @OA\Property(
     *      title="country",
     *      description="thisis the country code of the client",
     *      example="cmr"
     * )
     *
     * @var string
     */
    public string $country;

    /**
     * @OA\Property(
     *      title="ref_id",
     *      description="this is the referrence of the id",
     *      example="qwe123asd123"
     * )
     *
     * @var string
     */
    public string $ref_id;

    /**
     * @OA\Property(
     *      title="amount",
     *      description="this is the amount",
     *      example="500"
     * )
     *
     * @var float
     */
    public float $amount;

    /**
     * @OA\Property(
     *      title="msidn",
     *      description="this is the mobile number for the payment",
     *      example="+237680355391"
     * )
     *
     * @var string
     */
    public string $msidn;

}
