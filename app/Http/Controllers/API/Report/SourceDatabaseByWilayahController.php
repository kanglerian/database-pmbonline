<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use App\Models\Report\SourceDatabaseByWilayah;
use Illuminate\Http\Request;

class SourceDatabaseByWilayahController extends Controller
{
    public function get_all() {
        $databaseQuery = SourceDatabaseByWilayah::query();

        $pmbVal = request('pmbVal', 'all');
        $identityVal = request('identityVal', 'all');
        $roleVal = request('roleVal', 'all');

        if ($pmbVal !== 'all') {
            $databaseQuery->where('pmb', $pmbVal);
        }

        if ($roleVal === 'P') {
            $databaseQuery->where('identity_user', $identityVal);
        }

        $databases = $databaseQuery->get();
        return response()->json([
            'databases' => $databases
        ]);
    }
}
