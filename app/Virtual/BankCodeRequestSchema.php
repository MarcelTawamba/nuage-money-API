<?php

namespace App\Virtual;


/**
 * @OA\Schema(
 *      title="Bank Code Request Schema",
 *      description="this schema is use to make payment request",
 *      type="object",
 *      required={"currency"}
 * )
 */
class BankCodeRequestSchema
{

    /**
     * @OA\Property(
     *      title="currency",
     *      description="currency for which you want bank code",
     *      example="NGN"
     * )
     *
     * @var string
     */
    public string $currency;
}
