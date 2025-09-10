<?php

namespace App\Virtual\Resources;


use App\Virtual\Model\WalletSchema;

/**
 * @OA\Schema(
 *     title="AppWalletResponseResource",
 *     description="App Wallet Response Resource this is what you will get as reponse when you make request to have all wallet associated with the app",
 *     @OA\Xml(
 *         name="AppWalletResponseResource"
 *     )
 * )
 */
class AppWalletResponseResource
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
     * @var WalletSchema[]
     */
    private array $data;

}
