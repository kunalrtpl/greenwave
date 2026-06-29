<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Dealer;
use App\DealerOperatingCity;
use DB;
use Session;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PDF; // barryvdh/laravel-dompdf  (facade alias)
use Mpdf\Mpdf;
use App\Services\EmailService;

/**
 * ChannelPartnerController
 *
 * Revamped Dealer / Channel-Partner module — single-page, stage-aware edit.
 *
 *   index()                 GET  /admin/channel-partners
 *   addEdit()               GET  /admin/channel-partners/add | /{id}/edit
 *   save()                  POST /admin/channel-partners/save            (AJAX, action: save|onboard|confirm)
 *   sendOnboardingLink()    POST /admin/channel-partners/{id}/send-link  (AJAX)
 *   onboardingForm()        GET  /onboarding/{token}                     (public)
 *   onboardingSubmit()      POST /onboarding/{token}                     (public)
 *
 * updateStage() retained for backward-compat but stage moves now flow through save().
 */
class ChannelPartnerController extends Controller
{
    // ══════════════════════════════════════════════════════════════════
    //  1. LISTING
    // ══════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        Session::put('active', 'dealers');

        $query = Dealer::whereNull('parent_id')
            ->withCount([
                'customers as customers_count' => function ($q) {
                    $q->where('business_model', 'Dealer')->where('status', 1);
                },
            ])
            ->withCount('linked_products as linked_products_count');

