<?php

namespace App\Virtual;


/**
 * @OA\Schema(
 *      title="Verify account request schema",
 *      description="this schema is use to check a bank account is valid",
 *      type="object",
 *      required={"account_number","account_name","bank_code"}
 * )
 */
class VerifyAccountRequestSchema
{

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
