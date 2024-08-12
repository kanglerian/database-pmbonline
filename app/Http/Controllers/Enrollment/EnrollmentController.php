<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Mail\EnrollmentConfirmationMail;
use App\Models\Applicant;
use App\Models\StatusApplicantsEnrollment;
use App\Models\StatusApplicantsRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $total =  StatusApplicantsEnrollment::count();
        return view('pages.payment.enrollment.index')->with([
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
        $enrollmentQuery = StatusApplicantsEnrollment::query();
        $enrollmentQuery->with('applicant');

        $dateVal = request('date', 'all');
        $pmbVal = request('pmbVal', 'all');
        $repaymentVal = request('repaymentVal', 'all');
        $registerVal = request('registerVal', 'all');
        $registerEndVal = request('registerEndVal', 'all');

        if ($dateVal !== 'all') {
            $enrollmentQuery->where('date', $dateVal);
        }
        if ($pmbVal !== 'all') {
            $enrollmentQuery->where('pmb', $pmbVal);
        }
        if ($repaymentVal !== 'all') {
            $enrollmentQuery->where('repayment', $repaymentVal);
        }
        if ($registerVal !== 'all') {
            $enrollmentQuery->where('register', $registerVal);
        }
        if ($registerEndVal !== 'all') {
            $enrollmentQuery->where('register_end', $registerEndVal);
        }

        $enrollments = $enrollmentQuery->orderByDesc('created_at')->get();

        return response()->json(['enrollments' => $enrollments]);
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
            'receipt' => ['required', 'min:5', 'max:10', 'unique:status_applicants_enrollment'],
            'register' => ['required'],
            'register_end' => ['required'],
            'nominal' => ['required'],
            'session' => ['required']
        ],[
            'pmb.required' => 'Oops! Kolom PMB gak boleh kosong.',
            'date.required' => 'Oops! Kolom Tanggal gak boleh kosong,',
            'receipt.required' => 'Oops! Kolom No. Kwitansi gak boleh kosong,',
            'receipt.min' => 'No. Kwitansi harus memiliki setidaknya :min karakter. Nambahin dikit dong datanya!',
            'receipt.max' => 'No. Kwitansi gak boleh lebih dari :max karakter. Coba dikit lagi ya, jangan kebanyakan!',
            'receipt.unique' => 'Oops! No. Kwitansi ini udah dipake yang lain, coba pake yang beda ya.',
            'register.required' => 'Oops! Kolom Keterangan gak boleh kosong.',
            'register_end.required' => 'Oops! Kolom Keterangan Daftar gak boleh kosong.',
            'nominal.required' => 'Oops! Kolom Nominal Daftar gak boleh kosong.',
            'session.required' => 'Oops! Kolom Gelombang gak boleh kosong.',
        ]);

        $data_enrollment = [
            'pmb' => $request->input('pmb'),
            'date' => $request->input('date'),
            'identity_user' => $request->input('identity_user'),
            'receipt' => $request->input('receipt'),
            'register' => $request->input('register'),
            'register_end' => $request->input('register_end'),
            'nominal' => (int) str_replace('.', '', $request->input('nominal')),
            'repayment' => $request->input('repayment'),
            'debit' => (int) str_replace('.', '', $request->input('debit')),
            'session' => $request->input('session'),
        ];

        $applicant = Applicant::where('identity', $request->input('identity_user'))->first();
        $data_applicant = [
            'is_daftar' => 1,
        ];

        $applicant->update($data_applicant);

        StatusApplicantsEnrollment::create($data_enrollment);
        $data = [
            'receipt' => $request->input('receipt'),
            'name' => $applicant->name,
            'school' => $applicant->schoolapplicant->name,
            'major' => $applicant->major,
            'year' => $applicant->year,
            'phone' => $applicant->phone,
            'email' => $applicant->email,
            'presenter' => $applicant->presenter->name,
        ];
        Mail::to($applicant->email)->send(new EnrollmentConfirmationMail($data));
        return back()->with('message', 'Data daftar berhasil ditambahkan, notifikasi email sudah terkirim!');
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
            'receipt' => [
                'required',
                'min:5',
                'max:10',
                Rule::unique('status_applicants_enrollment')->ignore($id, 'id'),
            ],
            'register' => ['required'],
            'register_end' => ['required'],
            'nominal' => ['required'],
            'session' => ['required']
        ],[
            'pmb.required' => 'Oops! Kolom PMB gak boleh kosong.',
            'date.required' => 'Oops! Kolom Tanggal gak boleh kosong,',
            'receipt.required' => 'Oops! Kolom No. Kwitansi gak boleh kosong,',
            'receipt.min' => 'No. Kwitansi harus memiliki setidaknya :min karakter. Nambahin dikit dong datanya!',
            'receipt.max' => 'No. Kwitansi gak boleh lebih dari :max karakter. Coba dikit lagi ya, jangan kebanyakan!',
            'receipt.unique' => 'Oops! No. Kwitansi ini udah dipake yang lain, coba pake yang beda ya.',
            'register.required' => 'Oops! Kolom Keterangan gak boleh kosong.',
            'register_end.required' => 'Oops! Kolom Keterangan Daftar gak boleh kosong.',
            'nominal.required' => 'Oops! Kolom Nominal Daftar gak boleh kosong.',
            'session.required' => 'Oops! Kolom Gelombang gak boleh kosong.',
        ]);

        $enrollment = StatusApplicantsEnrollment::findOrFail($id);

        $data_enrollment = [
            'pmb' => $request->input('pmb'),
            'date' => $request->input('date'),
            'identity_user' => $request->input('identity_user'),
            'receipt' => $request->input('receipt'),
            'register' => $request->input('register'),
            'register_end' => $request->input('register_end'),
            'nominal' => (int) str_replace('.', '', $request->input('nominal')),
            'repayment' => $request->input('repayment'),
            'debit' => (int) str_replace('.', '', $request->input('debit')),
            'session' => $request->input('session'),
        ];

        $enrollment->update($data_enrollment);

        return back()->with('message', 'Data daftar berhasil diubah!');
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
            $enrollment = StatusApplicantsEnrollment::findOrFail($id);
            $registration = StatusApplicantsRegistration::where('identity_user', $enrollment->identity_user)->first();
            if(!$registration){
                $data = [
                    'is_daftar' => 0,
                ];
                $applicant = Applicant::where('identity', $enrollment->identity_user)->first();
                $applicant->update($data);
                $enrollment->delete();
                $message = [
                    'status' => 'message',
                    'message' => 'Data pendaftaran berhasil dihapus!'
                ];
            } else {

                $message = [
                    'status' => 'error',
                    'message' => 'Tidak bisa menghapus pendaftaran. Hapus data registrasi terlebih dahulu!'
                ];
            }
            return session()->flash($message['status'], $message['message']);
        } catch (\Throwable $th) {
            $errorMessage = 'Terjadi sebuah kesalahan. Perika koneksi anda.';
            return back()->with('error', $errorMessage);
        }
    }
}
