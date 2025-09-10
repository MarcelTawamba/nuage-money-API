<?php



namespace App\Virtual\Resources;


use App\Virtual\Model\TransactionSchema;

/**
 * @OA\Schema(
 *     title="AppTransactionResponseResource",
 *     description="App Transaction Response Resource this is what you will get as reponse when you make request to have all transaction associated with the app",
 *     @OA\Xml(
 *         name="AppTransactionResponseResource"
 *     )
 * )
 */
class AppTransactionResponseResource
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
     * @var TransactionSchema[]
     */
    private array $data;

}
