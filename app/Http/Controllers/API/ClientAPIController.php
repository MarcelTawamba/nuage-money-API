<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateClientAPIRequest;
use App\Http\Requests\API\UpdateClientAPIRequest;
use App\Models\Client;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Http\Rules\RedirectRule;
use Laravel\Passport\Passport;

//use Laravel\Passport\Http\Controllers;

/**
 * Class ClientAPIController
 */
class ClientAPIController extends AppBaseController
{
    private ClientRepository $clientRepository;
    /**
     * The redirect validation rule.
     *
     * @var \Laravel\Passport\Http\Rules\RedirectRule
     */
    protected $redirectRule;

    /**
     * The validation factory implementation.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validation;

    public function __construct(ClientRepository $clientRepo, ValidationFactory $validation, RedirectRule $redirectRule)
    {
        $this->clientRepository = $clientRepo;
        $this->validation = $validation;
        $this->redirectRule = $redirectRule;
    }

    /**
     * Display a listing of the Clients.
     * GET|HEAD /clients
     */
    public function index(Request $request)
    {

        $user = \Auth::user();
        $clients = $this->clientRepository->activeForUser($user->id);

        if (Passport::$hashesClientSecrets) {
            return $clients;
        }

        return $clients->makeVisible('secret');

       // return $this->sendResponse($clients->toArray(), 'Clients retrieved successfully');
    }

    /**
     * Store a newly created Client in storage.
     * POST /clients
     */
    public function store(CreateClientAPIRequest $request)
    {
        $user_id = $request->user()->getAuthIdentifier();

        $this->validation->make($request->all(), [
            'name' => Rule::unique('oauth_clients')->where(fn ($query) => $query->where('user_id', $user_id)),
            'redirect' => ['required', $this->redirectRule],
            'confidential' => 'boolean',
        ])->validate();


        $client = $this->clientRepository->create(
            $user_id, $request->name, $request->redirect,
            null, false, false, (bool) $request->input('confidential', true)
        );

        if (Passport::$hashesClientSecrets) {
            return ['plainSecret' => $client->plainSecret] + $client->toArray();
        }

        return $client->makeVisible('secret');
    }

    /**
     * Display the specified Client.
     * GET|HEAD /clients/{id}
     */
    public function show($id): JsonResponse
    {
        /** @var Client $client */
        $client = $this->clientRepository->find($id);

        if (empty($client)) {
            return $this->sendError('Client not found');
        }

        return $this->sendResponse($client->toArray(), 'Client retrieved successfully');
    }

    /**
     * Update the specified Client in storage.
     * PUT/PATCH /clients/{id}
     */
    public function update($id, UpdateClientAPIRequest $request)
    {
        $user_id = $request->user()->getAuthIdentifier();
        $client = $this->clientRepository->findForUser($id,$user_id );

        if (! $client) {
            return new Response('', 404);
        }

        $this->validation->make($request->all(), [
            'name' => Rule::unique('oauth_clients')->where(fn ($query) => $query->where('user_id', $user_id))->ignore($user_id),
            'redirect' => ['required', $this->redirectRule],
        ])->validate();

        return $this->clientRepository->update(
            $client, $request->name, $request->redirect
        );
    }

    /**
     * Remove the specified Client from storage.
     * DELETE /clients/{id}
     *
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        /** @var Client $client */
        $client = $this->clientRepository->find($id);

        if (empty($client)) {
            return $this->sendError('Client not found');
        }

        $client->delete();

        return $this->sendSuccess('Client deleted successfully');
    }
}
