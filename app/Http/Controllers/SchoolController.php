<?php

namespace App\Http\Controllers;


use App\Imports\SchoolsImport;
use App\Models\Applicant;
use App\Models\Report\SchoolBySourceAll;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\School;
use Illuminate\Http\JsonResponse;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $total = School::count();
        $useless = SchoolBySourceAll::where('jumlah', 0)->count();
        $schools_by_region = School::select('region')->groupBy('region')->get();
        $slepets = School::where(['region' => 'TIDAK DIKETAHUI'])
            ->orWhereNull('name')
            ->orWhereNull('region')
            ->orWhereNull('type')
            ->orWhereNull('status')
            ->count();
        return view('pages.schools.index')->with([
            'total' => $total,
            'schools_by_region' => $schools_by_region,
            'slepets' => $slepets,
            'useless' => $useless
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'region' => ['required', 'string'],
        ]);

        $data = [
            'name' => strtoupper($request->input('name')),
            'region' => strtoupper($request->input('region')),
        ];

        School::create($data);
        return back()->with('message', 'Data sekolah berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $school = School::findOrFail($id);
        return view('pages.schools.show')->with([
            'school' => $school,
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
        $school = School::findOrFail($id);
        return view('pages.schools.edit')->with([
            'school' => $school,
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
        $request->validate([
            'name' => ['required'],
            'type' => ['required', 'not_in:Pilih'],
            'status' => ['required', 'not_in:Pilih'],
            'region' => ['required', 'not_in:Pilih'],
            'lat' => ['required','min:7','max:9'],
            'lng' => ['required','min:7','max:9'],
        ], [
            'name.required' => 'Kolom nama sekolah tidak boleh kosong.',
            'type.required' => 'Kolom tipe sekolah tidak boleh kosong.',
            'type.not_in' => 'Pilih tipe sekolah tidak valid.',
            'status.required' => 'Kolom status sekolah tidak boleh kosong.',
            'status.not_in' => 'Pilih status sekolah tidak valid.',
            'region.required' => 'Kolom wilayah sekolah tidak boleh kosong.',
            'region.not_in' => 'Pilih wilayah sekolah tidak valid.',
            'lat.required' => 'Lattitude tidak boleh kosong.',
            'lat.min' => 'Lattitude tidak boleh kurang dari 7.',
            'lat.max' => 'Lattitude tidak boleh lebih dari 8.',
            'lng.required' => 'Longitude tidak boleh kosong.',
            'lng.min' => 'Longitude tidak boleh kurang dari 7.',
            'lng.max' => 'Longitude tidak boleh lebih dari 9.',
        ]);

        $data = [
            'name' => strtoupper($request->input('name')),
            'type' => strtoupper($request->input('type')),
            'status' => strtoupper($request->input('status')),
            'region' => strtoupper($request->input('region')),
            'lat' => $request->input('lat'),
            'lng' => $request->input('lng'),
        ];

        $school = School::findOrFail($id);
        $school->update($data);

        return back()->with('message', 'Data sekolah berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $school = School::findOrFail($id);
        $applicants = Applicant::where('school', $id)->count();
        if ($applicants > 0) {
            return response()->json([
                'message' =>
                    'Gagal menghapus data: Data tersebut masih digunakan di bagian lain dan tidak dapat dihapus.'
            ]);
        } else {
            $school->delete();
            return response()->json([
                'message' => 'Data sumber database berhasil dihapus!'
            ]);
        }

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        Excel::import(new SchoolsImport, $request->file('berkas'));

        return back()->with('message', 'Data sekolah berhasil diimport');
    }

    public function setting()
    {
        $schools = SchoolBySourceAll::all();
        return view('pages.schools.setting')->with([
            'schools' => $schools
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function migration(Request $request)
    {
        $request->validate([
            'school_from' => ['required'],
            'school_to' => ['required'],
        ]);

        Applicant::where('school', $request->input('school_from'))->update(['school' => $request->input('school_to')]);

        return back()->with('message', 'Sekolah aplikan berhasil dimigrasikan!');
    }

    public function clear()
    {
        $school_all = SchoolBySourceAll::where('jumlah', 0)->get();
        foreach ($school_all as $school) {
            $schoolId = $school->id;
            School::findOrFail($schoolId)->delete();
        }
        return back()->with('message', 'Sekolah dengan data 0 berhasil dihapus!');
    }
}
