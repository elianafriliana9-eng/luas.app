<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        $anggota = Anggota::where('nik', $request->nik)->first();

        if (!$anggota || !Hash::check($request->password, $anggota->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'NIK atau Password yang Anda masukkan salah.',
            ], 401);
        }

        if ($anggota->status !== 'aktif') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda sedang tidak aktif. Silakan hubungi pengurus.',
            ], 403);
        }

        // Revoke existing tokens for fresh login
        $anggota->tokens()->delete();

        // Issue new token
        $token = $anggota->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil!',
            'data' => [
                'token' => $token,
                'anggota' => [
                    'no_anggota' => $anggota->no_anggota,
                    'nama_lengkap' => $anggota->nama_lengkap,
                    'nik' => $anggota->nik,
                ]
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout.',
        ], 200);
    }
}
