<?php

namespace App\Virtual\Resources;


/**
 * @OA\Schema(
 *     title="VerifyAccountResponseResource",
 *     description="Mobile Payment Response Resource this is what you will get as reponse when you make payment request",
 *     @OA\Xml(
 *         name="VerifyAccountResponseResource"
 *     )
 * )
 */
class VerifyAccountResponseResource
{

    /**
     * @OA\Property(
     *      title="message",
     *      description="this give you information that account is available or not",
     *      example="Account available"
     * )
     *
     * @var string
     */
    public string $message;
    /**
     * @OA\Property(
     *      title="success",
     *      description="this is a flag to know if the request work well",
     *      example=true
     * )
     *
     * @var bool
     */
    public bool $success;


}

