<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Client;
use App\Models\Company;
use App\Repositories\UserRepository;
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

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepository = $userRepo;
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
}