        if ($request->filled('business_name')) {
            $query->where('business_name', 'like', '%' . $request->business_name . '%');
        }
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }
        if ($request->filled('dealer_type')) {
            $query->where('dealer_type', $request->dealer_type);
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $dealers = $query->orderBy('id', 'DESC')
                         ->paginate(25)
                         ->appends($request->except('page'));

        $title = 'Channel Partners';
        return view('admin.channel-partners.index', compact('title', 'dealers'));
    }

    // ══════════════════════════════════════════════════════════════════
    //  2. ADD / EDIT FORM
    // ══════════════════════════════════════════════════════════════════

    public function addEdit(Request $request, $id = null)
    {
        $selAppRoles     = [];
        $selProductTypes = [];
        $linkedCustomers = [];
        $operatingCities = [];
        $selLinkedProids = [];
        $dealerdata      = [];
        $ignoreDealer    = 0;

        if (!empty($id)) {
            $dealerdata = Dealer::with(['linked_products'])
                                ->withCount('linked_products as linked_products_count')
                                ->findOrFail($id);
            $dealerdata = json_decode(json_encode($dealerdata), true);

            $selLinkedProids = array_column($dealerdata['linked_products'] ?? [], 'product_id');
            $selProductTypes = array_unique(array_filter(
                explode(',', $dealerdata['product_types'] ?? ''),
                'strlen'
            ));
            $selAppRoles = array_filter(explode(',', $dealerdata['app_roles'] ?? ''));

            $linkedCustomers = \App\Customer::where('business_model', 'Dealer')
                ->where('dealer_id', $id)
                ->where('status', 1)
                ->select('id', 'name')
                ->get()
                ->toArray();

            // Area of operations = territory = dealer_operating_cities
            $operatingCities = DealerOperatingCity::where('dealer_id', $id)
                ->pluck('city')
                ->toArray();

            $ignoreDealer = $id;
            $title        = 'Edit Channel Partner';
        } else {
            $title = 'Add Channel Partner';
        }

        $otherDealers  = Dealer::where('id', '!=', $ignoreDealer)
                                ->whereNull('parent_id')
                                ->get()
                                ->toArray();

        $parentDealers = Dealer::where('dealer_type', 'dealer')
                                ->whereNull('parent_id')
                                ->where('id', '!=', ($id ?? 0))
                                ->get()
                                ->toArray();

        return view('admin.channel-partners.add-edit', compact(
            'title', 'dealerdata', 'otherDealers', 'selLinkedProids',
            'selProductTypes', 'selAppRoles', 'linkedCustomers',
            'operatingCities', 'parentDealers', 'id'
        ));
    }

    // ══════════════════════════════════════════════════════════════════
    //  3. SAVE (AJAX)  ·  action = save | onboard | confirm
    // ══════════════════════════════════════════════════════════════════

    public function save(Request $request)
    {
        if (!$request->ajax()) {
            abort(405);
        }

        $data        = $request->all();
        $isNew       = empty($data['dealerid']);
        $action      = $data['action'] ?? 'save';
        $isSubDealer = ($data['dealer_type'] ?? '') === 'sub dealer';

        $emailSuffix  = $isNew ? '' : ',' . $data['dealerid'];
        $mobileSuffix = $isNew ? '' : ',' . $data['dealerid'];

        // ── base validation ───────────────────────────────────────────
        $rules = [
            'dealer_type'      => 'bail|required',
            'business_name'    => 'bail|required',
            'name'             => 'bail|required',
            'city'             => 'bail|required',
            //'address'          => 'nullable|required',
            'email'            => 'bail|email|unique:dealers,email' . $emailSuffix,
            'owner_mobile'     => 'bail|required|numeric|digits:10|unique:dealers,owner_mobile' . $mobileSuffix,
            'is_authenticated' => 'bail|nullable|in:0,1',
        ];

        if ($isSubDealer) {
            $rules['linked_dealer_id'] = 'bail|required';
        }

        // ── action-specific gates ─────────────────────────────────────
        if ($action === 'onboard') {
            // Territory is required to move to onboarding (both types)
            $rules['operating_cities'] = 'bail|required|array|min:1';
            if (!$isSubDealer) {
                $rules['payment_term']   = 'bail|required|numeric';
                $rules['security_amount']= 'bail|required';
                $rules['credit_allowed'] = 'bail|required';
            }
        }

        if ($action === 'confirm' && !$isSubDealer) {
            $rules['cp_status']               = 'bail|required|in:provisional,authorized';
            $rules['security_deposit_status'] = 'bail|required|in:received,waived';
        }

        // Document replacements (admin fixing wrong uploads) — optional, validated if present.
        foreach (['doc_gst_certificate', 'doc_pan_card', 'doc_cancelled_cheque', 'doc_visiting_card'] as $docField) {
            if ($request->hasFile($docField)) {
                $rules[$docField] = 'bail|file|mimes:pdf,jpg,jpeg,png|max:5120';
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if (!$validator->passes()) {
            return response()->json(['status' => false, 'errors' => $validator->messages()]);
        }

        // ── load model ────────────────────────────────────────────────
        $dealer = $isNew ? new Dealer() : Dealer::findOrFail($data['dealerid']);

        // Defaults for a brand-new lead (the Activation fields live in the
        // gated confirmation section, so they aren't posted at evaluation).
        if ($isNew) {
            $dealer->is_authenticated = 1;
            $dealer->status           = 1;
        }

        // ── confirm gate: dealer MUST have submitted the onboarding form ─
        if ($action === 'confirm' && empty($dealer->onboarding_form_submitted)) {
            return response()->json([
                'status'  => false,
                'message' => 'Action Denied: the partner has not submitted the onboarding form yet. Please ask the partner to complete and submit their onboarding form before confirming.',
            ]);
        }

        // ── scalar fields (existing + new) ────────────────────────────
        $scalarFields = [
            'dealer_type', 'business_name', 'short_name', 'name', 'designation',
            'address', 'city', 'office_phone', 'gst_no', 'source_of_lead',
            'owner_mobile', 'email', 'is_authenticated',
            'payment_term', 'basic_discount', 'cd_7days', 'cd_advance',
            'security_amount', 'interest_rate_on_security', 'credit_multiple', 'credit_allowed',
            'show_class', 'status', 'cp_status',
            // stage-3 admin fields
            'security_deposit_status', 'security_deposit_received_amount', 'deposit_credit_details',
            // partner-submitted fields (now admin-editable)
            'business_constitution', 'pan_no', 'billing_address', 'shipping_address',
            'bank_name', 'bank_account_name', 'bank_account_number', 'bank_ifsc',
            'accounts_contact_person', 'accounts_mobile', 'accounts_email',
        ];

        foreach ($scalarFields as $field) {
            if (array_key_exists($field, $data)) {
                $dealer->$field = $data[$field];
            }
        }

        // booleans (checkboxes posted as 0/1 hidden inputs)
        foreach (['gst_checked', 'pan_checked', 'bank_details_checked'] as $bool) {
            if (array_key_exists($bool, $data)) {
                $dealer->$bool = $data[$bool] ? 1 : 0;
            }
        }

        // sub-dealer overrides
        if ($isSubDealer) {
            $dealer->linked_dealer_id = $data['linked_dealer_id'] ?? null;
            $dealer->payment_term     = 0;
            $dealer->basic_discount   = null;
            $dealer->cd_7days         = null;
            $dealer->cd_advance       = null;
        } else {
            $dealer->linked_dealer_id = null;
        }

        // Catalogue & access fields live in the gated confirmation section.
        // Only touch them when that section was actually on screen (submitted),
        // otherwise a plain evaluation-stage Save would wipe them.
        if (isset($data['confirmation_visible'])) {
            $dealer->product_types  = isset($data['product_types'])  ? implode(',', $data['product_types'])  : '';
            $dealer->linked_dealers = isset($data['linked_dealers']) ? implode(',', $data['linked_dealers']) : '';
            $dealer->app_roles      = isset($data['app_roles'])      ? implode(',', $data['app_roles'])      : '';
        }

        // evaluation form upload (optional)
        if ($request->hasFile('evaluation_form_pdf')) {
            $path = public_path('uploads/evaluation/' . ($dealer->id ?: 'tmp'));
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $file = $request->file('evaluation_form_pdf');
            $fn   = 'evaluation_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $fn);
            $dealer->evaluation_form_pdf = 'uploads/evaluation/' . ($dealer->id ?: 'tmp') . '/' . $fn;
        }

        // ── partner document replacements (admin can fix wrong uploads) ──
        //    Files come from the "Submitted by Partner" section. Only relevant
        //    once the dealer exists and isn't a sub dealer.
        if (!$isNew && !$isSubDealer) {
            $docFields  = ['doc_gst_certificate', 'doc_pan_card', 'doc_cancelled_cheque', 'doc_visiting_card'];
            $uploadPath = public_path('uploads/onboarding/' . $dealer->id);
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($docFields as $docField) {
                if ($request->hasFile($docField)) {
                    $file     = $request->file($docField);
                    $filename = $docField . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $dealer->$docField = 'uploads/onboarding/' . $dealer->id . '/' . $filename;
                }
            }
        }

        // ── stage handling ────────────────────────────────────────────
        if ($isNew) {
            $dealer->stage = 'evaluation';
        }
        if ($action === 'onboard') {
            $dealer->stage = 'onboarding';
        }
        if ($action === 'confirm') {
            $dealer->stage        = 'confirmed';
            $dealer->confirmed_at = Carbon::now();
        }

        $dealer->save();

        // ── operating cities (= territory) ────────────────────────────
        DealerOperatingCity::where('dealer_id', $dealer->id)->delete();
        if (!empty($data['operating_cities'])) {
            foreach ($data['operating_cities'] as $city) {
                DealerOperatingCity::create([
                    'dealer_id' => $dealer->id,
                    'city'      => $city,
                ]);
            }
        }

        // ── confirmation email to the dealer (only on a successful confirm) ──
        if ($action === 'confirm') {
            try {
                $this->sendDealerConfirmedEmail($dealer, $isSubDealer);
            } catch (\Throwable $e) {
                \Log::warning('Dealer confirmation email failed for dealer ' . $dealer->id . ': ' . $e->getMessage());
            }
        }

        $msg = $action === 'onboard'  ? 'Moved to Onboarding.'
             : ($action === 'confirm' ? 'Channel Partner confirmed.' : 'Saved successfully.');

        // Always land back on the edit page so the updated sections show.
        return response()->json([
            'status'  => true,
            'message' => $msg,
            'url'     => url('/admin/channel-partners/' . $dealer->id . '/edit?s=1'),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  4. UPDATE STAGE (AJAX) — retained for backward-compat
    // ══════════════════════════════════════════════════════════════════

    public function updateStage(Request $request, $id)
    {
        $dealer = Dealer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'stage' => 'required|in:evaluation,onboarding,confirmed',
        ]);
        if (!$validator->passes()) {
            return response()->json(['status' => false, 'message' => 'Invalid stage']);
        }

        $newStage = $request->stage;

        if ($newStage === 'onboarding') {
            $hasCities = DealerOperatingCity::where('dealer_id', $id)->exists();
            if (!$hasCities) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Please set the Area of Operations (Territory) before moving to Onboarding.',
                ]);
            }
        }

        if ($newStage === 'confirmed' && !$dealer->onboarding_form_submitted) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot confirm — the partner has not yet submitted the onboarding form.',
            ]);
        }

        $dealer->stage = $newStage;
        if ($newStage === 'confirmed') {
            $dealer->confirmed_at = Carbon::now();
        }
        $dealer->save();

        return response()->json(['status' => true, 'message' => 'Stage updated to ' . ucfirst($newStage)]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  5. GENERATE & RETURN ONBOARDING LINK (AJAX)
    // ══════════════════════════════════════════════════════════════════

    public function sendOnboardingLink(Request $request, $id)
    {
        $dealer = Dealer::findOrFail($id);

        if ($dealer->stage !== 'onboarding') {
            return response()->json([
                'status'  => false,
                'message' => 'Partner must be in the Onboarding stage to generate the link.',
            ]);
        }

        $token = Str::random(64);
        $expiresAt = Carbon::now()->addHours(24);

        $dealer->onboarding_token            = $token;
        $dealer->onboarding_token_expires_at = $expiresAt;
        $dealer->save();

        $onboardingLink = url('/onboarding/' . $token);
        //$dealer->email = "mkanum786@gmail.com";
        // Send the email to the dealer
        // Note: Adjust `$dealer->email` if your column name is different
        EmailService::send('dealer_onboarding_link', [
            'dealer'     => $dealer->toArray(),
            'link'       => $onboardingLink,
            'expires_at' => $expiresAt->format('d M Y, h:i A')
        ], $dealer->email);

        return response()->json([
            'status'     => true,
            'link'       => $onboardingLink,
            'expires_at' => $expiresAt->format('d M Y, h:i A'),
            'message'    => 'Link generated and email sent successfully. Valid for 24 hours.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  6. PUBLIC ONBOARDING FORM
    // ══════════════════════════════════════════════════════════════════

    public function onboardingForm($token)
    {
        $dealer = Dealer::where('onboarding_token', $token)
            ->where('onboarding_token_expires_at', '>', Carbon::now())
            ->first();

        if (!$dealer) {
            return view('onboarding.expired');
        }
        if ($dealer->onboarding_form_submitted) {
            return view('onboarding.already-submitted', compact('dealer'));
        }

        $isSubDealer  = $dealer->dealer_type === 'sub dealer';
        $parentDealer = ($isSubDealer && $dealer->linked_dealer_id)
            ? Dealer::find($dealer->linked_dealer_id)
            : null;

        $operatingCities = DealerOperatingCity::where('dealer_id', $dealer->id)
            ->pluck('city')
            ->implode(', ');

        $state = \DB::table('cities')
        ->whereRaw('UPPER(city_name) = ?', [strtoupper(trim($dealer->city))])
        ->value('state_name');

        return view('onboarding.form', compact(
            'dealer', 'token', 'isSubDealer', 'parentDealer', 'operatingCities','state'
        ));
    }

    // ══════════════════════════════════════════════════════════════════
    //  7. PUBLIC ONBOARDING FORM SUBMIT
    // ══════════════════════════════════════════════════════════════════

    public function onboardingSubmit(Request $request, $token)
    {
        $dealer = Dealer::where('onboarding_token', $token)
            ->where('onboarding_token_expires_at', '>', Carbon::now())
            ->first();

        if (!$dealer) {
            return redirect()->back()->with('error', 'This link has expired.');
        }
        if ($dealer->onboarding_form_submitted) {
            return redirect()->back()->with('error', 'This form has already been submitted.');
        }

        $isSubDealer = $dealer->dealer_type === 'sub dealer';

        // ── Validation ────────────────────────────────────────────────
        $rules = [
            'business_name'        => 'required|string|max:191',
            'name'                 => 'required|string|max:191',
            'designation'          => 'required|string|max:191',
            'city'                 => 'required|string|max:191',
            'declaration_accepted' => 'required|accepted',
        ];

        if (!$isSubDealer) {
            $rules = array_merge($rules, [
                'business_constitution' => 'required|string',
                'gst_no'               => 'required|string|max:20',
                'pan_no'               => 'required|string|max:20',
                'billing_address'      => 'required|string',
                'bank_name'            => 'required|string|max:191',
                'bank_account_name'    => 'required|string|max:191',
                'bank_account_number'  => 'required|string|max:50',
                'bank_ifsc'            => 'required|string|max:20',
                'doc_gst_certificate'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'doc_pan_card'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'doc_cancelled_cheque' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'doc_visiting_card'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ── Persist ───────────────────────────────────────────────────
        $dealer->business_name = $request->business_name;
        $dealer->name          = $request->name;
        $dealer->designation   = $request->designation;
        $dealer->city          = $request->city;

        if (!$isSubDealer) {
            $dealer->business_constitution   = $request->business_constitution;
            $dealer->gst_no                  = $request->gst_no;
            $dealer->pan_no                  = $request->pan_no;
            $dealer->address                 = $request->billing_address;
            $dealer->billing_address         = $request->billing_address;
            $sameAsBilling                   = ($request->input('same_as_billing') == 1) ? 1 : 0;
            $dealer->same_as_billing         = $sameAsBilling;
            $dealer->shipping_address        = $sameAsBilling
                                                ? $request->billing_address
                                                : $request->shipping_address;
            $dealer->accounts_contact_person = $request->accounts_contact_person;
            $dealer->accounts_mobile         = $request->accounts_mobile;
            $dealer->accounts_email          = $request->accounts_email;
            $dealer->bank_name               = $request->bank_name;
            $dealer->bank_account_name       = $request->bank_account_name;
            $dealer->bank_account_number     = $request->bank_account_number;
            $dealer->bank_ifsc               = $request->bank_ifsc;

            $uploadPath = public_path('uploads/onboarding/' . $dealer->id);
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ([
                'doc_gst_certificate', 'doc_pan_card', 'doc_cancelled_cheque', 'doc_visiting_card',
            ] as $fileField) {
                if ($request->hasFile($fileField)) {
                    $file     = $request->file($fileField);
                    $filename = $fileField . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $dealer->$fileField = 'uploads/onboarding/' . $dealer->id . '/' . $filename;
                }
            }
        }

        $dealer->declaration_accepted      = 1;
        $dealer->declaration_accepted_at   = Carbon::now();
        $dealer->onboarding_form_submitted = 1;            // <- the "form_filled" flag
        $dealer->onboarding_submitted_at   = Carbon::now();
        $dealer->onboarding_token            = null;       // single-use link
        $dealer->onboarding_token_expires_at = null;
        $dealer->save();

        // ── Attach the submitted form as a PDF (never blocks submission) ─
        //     · Saves a permanent copy in public/uploads (for admin panel view)
        //     · Emails the admin with the PDF attached, then deletes the temp copy
        try {
            $this->sendOnboardingSubmittedEmail($dealer, $isSubDealer);
        } catch (\Throwable $e) {
            \Log::warning('Onboarding submit email/PDF failed for dealer ' . $dealer->id . ': ' . $e->getMessage());
        }

        return view('onboarding.success', compact('dealer', 'isSubDealer'));
    }

    // ══════════════════════════════════════════════════════════════════
    //  8. ONBOARDING — EMAIL ADMIN + ATTACH PDF + CLEANUP  (mPDF)
    // ══════════════════════════════════════════════════════════════════

    /**
     * Called right after a dealer submits the onboarding form.
     *
     *   1. Builds the onboarding PDF with mPDF.
     *   2. Saves a permanent copy under public/uploads so the admin panel can
     *      still link to it later (onboarding_form_pdf column).
     *   3. Writes a temp copy to storage/app/temp_onboarding_pdfs and attaches
     *      it to the admin notification email (event: dealer_onboarding_submitted).
     *   4. Deletes the temp copy after the email is dispatched.
     */
    private function sendOnboardingSubmittedEmail(Dealer $dealer, bool $isSubDealer): void
    {
        $tempPdfFile = null;

        try {
            // ── Territory (area of operations) ────────────────────────
            $territory = DealerOperatingCity::where('dealer_id', $dealer->id)
                ->pluck('city')
                ->implode(', ');

            // ── Build PDF HTML from blade, render with mPDF ───────────
            $pdfHtml = view('onboarding.pdf', [
                'dealer'      => $dealer,
                'isSubDealer' => $isSubDealer,
                'territory'   => $territory,
            ])->render();

            $mpdf = new Mpdf([
                'margin_top'    => 12,
                'margin_bottom' => 12,
                'margin_left'   => 12,
                'margin_right'  => 12,
            ]);
            $mpdf->WriteHTML($pdfHtml);
            $pdfContent = $mpdf->Output('', 'S'); // return as string

            // ── 1) Permanent public copy for the admin panel ─────────
            $publicDir = public_path('uploads/onboarding/' . $dealer->id);
            if (!is_dir($publicDir)) {
                mkdir($publicDir, 0755, true);
            }
            $publicRelative = 'uploads/onboarding/' . $dealer->id . '/onboarding-form.pdf';
            file_put_contents(public_path($publicRelative), $pdfContent);

            $dealer->onboarding_form_pdf = $publicRelative;
            $dealer->save();

            // ── 2) Temp copy for the email attachment ────────────────
            $tempDir = storage_path('app/temp_onboarding_pdfs');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }
            $safeName    = Str::slug($dealer->business_name ?: ('dealer-' . $dealer->id));
            $tempPdfFile = $tempDir . '/onboarding_' . $dealer->id . '_' . time() . '.pdf';
            file_put_contents($tempPdfFile, $pdfContent);

            // ── 3) Email the admin with the PDF attached ─────────────
            $partnerType   = $isSubDealer ? 'Sub Dealer' : 'Primary Dealer';
            $linkedDealer  = null;
            if ($isSubDealer && $dealer->linked_dealer_id) {
                $parent       = Dealer::find($dealer->linked_dealer_id);
                $linkedDealer = $parent ? ($parent->business_name ?: $parent->name) : null;
            }

            $panelUrl = url('/admin/channel-partners/' . $dealer->id . '/edit');

            EmailService::send('dealer_onboarding_submitted', [
                'dealer'       => $dealer->toArray(),
                'partnerType'  => $partnerType,       // "Primary Dealer" | "Sub Dealer"
                'isSubDealer'  => $isSubDealer,
                'linkedDealer' => $linkedDealer,      // parent dealer name for sub dealers
                'territory'    => $territory,
                'submittedAt'  => Carbon::now()->format('d M Y, h:i A'),
                'panelUrl'     => $panelUrl,
                'pdfFileName'  => 'onboarding-' . $safeName . '.pdf',
                '_attachments' => [$tempPdfFile],
            ]); // recipients resolved from the template's to/cc/bcc in DB

        } finally {
            // ── 4) Always delete the temp PDF, success or failure ────
            if (!empty($tempPdfFile) && file_exists($tempPdfFile)) {
                @unlink($tempPdfFile);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  9. PARTNER CONFIRMED — WELCOME EMAIL TO DEALER
    // ══════════════════════════════════════════════════════════════════

    /**
     * Sent to the dealer after admin clicks "Save & Confirm Partner".
     * Uses event key `dealer_confirmed_dealer`. Recipients = the dealer's own
     * email (falls back to template to/cc/bcc if the column is empty).
     */
    private function sendDealerConfirmedEmail(Dealer $dealer, bool $isSubDealer): void
    {
        $territory = DealerOperatingCity::where('dealer_id', $dealer->id)
            ->pluck('city')
            ->implode(', ');

        $partnerType  = $isSubDealer ? 'Sub Dealer' : 'Primary Dealer';
        $cpStatus     = $dealer->cp_status ?: null; // provisional | authorized (primary only)
        $linkedDealer = null;
        if ($isSubDealer && $dealer->linked_dealer_id) {
            $parent       = Dealer::find($dealer->linked_dealer_id);
            $linkedDealer = $parent ? ($parent->business_name ?: $parent->name) : null;
        }

        // Prefer the dealer's real email; if blank, the template recipients are used.
        $to = !empty($dealer->email) ? $dealer->email : null;
        //$to =  "mkanum786@gmail.com";
        EmailService::send('dealer_confirmed_dealer', [
            'dealer'       => $dealer->toArray(),
            'partnerType'  => $partnerType,        // "Primary Dealer" | "Sub Dealer"
            'isSubDealer'  => $isSubDealer,
            'cpStatus'     => $cpStatus,           // "provisional" | "authorized" | null
            'linkedDealer' => $linkedDealer,
            'territory'    => $territory,
            'confirmedAt'  => Carbon::now()->format('d M Y, h:i A'),
            'loginUrl'     => url('/'),            // adjust to your dealer-app login URL
        ], $to);
    }

    // ══════════════════════════════════════════════════════════════════
    //  10. ONBOARDING PDF (dompdf) — retained for backward-compat
    // ══════════════════════════════════════════════════════════════════

    private function generateOnboardingPdf(Dealer $dealer, $isSubDealer)
    {
        $territory = DealerOperatingCity::where('dealer_id', $dealer->id)
            ->pluck('city')->implode(', ');

        $dir = public_path('uploads/onboarding/' . $dealer->id);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdf = PDF::loadView('onboarding.pdf', [
            'dealer'      => $dealer,
            'isSubDealer' => $isSubDealer,
            'territory'   => $territory,
        ])->setPaper('a4');

        $relative = 'uploads/onboarding/' . $dealer->id . '/onboarding-form.pdf';
        $pdf->save(public_path($relative));

        $dealer->onboarding_form_pdf = $relative;
        $dealer->save();
    }
}