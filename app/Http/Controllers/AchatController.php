<?php

namespace App\Http\Controllers;

use App\DataTables\AchatDataTable;
use App\Http\Requests\CreateAchatRequest;
use App\Http\Requests\UpdateAchatRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\AchatRepository;
use Illuminate\Http\Request;
use Flash;

class AchatController extends AppBaseController
{
    /** @var AchatRepository $achatRepository*/
    private $achatRepository;

    public function __construct(AchatRepository $achatRepo)
    {
        $this->achatRepository = $achatRepo;
    }

    /**
     * Display a listing of the Achat.
     */
    public function index(AchatDataTable $achatDataTable)
    {

        return $achatDataTable->render('achats.index');

    }

    /**
     * Show the form for creating a new Achat.
     */
    public function create()
    {
        return view('achats.create');
    }

    /**
     * Store a newly created Achat in storage.
     */
    public function store(CreateAchatRequest $request)
    {
        $input = $request->all();

        $achat = $this->achatRepository->create($input);

        Flash::success('Achat saved successfully.');

        return redirect(route('achats.index'));
    }

    /**
     * Display the specified Achat.
     */
    public function show($id)
    {
        $achat = $this->achatRepository->find($id);

        if (empty($achat)) {
            Flash::error('Achat not found');

            return redirect(route('achats.index'));
        }

        return view('achats.show')->with('achat', $achat);
    }

    /**
     * Show the form for editing the specified Achat.
     */
    public function edit($id)
    {
        $achat = $this->achatRepository->find($id);

        if (empty($achat)) {
            Flash::error('Achat not found');

            return redirect(route('achats.index'));
        }

        return view('achats.edit')->with('achat', $achat);
    }

    /**
     * Update the specified Achat in storage.
     */
    public function update($id, UpdateAchatRequest $request)
    {
        $achat = $this->achatRepository->find($id);

        if (empty($achat)) {
            Flash::error('Achat not found');

            return redirect(route('achats.index'));
        }

        $achat = $this->achatRepository->update($request->all(), $id);

        Flash::success('Achat updated successfully.');

        return redirect(route('achats.index'));
    }

    /**
     * Remove the specified Achat from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $achat = $this->achatRepository->find($id);

        if (empty($achat)) {
            Flash::error('Achat not found');

            return redirect(route('achats.index'));
        }

        $this->achatRepository->delete($id);

        Flash::success('Achat deleted successfully.');

        return redirect(route('achats.index'));
    }
}
