<?php

namespace App\Virtual\Model;



/**
 * @OA\Schema(
 *     title="Wallet",
 *     description="Wallet model",
 *     @OA\Xml(
 *         name="Wallet"
 *     )
 * )
 */
class WalletSchema
{
    /**
     * @OA\Property(
     *      title="Balance",
     *      description="Wallet Balance",
     *      example="500"
     * )
     *
     * @var double
     */
    public float $balance;

    /**
     * @OA\Property(
     *      title="Currency",
     *      description="wallet currency",
     *      example="XAF"
     * )
     *
     * @var string
     */
    public string $currency;

}
