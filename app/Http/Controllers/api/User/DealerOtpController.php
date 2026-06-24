<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmailService;
use Carbon\Carbon;
use DB;
use Validator;

class DealerOtpController extends Controller
{
    // =========================================================================
    // API 1 — SEND OTP
    // POST /api/executive/dealer-otp/send
    //
    // Payload (send one of these):
    //   email   string  — send OTP via email
    //   mobile  string  — send OTP via SMS
    //
    // Checks dealer exists with that email/mobile before sending.
    // =========================================================================
    public function send(Request $request)
    {
        // ── Validate — exactly one of email or mobile ─────────────────────────
        $validator = Validator::make($request->all(), [
            'email'  => 'nullable|email|max:191|required_without:mobile',
            'mobile' => 'nullable|string|max:15|required_without:email',
        ]);

        if ($validator->fails()) {
            return response()->json(apiErrorResponse($validator->errors()->first()), 422);
        }

        if ($request->filled('email') && $request->filled('mobile')) {
            return response()->json(apiErrorResponse('Please send either email or mobile, not both.'), 422);
        }

        // ── Determine identifier type & value ─────────────────────────────────
        if ($request->filled('email')) {
            $identifierType = 'email';
            $identifier     = strtolower(trim($request->input('email')));
        } else {
            $identifierType = 'mobile';
            $identifier     = trim($request->input('mobile'));
        }

        // ── Block if dealer already registered ───────────────────────────────────────────
        // OTP is for NEW dealer verification before evaluation form submission.
        // If already registered → block. If not found → allow OTP to proceed.
        if ($identifierType === 'email') {
            $alreadyExists = DB::table('dealers')
                ->where('email', $identifier)
                ->where('is_delete', 0)
                ->exists();
        } else {
            $alreadyExists = DB::table('dealers')
                ->where('owner_mobile', $identifier)
                ->where('is_delete', 0)
                ->exists();
        }

        if ($alreadyExists) {
            return response()->json(
                apiErrorResponse('A dealer with this ' . $identifierType . ' is already registered.'),
                422
            );
        }

        // ── Generate OTP ──────────────────────────────────────────────────────
        $otp       = (string) rand(100000, 999999);
        //$otp       = 123456;
        $expiresAt = Carbon::now()->addMinutes(10);
        $now       = Carbon::now()->toDateTimeString();

        // ── Delete any previous OTPs for same identifier (clean slate) ─────────
        DB::table('dealer_otps')
            ->where('identifier', $identifier)
            ->where('identifier_type', $identifierType)
            ->delete();

        // ── Insert new OTP ────────────────────────────────────────────────────
        DB::table('dealer_otps')->insert([
            'identifier'      => $identifier,
            'identifier_type' => $identifierType,
            'otp'             => $otp,
            'expires_at'      => $expiresAt->toDateTimeString(),
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        // ── Send OTP ──────────────────────────────────────────────────────────
        if ($identifierType === 'email') {
            //$identifier =  "bhupinder.rtpl@gmail.com";
            // Send via email
            EmailService::send('dealer_otp_employee', [
                'otp'        => $otp,
                'identifier' => $identifier,
                'expiresIn'  => '10 minutes',
            ], [$identifier]);

        } else {
            // Send via SMS
            $params            = [];
            $params['mobile']  = $identifier;
            $params['message'] = "To create your business profile, please share mobile verification code ".$otp." with our representative assisting you. - Greenwave Global Ltd.";
            sendSms($params);
        }

        return response()->json(apiSuccessResponse('OTP sent successfully.', [
            'identifier_type' => $identifierType,
            'identifier'      => $identifier,
            'expires_in'      => '10 minutes',
        ]), 200);
    }

    // =========================================================================
    // API 2 — VERIFY OTP
    // POST /api/executive/dealer-otp/verify
    //
    // Payload:
    //   email   string  — same email used in send
    //   mobile  string  — same mobile used in send
    //   otp     string  — 6 digit OTP   required
    // =========================================================================
    public function verify(Request $request)
    {
        // ── Validate ──────────────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'email'  => 'nullable|email|max:191|required_without:mobile',
            'mobile' => 'nullable|string|max:15|required_without:email',
            'otp'    => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(apiErrorResponse($validator->errors()->first()), 422);
        }

        if ($request->filled('email') && $request->filled('mobile')) {
            return response()->json(apiErrorResponse('Please send either email or mobile, not both.'), 422);
        }

        // ── Determine identifier ──────────────────────────────────────────────
        if ($request->filled('email')) {
            $identifierType = 'email';
            $identifier     = strtolower(trim($request->input('email')));
        } else {
            $identifierType = 'mobile';
            $identifier     = trim($request->input('mobile'));
        }

        $inputOtp = trim($request->input('otp'));

        // ── Find latest unused OTP for this identifier ────────────────────────
        $otpRecord = DB::table('dealer_otps')
            ->where('identifier', $identifier)
            ->where('identifier_type', $identifierType)
            ->orderBy('id', 'DESC')
            ->first();

        // ── Not found ─────────────────────────────────────────────────────────
        if (!$otpRecord) {
            return response()->json(apiErrorResponse('No OTP found. Please request a new OTP.'), 422);
        }

        // ── Expired ───────────────────────────────────────────────────────────
        if (Carbon::now()->gt(Carbon::parse($otpRecord->expires_at))) {
            return response()->json(apiErrorResponse('OTP has expired. Please request a new OTP.'), 422);
        }

        // ── Wrong OTP ─────────────────────────────────────────────────────────
        if ($otpRecord->otp !== $inputOtp) {
            return response()->json(apiErrorResponse('Invalid OTP. Please try again.'), 422);
        }

        // ── Delete OTP after successful verification ─────────────────────────
        DB::table('dealer_otps')
            ->where('id', $otpRecord->id)
            ->delete();

        // ── Fetch dealer for response ─────────────────────────────────────────
        if ($identifierType === 'email') {
            $dealer = DB::table('dealers')
                ->where('email', $identifier)
                ->where('is_delete', 0)
                ->first();
        } else {
            $dealer = DB::table('dealers')
                ->where('owner_mobile', $identifier)
                ->where('is_delete', 0)
                ->first();
        }

        return response()->json(apiSuccessResponse('OTP verified successfully.', [
            'identifier_type' => $identifierType,
            'identifier'      => $identifier,
            'dealer'          => $dealer,
        ]), 200);
    }
}