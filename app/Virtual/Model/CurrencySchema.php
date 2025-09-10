<?php

namespace App\Virtual\Model;



/**
 * @OA\Schema(
 *     title="Currency",
 *     description="Currency model",
 *     @OA\Xml(
 *         name="Currency"
 *     )
 * )
 */
class CurrencySchema
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private int $id;

    /**
     * @OA\Property(
     *      title="Name",
     *      description="Name of the new project",
     *      example="Franc CFA"
     * )
     *
     * @var string
     */
    public string $name;

    /**
     * @OA\Property(
     *      title="Name",
     *      description="Name of the new project",
     *      example="XAF"
     * )
     *
     * @var string
     */
    public string $code;


}
