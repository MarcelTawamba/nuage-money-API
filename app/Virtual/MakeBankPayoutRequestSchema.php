<?php

namespace App\Virtual;


/**
 * @OA\Schema(
 *      title="Make bank payment request schema",
 *      description="this schema is use to make payment request",
 *      type="object",
 *      required={"service","currency","country","ref_id","amount","account_number","account_name","bank_code"}
 * )
 */
class MakeBankPayoutRequestSchema
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
     *      title="bank_code",
     *      description="this is the code of your bank",
     *      example="354"
     * )
     *
     * @var int
     */
    public int $bank_code;

    /**
     * @OA\Property(
     *      title="account_number",
     *      description="this is the bank account number",
     *      example="0066259148"
     * )
     *
     * @var string
     */
    public string $account_number;

    /**
     * @OA\Property(
     *      title="account_name",
     *      description="this is the code of your bank",
     *      example="FEYISOLA ADESANYA"
     * )
     *
     * @var string
     */
    public string $account_name;

}
