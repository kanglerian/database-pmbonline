<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantBySourceDaftarId;
use App\Models\ApplicantBySourceId;
use App\Models\ProgramType;
use App\Models\Report\RegisterBySchool;
use App\Models\Report\RegisterBySchoolYear;
use App\Models\Report\RegisterBySource;
use App\Models\StatusApplicantsRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserUpload;
use App\Models\FileUpload;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userupload = UserUpload::where(['identity_user' => Auth::user()->identity, 'fileupload_id' => 11])->get();
        $fileupload = FileUpload::where('namefile', 'bukti-pembayaran')->get();

        $databaseQuery = Applicant::query();
        $applicantQuery = Applicant::query();
        $enrollmentQuery = Applicant::query();
        $registrasiQuery = Applicant::query();
        $schoolarshipQuery = Applicant::query();
        $sourcesIdQuery = ApplicantBySourceId::query();
        $sourcesIdEnrollmentQuery = ApplicantBySourceDaftarId::query();

        $account = false;
        $applicant = false;

        if (Auth::user()->role == 'S') {
            $register = StatusApplicantsRegistration::where('identity_user', Auth::user()->identity)->first();
            $applicant = Applicant::where('identity', Auth::user()->identity)->first();
            if ($register) {
                $account = true;
            }
        }

        $slepets = School::where(['region' => 'TIDAK DIKETAHUI'])
            ->orWhereNull('name')
            ->orWhereNull('region')
            ->orWhereNull('type')
            ->orWhereNull('status')
            ->count();

        if (Auth::user()->role === 'P') {
            $databaseQuery->where('identity_user', Auth::user()->identity);
            $applicantQuery->where('identity_user', Auth::user()->identity);
            $enrollmentQuery->where('identity_user', Auth::user()->identity);
            $registrasiQuery->where('identity_user', Auth::user()->identity);
            $schoolarshipQuery->where('identity_user', Auth::user()->identity);
        }
        $sourcesIdEnrollmentQuery->where('source_daftar_id', '!=', null);

        $databaseCount = $databaseQuery->count();
        $applicantCount = $applicantQuery->where('is_applicant', 1)->count();
        $enrollmentCount = $enrollmentQuery->where('is_daftar', 1)->count();
        $registrationCount = $registrasiQuery->where('is_register', 1)->count();
        $schoolarshipCount = $schoolarshipQuery->where('schoolarship', 1)->count();
        $sourcesIdCount = $sourcesIdQuery->with('SourceSetting')->get();
        $sourcesIdEnrollmentCount = $sourcesIdEnrollmentQuery->with('SourceDaftarSetting')->get();

        $databasesAdminstratorCount = Applicant::where('identity_user', '6281313608558')
            ->whereNotIn('source_id', [11])
            ->whereNotIn('source_daftar_id', [11])
            ->count();

        $databasesAdministrator = Applicant::where('identity_user', '6281313608558')
            ->with(['SourceSetting', 'SourceDaftarSetting', 'ApplicantStatus', 'ProgramType', 'SchoolApplicant', 'FollowUp', 'father', 'mother', 'presenter'])
            ->whereNotIn('source_id', [11])
            ->whereNotIn('source_daftar_id', [11])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('pages.dashboard.index')->with([
            'userupload' => $userupload,
            'fileupload' => $fileupload,
            // Database
            'databaseCount' => $databaseCount,
            // Applicant
            'applicantCount' => $applicantCount,
            'registrationCount' => $registrationCount,
            'schoolarshipCount' => $schoolarshipCount,
            'enrollmentCount' => $enrollmentCount,
            'sourcesIdCount' => $sourcesIdCount,
            'sourcesIdEnrollmentCount' => $sourcesIdEnrollmentCount,
            'databasesAdminstratorCount' => $databasesAdminstratorCount,
            'databasesAdministrator' => $databasesAdministrator,
            // School
            'slepets' => $slepets,
            // Account
            'account' => $account,
            'applicant' => $applicant
        ]);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_all()
    {
        // try {
            $databaseQuery = Applicant::query();
            $applicantQuery = Applicant::query();
            $enrollmentQuery = Applicant::query();
            $registrasiQuery = Applicant::query();
            $schoolarshipQuery = Applicant::query();
    
            $identityVal = request('identityVal', 'all');
            $pmbVal = request('pmbVal', 'all');
    
            if (Auth::user()->role === 'P') {
                $databaseQuery->where('identity_user', $identityVal);
                $applicantQuery->where('identity_user', $identityVal);
                $enrollmentQuery->where('identity_user', $identityVal);
                $registrasiQuery->where('identity_user', $identityVal);
                $schoolarshipQuery->where('identity_user', $identityVal);
            }
    
            if ($pmbVal !== 'all') {
                $databaseQuery->where('pmb', $pmbVal);
                $applicantQuery->where('pmb', $pmbVal);
                $enrollmentQuery->where('pmb', $pmbVal);
                $registrasiQuery->where('pmb', $pmbVal);
                $schoolarshipQuery->where('pmb', $pmbVal);
            }

            $databaseCount = $databaseQuery->count();
            $applicantCount = $applicantQuery->where('is_applicant', 1)->count();
            $schoolarshipCount = $schoolarshipQuery->where('schoolarship', 1)->count();
            $enrollmentCount = $enrollmentQuery->where('is_daftar', 1)->count();
            $registrationCount = $registrasiQuery->where('is_register', 1)->count();

            return response()->json([
                'database_count' => $databaseCount,
                'schoolarship_count' => $schoolarshipCount,
                'applicant_count' => $applicantCount,
                'enrollment_count' => $enrollmentCount,
                'registration_count' => $registrationCount,
            ]);

            // $data = [
            //     'database_phone' => $databasePhone,
            //     'database_count' => $databaseCount,
            //     'schoolarship_count' => $schoolarshipCount,
            //     'applicant_count' => $applicantCount,
            //     'enrollment_count' => $enrollmentCount,
            //     'registration_count' => $registrationCount,
            // ];
            // dd($data);
        // } catch (\Exception $e) {
        //     // Tangkap ex   ception dan kembalikan respon JSON dengan pesan error
        //     return response()->json([
        //         'error' => 'Terjadi kesalahan saat memproses permintaan.',
        //         'message' => $e->getMessage(),
        //     ], 411); // Status code 500 untuk internal server error
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_presenter()
    {
        $presenters = [];
        if (Auth::user()->role == 'A') {
            $presenters = User::where('role', 'P')->get();
        } elseif (Auth::user()->role == 'P') {
            $presenters = User::where('identity', Auth::user()->identity)->get();
        } elseif (Auth::user()->role == 'K') {
            $presenters = User::where('role', 'P')->get();
        }

        return response()->json([
            'presenters' => $presenters
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_sources($pmb = null)
    {

        $sourcesIdQuery = ApplicantBySourceId::query();
        $sourcesIdCount = $sourcesIdQuery->with('SourceSetting')->get();

        return response()->json(['sources' => $sourcesIdCount]);
    }

    public function get_sources_daftar($pmb = null)
    {

        $sourcesIdEnrollmentQuery = ApplicantBySourceDaftarId::query();
        $sourcesIdEnrollmentQuery->where('source_daftar_id', '!=', null);
        $sourcesIdenrollmentCount = $sourcesIdEnrollmentQuery->with('SourceDaftarSetting')->get();

        return response()->json(['sources' => $sourcesIdenrollmentCount]);
    }

    public function get_presenters($pmb = null)
    {
        $presentersQuery = User::select('users.identity', 'users.name', DB::raw('COUNT(applicants.identity_user) as count'))
            ->leftJoin('applicants', 'users.identity', '=', 'applicants.identity_user')
            ->where('users.role', 'P')
            ->where('users.status', '1');

        if ($pmb !== 'all' && $pmb !== null) {
            $presentersQuery->where('applicants.pmb', $pmb);
        }

        $presentersQuery->groupBy('users.identity', 'users.name');
        $presenters = $presentersQuery->get();

        return response()->json(['presenters' => $presenters]);
    }

    public function quick_search($name = null)
    {
        $applicants = Applicant::with(['SourceSetting', 'SourceDaftarSetting', 'ApplicantStatus', 'ProgramType', 'SchoolApplicant', 'FollowUp', 'father', 'mother', 'presenter'])->where('name', 'like', '%' . $name . '%')->get();
        return response()->json(['applicants' => $applicants]);
    }

    public function quick_search_status()
    {
        $applicantsQuery = Applicant::query();

        $pmbVal = request('pmbVal', 'all');
        $statusApplicant = request('statusApplicant', 'all');

        if (Auth::user()->role === 'P') {
            $applicantsQuery->where('identity_user', Auth::user()->identity);
        }

        if ($statusApplicant !== 'all') {
            switch ($statusApplicant) {
                case 'aplikan':
                    $applicantsQuery->where('is_applicant', 1);
                    break;
                case 'daftar':
                    $applicantsQuery->where('is_daftar', 1);
                    break;
                case 'registrasi':
                    $applicantsQuery->where('is_register', 1);
                    break;
                case 'schoolarship':
                    $applicantsQuery->where('schoolarship', 1);
                    break;
            }
        }

        if ($pmbVal !== 'all') {
            $applicantsQuery->where('pmb', $pmbVal);
        }

        $applicants = $applicantsQuery->with(['SourceSetting', 'SourceDaftarSetting', 'ApplicantStatus', 'ProgramType', 'SchoolApplicant', 'FollowUp', 'father', 'mother', 'presenter'])->orderByDesc('created_at')->get();

        return response()->json(['applicants' => $applicants]);
    }
    public function quick_search_source()
    {
        $applicantsQuery = Applicant::query();

        $pmbVal = request('pmbVal', 'all');
        $source = request('source', 'all');

        if (Auth::user()->role === 'P') {
            $applicantsQuery->where('identity_user', Auth::user()->identity);
        }

        if ($source !== 'all') {
            $applicantsQuery->where('source_daftar_id', $source);
        }

        if ($pmbVal !== 'all') {
            $applicantsQuery->where('pmb', $pmbVal);
        }

        $applicants = $applicantsQuery->with(['SourceSetting', 'SourceDaftarSetting', 'ApplicantStatus', 'ProgramType', 'SchoolApplicant', 'FollowUp', 'father', 'mother', 'presenter'])->orderByDesc('created_at')->get();

        return response()->json(['applicants' => $applicants]);
    }

    public function history_page()
    {
        /* Status: OK */
        return view('pages.dashboard.reports.rekapitulasi-history');
    }

    public function rekapitulasi_database()
    {
        /* Status: OK */
        return view('pages.dashboard.reports.rekapitulasi-database');
    }

    public function rekapitulasi_perolehan_pmb()
    {
        /* Status: OK */
        $program_types = ProgramType::all();
        return view('pages.dashboard.reports.rekapitulasi-perolehan-pmb')->with([
            'program_types' => $program_types,
        ]);
    }

    public function rekapitulasi_register_program()
    {
        /* Status: OK */
        $registers = RegisterBySchool::all();
        return view('pages.dashboard.reports.rekapitulasi-register-program')->with([
            'registers' => $registers,
        ]);
    }

    public function rekapitulasi_aplikan()
    {
        /* Status: OK */
        return view('pages.dashboard.reports.rekapitulasi-aplikan');
    }

    public function rekapitulasi_persyaratan()
    {
        /* Status: OK */
        return view('pages.dashboard.reports.rekapitulasi-persyaratan');
    }

    public function rekapitulasi_history()
    {
        /* Status: OK */
        return view('pages.dashboard.reports.rekapitulasi-history');
    }

    public function rekapitulasi_register_school()
    {
        /* Status: OK */
        $registers = RegisterBySchool::all();
        return view('pages.dashboard.reports.rekapitulasi-register-school')->with([
            'registers' => $registers,
        ]);
    }
    public function rekapitulasi_register_school_year()
    {
        /* Status: OK */
        $registers = RegisterBySchoolYear::all();
        return view('pages.dashboard.reports.rekapitulasi-register-school-year')->with([
            'registers' => $registers,
        ]);
    }

    public function rekapitulasi_register_source()
    {
        /* Status: OK */
        $registers = RegisterBySource::all();
        return view('pages.dashboard.reports.rekapitulasi-register-source')->with([
            'registers' => $registers,
        ]);
    }

    public function rekapitulasi_pencapaian_pmb()
    {
        return view('pages.dashboard.reports.rekapitulasi-pencapaian-pmb');
    }


    public function rekapitulasi_perolehan_pmb_page()
    {
        return view('pages.dashboard.reports.rekapitulasi-perolehan-pmb-page');
    }

}
