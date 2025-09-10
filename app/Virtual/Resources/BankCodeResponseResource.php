<?php

namespace App\Virtual\Resources;


use App\Virtual\Model\BankCodeSchema;

/**
 * @OA\Schema(
 *     title="BankCodeResponseResource",
 *     description="App Bank code Response Resource this is what you will get as reponse when you make request to have all bank associated with the currency",
 *     @OA\Xml(
 *         name="BankCodeResponseResource"
 *     )
 * )
 */
class BankCodeResponseResource
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
     * @var BankCodeSchema[]
     */
    private array $data;

}
