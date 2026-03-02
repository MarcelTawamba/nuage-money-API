<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ApiKey;
use App\Models\ApiScope;
use App\Models\Client;
use App\Models\Company;
use App\Repositories\UserRepository;
use App\Services\ApiKeyService;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Lwwcas\LaravelCountries\Models\Country;

class UserController extends AppBaseController
{
    /** @var UserRepository $userRepository*/
    private $userRepository;
    
    /** @var ApiKeyService $apiKeyService*/
    private $apiKeyService;

    public function __construct(UserRepository $userRepo, ApiKeyService $apiKeyService)
    {
        $this->userRepository = $userRepo;
        $this->apiKeyService = $apiKeyService;
    }

    /**
     * Display a listing of the User.
     */
    public function index(UserDataTable $userDataTable)
    {

        return $userDataTable->render('users.index');

    }

    /**
     * Show the form for creating a new User.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created User in storage.
     */
    public function store(CreateUserRequest $request)
    {
        $input = $request->all();

        $user = $this->userRepository->create($input);

        Flash::success('User saved successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Display the specified User.
     */
    public function show($id)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        return view('users.show')->with('user', $user);
    }

    /**
     * Show the form for editing the specified User.
     */
    public function edit($id)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        return view('users.edit')->with('user', $user);
    }

    /**
     * Update the specified User in storage.
     */
    public function update($id, UpdateUserRequest $request)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        $data = $request->all();

        $validator = Validator::make($data, [

            'email' => ['required', 'email', 'max:255', Rule::unique('App\Models\User')->ignore($user->id)],


        ]);
        if ($validator->fails()) {
            return redirect(route('users.edit',$user->id))
                ->withErrors($validator)
                ->withInput();
        }



        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->country_code = $data['country_code'];
        $user->phone_number = $data['phone_number'];
        if( $data["password"] != null and  $data["password"] != ""){
            $user->password =  Hash::make($data['password']);
        }
        $user->save();

        Flash::success('User updated successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Remove the specified User from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        $this->userRepository->delete($id);

        Flash::success('User deleted successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Display the specified User.
     */
    public function profile(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        $user = Auth::user();

        $company = Company::whereUserId($user->id)->first();

        return view('users.profile')->with('user', $user)->with('company', $company);
    }
    /**
     * Display the specified User.
     */
    public function editProfile(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        $user = Auth::user();
        $country = Country::all();
        $countries = [];

        foreach ($country as $can){
            $countries[strtolower($can->iso_alpha_3)] = $can->name;
        }
        $user->phone_number = substr($user->phone_number,4);


        $company = Company::whereUserId($user->id)->first();
        return view('users.edit_profile')->with('user', $user)->with("countries",$country)->with('pays', $countries)->with('company', $company);
    }

    /**
     * Display the specified User.
     */
    public function updateProfile(UpdateProfileRequest $request){
        $input = $request->all();
        $user = Auth::user();
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->country_code = $input['country_code'];
        $user->phone_number = "+". $input['phone_code'] . $input['phone_number']  ;
        $user->save();

        Flash::success('Profile updated successfully.');

        return redirect(route('users.profile.edit'));
    }

    /**
     * Display the specified User.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $input = $request->all();
        $user = Auth::user();

        if(!Hash::check($input['old_password'],$user->password)){
            return back()->withErrors(['Old password incorrect'])->withInput($input);

        }
        $user->password =  Hash::make($input['new_password']);
        $user->save();

        Flash::success('Password change successfully.');
        return redirect(route('users.profile.edit'));
    }

    /**
     * Display the API key management page.
     */
    public function apiKey()
    {
        $user = Auth::user();
        
        // Get user's existing API keys (with full access scope)
        $apiKeys = ApiKey::where('user_id', $user->id)
            ->with('scopes')
            ->whereHas('scopes', function($query) {
                $query->where('name', '*');
            })
            ->latest()
            ->get();
        
        return view('users.api_key')->with('user', $user)->with('apiKeys', $apiKeys);
    }

    /**
     * Generate a new API key with full scopes for the authenticated user.
     */
    public function generateApiKey(Request $request)
    {
        $user = Auth::user();
        
        // Check if user already has an active API key with full scopes
        $existingKey = ApiKey::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('scopes', function($query) {
                $query->where('name', '*');
            })
            ->first();
        
        if ($existingKey) {
            Flash::warning('You already have an active API key with full access. Please revoke it first if you want to generate a new one.');
            return redirect(route('users.api_key'));
        }
        
        // Get the full access scope
        $fullAccessScope = ApiScope::where('name', '*')->first();
        
        if (!$fullAccessScope) {
            Flash::error('Full access scope not found. Please run database seeders.');
            return redirect(route('users.api_key'));
        }
        
        // Generate API key with full scopes
        $result = $this->apiKeyService->generateKey(
            $user->id,
            $user->company_id ?? null,
            'Admin API Key - ' . now()->format('Y-m-d H:i:s'),
            config('app.env') === 'production' ? 'live' : 'test',
            [$fullAccessScope->id],
            'enterprise' // Highest tier for admin users
        );
        
        // Store the plain key in session to display once
        session()->flash('new_api_key', $result['plain_key']);
        Flash::success('API Key generated successfully! Make sure to copy it now as it will not be shown again.');
        
        // If there was an intended URL, show a button to go there
        if (session('url.intended')) {
            session()->flash('redirect_url', session('url.intended'));
            session()->forget('url.intended');
        }
        
        return redirect(route('users.api_key'));
    }

    /**
     * Revoke (delete) an API key.
     */
    public function revokeApiKey(string $id)
    {
        $user = Auth::user();
        
        // Find the API key and verify it belongs to the user
        $apiKey = ApiKey::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$apiKey) {
            Flash::error('API Key not found or you do not have permission to delete it.');
            return redirect(route('users.api_key'));
        }
        
        // Soft delete the API key
        $apiKey->delete();
        
        Flash::success('API Key has been revoked successfully.');
        return redirect(route('users.api_key'));
    }
}
