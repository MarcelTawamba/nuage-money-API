<?php

namespace App\Virtual\Model;


/**
 * @OA\Schema(
 *     title="Country",
 *     description="Country model",
 *     @OA\Xml(
 *         name="Country"
 *     )
 * )
 */
class CountrySchema
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
     *      description="Name of the new country",
     *      example="Cameroon"
     * )
     *
     * @var string
     */
    public string $name;

    /**
     * @OA\Property(
     *      title="Name",
     *      description="Name of the new project",
     *      example="cmr"
     * )
     *
     * @var string
     */
    public string $code;
}
