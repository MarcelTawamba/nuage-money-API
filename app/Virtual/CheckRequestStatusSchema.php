<?php

namespace App\Virtual;


/**
 * @OA\Schema(
 *      title="Check Payment Request Status",
 *      description="Use to verify pay in request status",
 *      type="object",
 *      required={"service","ref_id","payment_method"}
 * )
 */
class CheckRequestStatusSchema
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

}
