<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationConfirmationMail;
use App\Models\Applicant;
use App\Models\StatusApplicantsRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $total =  StatusApplicantsRegistration::count();
        return view('pages.payment.registration.index')->with([
            'total' => $total,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function get_all()
    {
        $registrationQuery = StatusApplicantsRegistration::query();
        $registrationQuery->with('applicant');

        $dateVal = request('date', 'all');
        $pmbVal = request('pmbVal', 'all');
        $sessionVal = request('sessionVal', 'all');
        $percentVal = request('percentVal', 'all');

        if ($dateVal !== 'all') {
            $registrationQuery->where('date', $dateVal);
        }
        if ($pmbVal !== 'all') {
            $registrationQuery->where('pmb', $pmbVal);
        }
        if ($sessionVal !== 'all') {
            $registrationQuery->where('session', $sessionVal);
        }
        if ($percentVal !== 'all') {
            $registrationQuery->whereRaw('nominal < (deal * ' . $percentVal . ')');
        }

        $registrations = $registrationQuery->orderByDesc('created_at')->get();

        return response()->json(['registrations' => $registrations]);
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
            'pmb' => ['required'],
            'date' => ['required'],
            'identity_user' => ['required'],
            'nominal' => ['required'],
            'deal' => ['required'],
            'discount' => ['required'],
            'session' => ['required'],
        ],[
            'pmb.required' => 'Oops! Kolom PMB gak boleh kosong.',
            'date.required' => 'Oops! Kolom Tanggal gak boleh kosong,',
            'nominal.required' => 'Oops! Kolom Nominal Registrasi gak boleh kosong,',
            'deal.required' => 'Oops! Kolom Harga Deal gak boleh kosong.',
            'discount.required' => 'Oops! Kolom Diskon gak boleh kosong.',
            'session.required' => 'Oops! Kolom Gelombang gak boleh kosong.',
        ]);

        $data = [
            'pmb' => $request->input('pmb'),
            'date' => $request->input('date'),
            'identity_user' => $request->input('identity_user'),
            'nominal' => (int) str_replace('.', '', $request->input('nominal')),
            'deal' => (int) str_replace('.', '', $request->input('deal')),
            'discount' => (int) str_replace('.', '', $request->input('discount')),
            'desc_discount' => $request->input('desc_discount'),
            'session' => $request->input('session'),
        ];

        $applicant = Applicant::where('identity', $request->input('identity_user'))->first();

        $data_applicant = [
            'is_register' => 1,
        ];

        $reset_password = [
            'password' => Hash::make($applicant->phone)
        ];

        $user = User::where('identity', $request->input('identity_user'))->first();

        $applicant->update($data_applicant);
        $user->update($reset_password);

        StatusApplicantsRegistration::create($data);
        $data = [
            'name' => $applicant->name,
            'program' => $applicant->program,
            'school' => $applicant->schoolapplicant->name,
            'major' => $applicant->major,
            'year' => $applicant->year,
            'phone' => $applicant->phone,
            'email' => $applicant->email,
            'password' => $applicant->phone,
            'presenter' => $applicant->presenter->name,
        ];
        Mail::to($applicant->email)->send(new RegistrationConfirmationMail($data));
        return back()->with('message', 'Data registrasi berhasil ditambahkan, notifikasi email sudah terkirim!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $request->validate([
            'pmb' => ['required'],
            'date' => ['required'],
            'identity_user' => ['required'],
            'nominal' => ['required'],
            'deal' => ['required'],
            'discount' => ['required'],
            'session' => ['required'],
        ],[
            'pmb.required' => 'Oops! Kolom PMB gak boleh kosong.',
            'date.required' => 'Oops! Kolom Tanggal gak boleh kosong,',
            'nominal.required' => 'Oops! Kolom Nominal Registrasi gak boleh kosong,',
            'deal.required' => 'Oops! Kolom Harga Deal gak boleh kosong.',
            'discount.required' => 'Oops! Kolom Diskon gak boleh kosong.',
            'session.required' => 'Oops! Kolom Gelombang gak boleh kosong.',
        ]);

        $data = [
            'pmb' => $request->input('pmb'),
            'date' => $request->input('date'),
            'identity_user' => $request->input('identity_user'),
            'nominal' => (int) str_replace('.', '', $request->input('nominal')),
            'deal' => (int) str_replace('.', '', $request->input('deal')),
            'discount' => (int) str_replace('.', '', $request->input('discount')),
            'desc_discount' => $request->input('desc_discount'),
            'session' => $request->input('session'),
        ];

        $registration = StatusApplicantsRegistration::findOrFail($id);
        $registration->update($data);
        return back()->with('message', 'Data registrasi berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $registration = StatusApplicantsRegistration::findOrFail($id);
            $data = [
                'is_register' => 0,
            ];
            $applicant = Applicant::where('identity', $registration->identity_user)->first();
            $applicant->update($data);
            $registration->delete();
            return session()->flash('message', 'Data registrasi berhasil dihapus!');
        } catch (\Throwable $th) {
            $errorMessage = 'Terjadi sebuah kesalahan. Perika koneksi anda.';
            return back()->with('error', $errorMessage);
        }
    }
}
