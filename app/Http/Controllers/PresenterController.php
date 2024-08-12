<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\StatusApplicantsRegistration;
use App\Models\TargetDatabase;
use App\Models\TargetRevenue;
use App\Models\TargetVolume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class PresenterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $total = User::where('role', 'P')->count();
        $presenters = User::where(['role' => 'P'])->paginate(5);
        return view('pages.presenter.index')->with([
            'total' => $total,
            'presenters' => $presenters
        ]);
    }

    public function get_all()
    {
        $presenters = User::where(['role' => 'P', 'status' => '1'])->get();
        return response()
            ->json([
                'presenters' => $presenters,
            ])
            ->header('Content-Type', 'application/json');
    }

    public function get_target()
    {
        $registrationQuery = StatusApplicantsRegistration::query();
        $registrationQuery->with('applicant');
        $targetQuery = TargetVolume::query();

        $identityVal = request('identityVal');
        $pmbVal = request('pmbVal', 'all');
        $sessionVal = request('sessionVal', 'all');
        $dateVal = request('dateVal', 'all');

        $targetQuery->where('identity_user', $identityVal);

        $registrationQuery->whereHas('applicant', function ($query) use ($identityVal) {
            $query->where('identity_user', $identityVal);
        });

        if ($pmbVal !== 'all') {
            $targetQuery->where('pmb', $pmbVal);
            $registrationQuery->where('pmb', $pmbVal);
        }

        if ($sessionVal !== 'all') {
            $targetQuery->where('session', $sessionVal);
            $registrationQuery->where('session', $sessionVal);
        }


        if ($dateVal !== 'all') {
            $targetQuery->where('date', $dateVal);
            $registrationQuery->where('date', $dateVal);
        }

        $targets = $targetQuery->get();
        $registrations = $registrationQuery->get();
        return response()->json(['targets' => $targets, 'registrations' => $registrations]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.presenter.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'not_in:Pilih gender'],
            'email' => ['required', 'unique:users', 'max:255'],
            'phone' => ['required', 'string', 'unique:users', 'max:15'],
            'role' => ['string'],
            'status' => ['string'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $data = [
            'identity' => mt_rand(1, 1000000000),
            'name' => ucwords(strtolower($request->input('name'))),
            'gender' => $request->input('gender'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'role' => 'P',
            'status' => '1',
        ];

        User::create($data);

        return back()->with('message', 'Data presenter berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $presenter = User::findOrFail($id);
        $targets = TargetVolume::where(['identity_user' => $presenter->identity])->get();
        return view('pages.presenter.show')->with([
            'presenter' => $presenter,
            'targets' => $targets
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $presenter = User::findOrFail($id);
        return view('pages.presenter.edit')->with([
            'presenter' => $presenter,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $presenter = User::findOrFail($id);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'not_in:Pilih gender'],
            'email' => ['required', 'max:255', Rule::unique('users')->ignore($id)],
            'phone' => ['required', 'string', 'max:15', Rule::unique('users')->ignore($id)],
            'role' => ['string'],
            'status' => ['string', 'not_in:Pilih status'],
        ]);
        $data = [
            'name' => ucwords(strtolower($request->input('name'))),
            'gender' => $request->input('gender'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role' => 'P',
            'status' => $request->input('status'),
        ];
        $presenter->update($data);
        return back()->with('message', 'Data presenter berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $presenter = User::findOrFail($id);
        $presenter->delete();
        return back()->with('message', 'Data presenter berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function status($id)
    {
        $presenter = User::findOrFail($id);
        $data = [
            'status' => $presenter->status == 0 ? 1 : 0,
        ];
        $presenter->update($data);
        return back()->with('message', 'Status presenter berhasil diubah!');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function change_password(Request $request, $id)
    {
        $presenter = User::findOrFail($id);
        $request->validate([
            'password' => ['required', 'min:8', 'confirmed'],
        ]);
        $data = [
            'password' => Hash::make($request->input('password')),
        ];
        $presenter->update($data);
        return back()->with('message', 'Password berhasil diubah!');
    }

    public function sales_volume($id)
    {
        $presenter = User::findOrFail($id);
        $targets = TargetVolume::where(['identity_user' => $presenter->identity])->get();
        return view('pages.presenter.sales.volume.index')->with([
            'presenter' => $presenter,
            'targets' => $targets
        ]);
    }

    public function sales_revenue($id)
    {
        $presenter = User::findOrFail($id);
        $targets = TargetRevenue::where(['identity_user' => $presenter->identity])->get();
        return view('pages.presenter.sales.revenue.index')->with([
            'presenter' => $presenter,
            'targets' => $targets
        ]);
    }


    public function sales_database($id)
    {
        $presenter = User::findOrFail($id);
        $targets = TargetDatabase::where(['identity_user' => $presenter->identity])->get();
        return view('pages.presenter.sales.database.index')->with([
            'presenter' => $presenter,
            'targets' => $targets
        ]);
    }
}
