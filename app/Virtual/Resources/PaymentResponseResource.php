<?php

namespace App\Virtual\Resources;


/**
 * @OA\Schema(
 *     title="MobilePaymentResponseResource",
 *     description="Mobile Payment Response Resource this is what you will get as reponse when you make payment request",
 *     @OA\Xml(
 *         name="Mobile PaymentResponseResource"
 *     )
 * )
 */
class PaymentResponseResource
{

    /**
     * @OA\Property(
     *      title="pay_token",
     *      description="pay_token of the transaction",
     *      example="qwe741wedd25514"
     * )
     *
     * @var string
     */

    public string $pay_token;

    /**
     * @OA\Property(
     *      title="amount",
     *      description="the amount of the transaction",
     *      example="500"
     * )
     *
     * @var float
     */
    public float $amount;

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
     *      title="payment_method",
     *      description="this is the operator of the request given to you when you made a request",
     *      example="toupesu_mobile"
     * )
     *
     * @var string
     */
    public string $payment_method;
    /**
     * @OA\Property(
     *      title="",
     *      description="thi is the status of the request",
     *      example="CREATED"
     * )
     *
     * @var string
     */
    public string $status;
    /**
     * @OA\Property(
     *      title="success",
     *      description="this is a flag to know if the request work well",
     *      example=true
     * )
     *
     * @var bool
     */
    public bool $success;



}

