<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSystemLegerRequest;
use App\Http\Requests\UpdateSystemLegerRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\SystemLedger;
use App\Models\Wallet;
use App\Repositories\SystemLegerRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SystemLedgerController extends AppBaseController
{
    /** @var SystemLegerRepository $systemLegerRepository*/
    private $systemLegerRepository;

    public function __construct(SystemLegerRepository $systemLegerRepo)
    {
        $this->systemLegerRepository = $systemLegerRepo;
    }

    /**
     * Display a listing of the SystemLeger.
     */
    public function index(Request $request)
    {
        $systemLegers = $this->systemLegerRepository->paginate(10);

        return view('system_legers.index')
            ->with('systemLegers', $systemLegers);
    }

    /**
     * Show the form for creating a new SystemLeger.
     */
    public function create()
    {
        return view('system_legers.create');
    }

    /**
     * Store a newly created SystemLeger in storage.
     */
    public function store(CreateSystemLegerRequest $request)
    {
        $input = $request->all();

        $systemLeger = $this->systemLegerRepository->create($input);

        Flash::success('System Leger saved successfully.');

        return redirect(route('system-legers.index'));
    }

    /**
     * Display the specified SystemLeger.
     */
    public function show($id)
    {
        $systemLeger = $this->systemLegerRepository->find($id);

        if (empty($systemLeger)) {
            Flash::error('System Leger not found');

            return redirect(route('system-legers.index'));
        }

        return view('system_legers.show')->with('systemLeger', $systemLeger);
    }

    /**
     * Show the form for editing the specified SystemLeger.
     */
    public function edit($id)
    {
        $systemLeger = $this->systemLegerRepository->find($id);

        if (empty($systemLeger)) {
            Flash::error('System Leger not found');

            return redirect(route('system-legers.index'));
        }

        return view('system_legers.edit')->with('systemLeger', $systemLeger);
    }

    /**
     * Update the specified SystemLeger in storage.
     */
    public function update($id, UpdateSystemLegerRequest $request)
    {
        $systemLeger = $this->systemLegerRepository->find($id);

        if (empty($systemLeger)) {
            Flash::error('System Leger not found');

            return redirect(route('system-legers.index'));
        }
        $data = $request->all();
        $validator = Validator::make($data, [

            'name' => [Rule::unique('App\Models\SystemLedger')->ignore($systemLeger->id)],
        ]);
        if ($validator->fails()) {
            return redirect(route('system-legers.edit',$systemLeger->id))
                ->withErrors($validator)
                ->withInput();
        }

        $systemLeger = $this->systemLegerRepository->update($request->all(), $id);

        Flash::success('System Leger updated successfully.');

        return redirect(route('system-legers.index'));
    }

    /**
     * Remove the specified SystemLeger from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $systemLeger = $this->systemLegerRepository->find($id);

        if (empty($systemLeger)) {
            Flash::error('System Leger not found');

            return redirect(route('system-legers.index'));
        }

        $wallets = Wallet::whereUserId($systemLeger->id)->where("user_type",SystemLedger::class)->get();

        foreach ($wallets as $wallet){
            if($wallet->balance >0){
                Flash::error('Could not delete larger because its has fund');

                return redirect(route('system-legers.index'));
            }
        }

        $this->systemLegerRepository->delete($id);

        Flash::success('System Leger deleted successfully.');

        return redirect(route('system-legers.index'));
    }
}
