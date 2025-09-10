<?php

namespace App\Virtual\Model;



/**
 * @OA\Schema(
 *     title="Transaction",
 *     description="Transaction model",
 *     @OA\Xml(
 *         name="Transaction"
 *     )
 * )
 */
class TransactionSchema
{
    /**
     * @OA\Property(
     *      title="transaction_id",
     *      description="Transaction Id",
     *      example="521"
     * )
     *
     * @var int
     */
    public int $transaction_id;

    /**
     * @OA\Property(
     *      title="Wallet",
     *      description="this is the currency of the wallet",
     *      example="XAF"
     * )
     *
     * @var string
     */
    public string $wallet;
    /**
     * @OA\Property(
     *      title="ref_id",
     *      description="ref_id",
     *      example="qwertyaev123v11"
     * )
     *
     * @var string
     */
    public string $ref_id;
    /**
     * @OA\Property(
     *      title="amount",
     *      description="amount",
     *      example="500"
     * )
     *
     * @var double
     */
    public float $amount;
    /**
     * @OA\Property(
     *      title="source",
     *      description="source",
     *      example= "[ 'msidn': '+237680355391','name': 'unknown']"
     * )
     *
     * @var string
     */
    public string $source;



    /**
     * @OA\Property(
     *      title="created_at",
     *      description="created_at",
     *      example="2023-11-23T11:58:22.000000Z"
     * )
     *
     * @var \DateTime
     */
    public \DateTime $created_at;





}
