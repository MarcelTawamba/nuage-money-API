<?php

namespace App\Virtual\Resources;

use App\Virtual\Model\CountrySchema;


/**
 * @OA\Schema(
 *     title="CountryResponseResource",
 *     description="Response given for when countries are requested",
 *     @OA\Xml(
 *         name="CountryResponseResource"
 *     )
 * )
 */
class CountryResponseResource
{
    /**
     * @OA\Property(
     *      title="success",
     *      description="status of your request",
     *      example=true
     * )
     *
     * @var bool
     */

    public bool $success;

    /**
     * @OA\Property(
     *      title="message",
     *      description="message for the request",
     *      example="countries retrieves successfully"
     * )
     *
     * @var string
     */
    public string $message;

    /**
     * @OA\Property(
     *      title="data",
     *      description="this is the data",
     *
     * )
     *
     * @var CountrySchema[]
     */
    private array $data;



}
