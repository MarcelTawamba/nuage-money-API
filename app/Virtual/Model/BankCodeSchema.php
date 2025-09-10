<?php

namespace App\Virtual\Model;



/**
 * @OA\Schema(
 *     title="BankCode",
 *     description="BankCode model",
 *     @OA\Xml(
 *         name="BankCode"
 *     )
 * )
 */
class BankCodeSchema
{

    /**
     * @OA\Property(
     *     title="Code",
     *     description="bank code",
     *     format="int64",
     *     example=125
     * )
     *
     * @var integer
     */
    private int $code;

    /**
     * @OA\Property(
     *      title="Name",
     *      description="Name of the new bank",
     *      example="UBA"
     * )
     *
     * @var string
     */
    public string $name;

    /**
     * @OA\Property(
     *      title="country",
     *      description="country of bank",
     *      example="NGA"
     * )
     *
     * @var string
     */
    public string $country;
}
