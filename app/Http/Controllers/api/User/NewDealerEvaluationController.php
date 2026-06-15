<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AuthToken;
use App\Dealer;
use App\DealerEvaluation;
use App\DealerEvaluationAnswer;
use App\DealerEvaluationAttachment;
use App\Services\EmailService;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Validator;
use DB;

class NewDealerEvaluationController extends Controller
{
    protected $resp;

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $token      = $request->header('Authorization');
            $resp       = AuthToken::verifyUser($token);
            $this->resp = $resp;
        }
    }

    // =========================================================================
    // QUESTION SCHEMA — single source of truth
    // =========================================================================
    protected function getFormSchema()
    {
        return [
            [
                'section_key'  => 'B',
                'section_name' => 'Business Profile',
                'questions'    => [
                    [
                        'key'               => 'years_in_business',
                        'question'          => 'Years in Business *',
                        'type'              => 'radio',
                        'required'          => true,
                        'available_options' => [
                            'Less than 3 Years',
                            '3 – 5 Years',
                            '6 – 10 Years',
                            'More than 10 Years',
                        ],
                    ],
                    [
                        'key'               => 'nature_of_products',
                        'question'          => 'Nature of Products in Textile Industry *',
                        'type'              => 'checkbox',
                        'required'          => true,
                        'available_options' => [
                            'Basic Chemicals',
                            'Textile Auxiliaries',
                            'Dyes',
                            'Printing Chemicals',
                            'Enzymes',
                            'Silicones',
                            'Machinery',
                            'Other',
                        ],
                    ],
                    [
                        'key'               => 'textile_segments',
                        'question'          => 'Main Textile Segments Served *',
                        'type'              => 'checkbox',
                        'required'          => true,
                        'available_options' => [
                            'Knit Processing',
                            'Woven Processing',
                            'Terry Towel',
                            'Denim',
                            'Printing',
                            'Garment Washing',
                            'Home Textiles',
                            'Others',
                        ],
                    ],
                    [
                        'key'               => 'active_customers_range',
                        'question'          => 'Approximate Number of Active Textile Customers *',
                        'type'              => 'radio',
                        'required'          => true,
                        'available_options' => [
                            'Less than 10',
                            '10 – 25',
                            '26 – 50',
                            '51 – 100',
                            'More than 100',
                        ],
                    ],
                    [
                        'key'               => 'principal_companies',
                        'question'          => 'Principal Companies / Brands Represented',
                        'type'              => 'text',
                        'required'          => false,
                        'available_options' => [],
                    ],
                ],
            ],
            [
                'section_key'  => 'C',
                'section_name' => 'Customer Reach',
                'questions'    => [
                    ['key' => 'key_customer_1', 'question' => 'Key Customer 1', 'type' => 'text', 'required' => false, 'available_options' => []],
                    ['key' => 'key_customer_2', 'question' => 'Key Customer 2', 'type' => 'text', 'required' => false, 'available_options' => []],
                    ['key' => 'key_customer_3', 'question' => 'Key Customer 3', 'type' => 'text', 'required' => false, 'available_options' => []],
                    ['key' => 'key_customer_4', 'question' => 'Key Customer 4', 'type' => 'text', 'required' => false, 'available_options' => []],
                    ['key' => 'key_customer_5', 'question' => 'Key Customer 5', 'type' => 'text', 'required' => false, 'available_options' => []],
                ],
            ],
            [
                'section_key'  => 'D',
                'section_name' => 'Capability Assessment',
                'questions'    => [
                    [
                        'key'               => 'technical_sales_team',
                        'question'          => 'Technical Sales Team Available? *',
                        'type'              => 'radio',
                        'required'          => true,
                        'available_options' => ['Yes', 'No', 'Not Known'],
                    ],
                    [
                        'key'               => 'existing_enzyme_silicone_biz',
                        'question'          => 'Existing Business in Enzymes & Silicones *',
                        'type'              => 'radio',
                        'required'          => true,
                        'available_options' => ['Strong', 'Moderate', 'Limited', 'None', 'Not Known'],
                    ],
                    [
                        'key'               => 'relationship_with_textile_houses',
                        'question'          => 'Relationship with Textile Processing Houses *',
                        'type'              => 'radio',
                        'required'          => true,
                        'available_options' => ['Strong', 'Moderate', 'Limited'],
                    ],
                    [
                        'key'               => 'interest_in_bio_silicone',
                        'question'          => "Dealer's Interest in Developing Bio & Silicone Business *",
                        'type'              => 'radio',
                        'required'          => true,
                        'available_options' => ['High', 'Medium', 'Low'],
                    ],
                    [
                        'key'               => 'estimated_annual_potential',
                        'question'          => 'Estimated Annual Potential for Greenwave Bio and Silicone Business *',
                        'type'              => 'radio',
                        'required'          => true,
                        'available_options' => [
                            'Less than ₹10 Lakhs per year',
                            '₹10 – 25 Lakhs per year',
                            '₹25 – 50 Lakhs per year',
                            '₹50 Lakhs – 1 Crore per year',
                            'More than ₹1 Crore per year',
                        ],
                    ],
                ],
            ],
            [
                'section_key'  => 'E',
                'section_name' => 'Area Head Observations',
                'questions'    => [
                    [
                        'key'               => 'key_strengths',
                        'question'          => 'Key Strengths Observed *',
                        'type'              => 'text',
                        'required'          => true,
                        'available_options' => [],
                    ],
                    [
                        'key'               => 'key_concerns',
                        'question'          => 'Key Concerns',
                        'type'              => 'checkbox',
                        'required'          => false,
                        'available_options' => [
                            'No major concern identified',
                            'Already represents a competing principal',
                            'Limited technical capability',
                            'Limited customer base',
                            'Weak market reputation',
                            'Limited Bio / Silicone business',
                            'Insufficient market coverage',
                            'Creditworthiness concern',
                            'Other',
                        ],
                    ],
                    [
                        'key'               => 'additional_remarks',
                        'question'          => 'Additional Remarks',
                        'type'              => 'text',
                        'required'          => false,
                        'available_options' => [],
                    ],
                ],
            ],
        ];
    }

    // ── Flat map: key → question definition (for validation) ──────────────────
    protected function questionFlatMap()
    {
        $map = [];
        foreach ($this->getFormSchema() as $section) {
            foreach ($section['questions'] as $q) {
                $q['section_key']  = $section['section_key'];
                $q['section_name'] = $section['section_name'];
                $map[$q['key']]    = $q;
            }
        }
        return $map;
    }

    // ── Shared: parse + validate sections JSON → parsedAnswers array ──────────
    // Returns array of rows ready for DB insert, or a JSON error response.
    protected function parseSections($sectionsRaw)
    {
        $flatMap       = $this->questionFlatMap();
        $parsedAnswers = [];
        $now           = Carbon::now()->toDateTimeString();

        foreach ($sectionsRaw as $section) {
            foreach (($section['questions'] ?? []) as $item) {
                $key = $item['key'] ?? null;

                if (!$key || !isset($flatMap[$key])) {
                    return ['error' => "Unknown question key: {$key}"];
                }

                $def             = $flatMap[$key];
                $selectedOptions = $item['selected_options'] ?? [];
                $customAnswer    = $item['custom_answer']    ?? null;

                // Required check
                if ($def['required']) {
                    if ($def['type'] === 'text' && empty($customAnswer)) {
                        return ['error' => "Answer required for: {$def['question']}"];
                    }
                    if (in_array($def['type'], ['radio', 'checkbox']) && empty($selectedOptions)) {
                        return ['error' => "Selection required for: {$def['question']}"];
                    }
                }

                // Validate selected options exist in available_options
                if (!empty($selectedOptions) && !empty($def['available_options'])) {
                    foreach ($selectedOptions as $sel) {
                        if (!in_array($sel, $def['available_options'])) {
                            return ['error' => "Invalid option '{$sel}' for: {$def['question']}"];
                        }
                    }
                }

                // Build flat row for bulk insert
                // json_encode here because DB::table()->insert() bypasses model casts
                $parsedAnswers[] = [
                    'section_key'      => $def['section_key'],
                    'section_name'     => $def['section_name'],
                    'question_key'     => $key,
                    'question_text'    => $def['question'],
                    'question_type'    => $def['type'],
                    'available_options'=> json_encode($def['available_options']),
                    'selected_options' => json_encode($selectedOptions),
                    'custom_answer'    => $customAnswer,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        return ['data' => $parsedAnswers];
    }

    // =========================================================================
    // API 1 — GET /dealer-evaluation-questions
    // =========================================================================
    public function formQuestions(Request $request)
    {
        $sectionA = [
            'section_key'  => 'A',
            'section_name' => 'Basic Information',
            'questions'    => [
                ['key' => 'firm_name',         'question' => 'Firm Name *',                'type' => 'text',  'required' => true,  'available_options' => []],
                ['key' => 'contact_person',    'question' => 'Contact Person *',           'type' => 'text',  'required' => true,  'available_options' => []],
                ['key' => 'mobile_number',     'question' => 'Mobile Number *',            'type' => 'text',  'required' => true,  'available_options' => []],
                ['key' => 'email',             'question' => 'Email ID',                   'type' => 'text',  'required' => false, 'available_options' => []],
                ['key' => 'city',              'question' => 'City *',                     'type' => 'text',  'required' => true,  'available_options' => []],
                ['key' => 'territory_covered', 'question' => 'Area / Territory Covered *', 'type' => 'text',  'required' => true,  'available_options' => []],
                [
                    'key'               => 'source_of_lead',
                    'question'          => 'Source of Lead *',
                    'type'              => 'radio',
                    'required'          => true,
                    'available_options' => [
                        'Marketing Executive',
                        'Existing Channel Partner',
                        'Industry Reference',
                        'Website/App Enquiry',
                        'Exhibition',
                        'Self-Generated',
                        'Other',
                    ],
                ],
            ],
        ];

        $fullSchema = array_merge([$sectionA], $this->getFormSchema());

        return response()->json(apiSuccessResponse('Form schema fetched successfully.', [
            'form_sections' => $fullSchema,
        ]), 200);
    }

    // =========================================================================
    // API 2 — LIST   GET /dealer-evaluations
    // =========================================================================
    public function index(Request $request)
    {
        $resp = $this->resp;
        if (!$resp['status']) {
            return response()->json(apiErrorResponse('Unauthorized. Please try again after sometime.'), 422);
        }

        $dealers = Dealer::with(['evaluations.answers', 'evaluations.attachments'])
            ->where('is_authenticated', 0)
            ->where('is_delete', 0)
            ->orderBy('created_at', 'DESC')
            ->get();

        $dealers->each(function ($dealer) {
            $dealer->evaluations->each(function ($eval) {
                $eval->attachments->each(function ($att) {
                    $att->file_url = url('dealer_evaluations/attachments/' . $att->file);
                });
                $eval->answers_grouped = $this->reshapeAnswersForResponse($eval->answers);
            });
        });

        return response()->json(apiSuccessResponse('Dealer evaluations fetched successfully.', [
            'dealers' => $dealers,
        ]), 200);
    }

    // =========================================================================
    // API 3 — CREATE   POST /dealer-evaluations
    // =========================================================================
    public function store(Request $request)
    {
        $resp = $this->resp;
        if (!$resp['status']) {
            return response()->json(apiErrorResponse('Unauthorized. Please try again after sometime.'), 422);
        }

        // ── Validate ──────────────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'firm_name'         => 'required|string|max:191',
            'contact_person'    => 'required|string|max:255',
            'mobile_number'     => 'required|string|max:15',
            'email'             => 'nullable|email|max:191',
            'city'              => 'required|string|max:191',
            'territory_covered' => 'required|string|max:255',
            'source_of_lead'    => 'required|string|max:100',
            'sections'          => 'required|string',
            'is_submitted'      => 'required|in:0,1',  // 1 = send email, 0 = save as draft
            'attachments'       => 'nullable|array',
            'attachments.*'     => 'file|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(apiErrorResponse($validator->errors()->first()), 422);
        }

        // ── Duplicate checks ──────────────────────────────────────────────────
        $mobileExists = Dealer::where('owner_mobile', $request->input('mobile_number'))
            ->where('is_delete', 0)->exists();
        if ($mobileExists) {
            return response()->json(apiErrorResponse('A dealer with this mobile number already exists.'), 422);
        }

        if (!empty($request->input('email'))) {
            $emailExists = Dealer::where('email', $request->input('email'))
                ->where('is_delete', 0)->exists();
            if ($emailExists) {
                return response()->json(apiErrorResponse('A dealer with this email address already exists.'), 422);
            }
        }

        // ── Parse & validate sections ─────────────────────────────────────────
        $sectionsRaw = json_decode($request->input('sections'), true);
        if (!is_array($sectionsRaw)) {
            return response()->json(apiErrorResponse('sections must be a valid JSON array.'), 422);
        }

        $parsed = $this->parseSections($sectionsRaw);
        if (isset($parsed['error'])) {
            return response()->json(apiErrorResponse($parsed['error']), 422);
        }
        $parsedAnswers = $parsed['data'];

        // ── Move uploaded files BEFORE transaction (can't rollback filesystem) ─
        $attachmentDir  = public_path('dealer_evaluations/attachments');
        if (!file_exists($attachmentDir)) mkdir($attachmentDir, 0775, true);
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $index => $file) {
                if (!$file->isValid()) continue;
                $fileName = 'eval_' . time() . '_' . uniqid() . '_' . $index . '.' . $file->getClientOriginalExtension();
                $file->move($attachmentDir, $fileName);
                $uploadedFiles[] = [
                    'file'          => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getClientMimeType(),
                ];
            }
        }

        // ── Everything in one transaction ─────────────────────────────────────
        try {
            DB::beginTransaction();

            $employeeId = $resp['user']['id'];
            $now        = Carbon::now()->toDateTimeString();

            // 1. Create dealer
            $dealer                            = new Dealer();
            $dealer->dealer_type               = 'dealer';
            $dealer->business_name             = $request->input('firm_name');
            $dealer->short_name                = strtoupper(substr($request->input('firm_name'), 0, 5));
            $dealer->name                      = $request->input('contact_person');
            $dealer->owner_name                = $request->input('contact_person');
            $dealer->owner_mobile              = $request->input('mobile_number');
            $dealer->email                     = $request->input('email', '');
            $dealer->city                      = $request->input('city');
            $dealer->source_of_lead            = $request->input('source_of_lead');
            $dealer->created_by                = $employeeId;
            $dealer->is_authenticated          = 0;
            $dealer->gst_no                    = '';
            $dealer->address                   = '';
            $dealer->office_phone              = '';
            $dealer->password                  = '';
            $dealer->payment_term              = 0;
            $dealer->security_amount           = 0;
            $dealer->interest_rate_on_security = 0;
            $dealer->credit_multiple           = 0;
            $dealer->credit_allowed            = 0;
            $dealer->freight                   = 0;
            $dealer->base_sale_margin_lock     = '';
            $dealer->base_sale_level_to_archive= 0;
            $dealer->margin_lock               = 0;
            $dealer->applicable_from           = '';
            $dealer->applicable_to             = '';
            $dealer->show_class                = 'No';
            $dealer->status                    = 1;
            $dealer->linked_dealers            = '';
            $dealer->trader_product            = 0;
            $dealer->product_types             = '';
            $dealer->app_roles                 = '';
            $dealer->login_device              = '';
            $dealer->notification_token        = '';
            $dealer->app_details               = '';
            $dealer->save();

            // 2. Territory — single insert
            DB::table('dealer_operating_cities')->insert([
                'dealer_id'  => $dealer->id,
                'city'       => $request->input('territory_covered'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 3. Evaluation — single insert
            $evaluationId = DB::table('dealer_evaluations')->insertGetId([
                'dealer_id'    => $dealer->id,
                'submitted_by' => $employeeId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            // 4. Answers — ONE bulk insert instead of N inserts
            $answerRows = array_map(function ($ans) use ($evaluationId) {
                return array_merge($ans, ['evaluation_id' => $evaluationId]);
            }, $parsedAnswers);

            DB::table('dealer_evaluation_answers')->insert($answerRows);

            // 5. Attachments — ONE bulk insert instead of N inserts
            if (!empty($uploadedFiles)) {
                $attachmentRows = array_map(function ($f) use ($evaluationId, $now) {
                    return [
                        'evaluation_id' => $evaluationId,
                        'file'          => $f['file'],
                        'original_name' => $f['original_name'],
                        'mime_type'     => $f['mime_type'],
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }, $uploadedFiles);

                DB::table('dealer_evaluation_attachments')->insert($attachmentRows);
            }

            DB::commit();

            // ── Load for response & email (after commit) ──────────────────────
            $evaluation = DealerEvaluation::with(['answers', 'attachments'])->find($evaluationId);
            $evaluation->attachments->each(fn($att) => $att->file_url = url('dealer_evaluations/attachments/' . $att->file));

            // Send email only if is_submitted = 1
            if ((int)$request->input('is_submitted') === 1) {
                $this->sendEvaluationEmail($dealer, $evaluation, $resp['user'], true);
            }

            return response()->json(apiSuccessResponse('Dealer evaluation created successfully.', [
                'dealer'     => $dealer,
                'evaluation' => $evaluation,
            ]), 200);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up any uploaded files since DB rolled back
            foreach ($uploadedFiles as $f) {
                $path = $attachmentDir . '/' . $f['file'];
                if (file_exists($path)) unlink($path);
            }

            \Log::error('Dealer evaluation create failed: ' . $e->getMessage());
            return response()->json(apiErrorResponse('Something went wrong. Please try again.'), 422);
        }
    }

    // =========================================================================
    // API 4 — EDIT   POST /dealer-evaluations/dealer/{dealerId}
    // Based on dealer_id. Email & mobile NOT editable.
    // Section A editable fields: contact_person, city, territory_covered, source_of_lead
    // Sections B–E: fully editable
    // is_submitted: 1 = send email, 0 = save as draft (no email)
    // =========================================================================
    public function update(Request $request, $dealerId)
    {
        $resp = $this->resp;
        if (!$resp['status']) {
            return response()->json(apiErrorResponse('Unauthorized. Please try again after sometime.'), 422);
        }

        // ── Find dealer ───────────────────────────────────────────────────────
        $dealer = Dealer::where('id', $dealerId)
            ->where('is_delete', 0)
            ->first();

        if (!$dealer) {
            return response()->json(apiErrorResponse('Dealer not found.'), 422);
        }

        // ── Find latest evaluation for this dealer ────────────────────────────
        $evaluation = DealerEvaluation::with(['answers', 'attachments'])
            ->where('dealer_id', $dealerId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$evaluation) {
            return response()->json(apiErrorResponse('Evaluation not found for this dealer.'), 422);
        }

        // ── Validate ──────────────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            // Section A editable fields (email & mobile_number intentionally excluded)
            'contact_person'          => 'nullable|string|max:255',
            'city'                    => 'nullable|string|max:191',
            'territory_covered'       => 'nullable|string|max:255',
            'source_of_lead'          => 'nullable|string|max:100',
            // Sections B–E
            'sections'                => 'required|string',
            // Email control
            'is_submitted'            => 'required|in:0,1',  // 1 = send email, 0 = draft
            // Attachments
            'attachments'             => 'nullable|array',
            'attachments.*'           => 'file|max:51200',
            'remove_attachment_ids'   => 'nullable|array',
            'remove_attachment_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(apiErrorResponse($validator->errors()->first()), 422);
        }

        // ── Parse & validate sections ─────────────────────────────────────────
        $sectionsRaw = json_decode($request->input('sections'), true);
        if (!is_array($sectionsRaw)) {
            return response()->json(apiErrorResponse('sections must be a valid JSON array.'), 422);
        }

        $parsed = $this->parseSections($sectionsRaw);
        if (isset($parsed['error'])) {
            return response()->json(apiErrorResponse($parsed['error']), 422);
        }
        $parsedAnswers = $parsed['data'];

        // ── Move uploaded files BEFORE transaction ────────────────────────────
        $attachmentDir = public_path('dealer_evaluations/attachments');
        if (!file_exists($attachmentDir)) mkdir($attachmentDir, 0775, true);
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $index => $file) {
                if (!$file->isValid()) continue;
                $fileName = 'eval_' . time() . '_' . uniqid() . '_' . $index . '.' . $file->getClientOriginalExtension();
                $file->move($attachmentDir, $fileName);
                $uploadedFiles[] = [
                    'file'          => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getClientMimeType(),
                ];
            }
        }

        try {
            DB::beginTransaction();

            $now = Carbon::now()->toDateTimeString();

            // 1. Update dealer Section A fields (email & mobile NOT touched)
            $dealerUpdates = [];
            if ($request->filled('contact_person')) {
                $dealerUpdates['name']       = $request->input('contact_person');
                $dealerUpdates['owner_name'] = $request->input('contact_person');
            }
            if ($request->filled('city')) {
                $dealerUpdates['city'] = $request->input('city');
            }
            if ($request->filled('source_of_lead')) {
                $dealerUpdates['source_of_lead'] = $request->input('source_of_lead');
            }
            if (!empty($dealerUpdates)) {
                $dealerUpdates['updated_at'] = $now;
                DB::table('dealers')->where('id', $dealerId)->update($dealerUpdates);
            }

            // 2. Update territory_covered in dealer_operating_cities
            if ($request->filled('territory_covered')) {
                // Update existing city row for this dealer, or insert if missing
                $existingCity = DB::table('dealer_operating_cities')
                    ->where('dealer_id', $dealerId)
                    ->first();

                if ($existingCity) {
                    DB::table('dealer_operating_cities')
                        ->where('dealer_id', $dealerId)
                        ->update([
                            'city'       => $request->input('territory_covered'),
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('dealer_operating_cities')->insert([
                        'dealer_id'  => $dealerId,
                        'city'       => $request->input('territory_covered'),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // 3. Delete old answers — single delete query
            DB::table('dealer_evaluation_answers')
                ->where('evaluation_id', $evaluation->id)
                ->delete();

            // 4. Bulk insert new answers — single query
            $answerRows = array_map(function ($ans) use ($evaluation, $now) {
                return array_merge($ans, [
                    'evaluation_id' => $evaluation->id,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }, $parsedAnswers);

            DB::table('dealer_evaluation_answers')->insert($answerRows);

            // 5. Remove requested attachments
            $removedFiles = [];
            if ($request->has('remove_attachment_ids') && !empty($request->input('remove_attachment_ids'))) {
                $toRemove = DB::table('dealer_evaluation_attachments')
                    ->whereIn('id', $request->input('remove_attachment_ids'))
                    ->where('evaluation_id', $evaluation->id)
                    ->get();

                DB::table('dealer_evaluation_attachments')
                    ->whereIn('id', $request->input('remove_attachment_ids'))
                    ->where('evaluation_id', $evaluation->id)
                    ->delete();

                foreach ($toRemove as $att) {
                    $removedFiles[] = public_path('dealer_evaluations/attachments/' . $att->file);
                }
            }

            // 6. Bulk insert new attachments
            if (!empty($uploadedFiles)) {
                $attachmentRows = array_map(function ($f) use ($evaluation, $now) {
                    return [
                        'evaluation_id' => $evaluation->id,
                        'file'          => $f['file'],
                        'original_name' => $f['original_name'],
                        'mime_type'     => $f['mime_type'],
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }, $uploadedFiles);

                DB::table('dealer_evaluation_attachments')->insert($attachmentRows);
            }

            // 7. Touch evaluation updated_at
            DB::table('dealer_evaluations')
                ->where('id', $evaluation->id)
                ->update(['updated_at' => $now]);

            DB::commit();

            // ── Clean up removed files after commit ───────────────────────────
            foreach ($removedFiles as $filePath) {
                if (file_exists($filePath)) unlink($filePath);
            }

            // ── Load fresh dealer & evaluation for response & email ───────────
            $dealer->refresh();
            $evaluation->load(['answers', 'attachments']);
            $evaluation->attachments->each(fn($att) => $att->file_url = url('dealer_evaluations/attachments/' . $att->file));
            $evaluation->answers_grouped = $this->reshapeAnswersForResponse($evaluation->answers);

            // ── Send email only if is_submitted = 1 ──────────────────────────
            if ((int)$request->input('is_submitted') === 1) {
                $this->sendEvaluationEmail($dealer, $evaluation, $resp['user'], false);
            }

            return response()->json(apiSuccessResponse('Evaluation updated successfully.', [
                'dealer'     => $dealer,
                'evaluation' => $evaluation,
            ]), 200);

        } catch (\Exception $e) {
            DB::rollBack();

            foreach ($uploadedFiles as $f) {
                $path = $attachmentDir . '/' . $f['file'];
                if (file_exists($path)) unlink($path);
            }

            \Log::error('Dealer evaluation update failed: ' . $e->getMessage());
            return response()->json(apiErrorResponse('Something went wrong. Please try again.'), 422);
        }
    }

    // =========================================================================
    // API 5 — SHOW   GET /dealer-evaluations/{id}
    // =========================================================================
    public function show(Request $request, $id)
    {
        $resp = $this->resp;
        if (!$resp['status']) {
            return response()->json(apiErrorResponse('Unauthorized. Please try again after sometime.'), 422);
        }

        $evaluation = DealerEvaluation::with(['dealer', 'answers', 'attachments'])->find($id);
        if (!$evaluation) {
            return response()->json(apiErrorResponse('Evaluation not found.'), 422);
        }

        $evaluation->attachments->each(fn($att) => $att->file_url = url('dealer_evaluations/attachments/' . $att->file));
        $evaluation->answers_grouped = $this->reshapeAnswersForResponse($evaluation->answers);

        return response()->json(apiSuccessResponse('Evaluation fetched successfully.', [
            'evaluation' => $evaluation,
        ]), 200);
    }

    // =========================================================================
    // API 6 — DELETE ATTACHMENT   POST /dealer-evaluations/attachment/{attachmentId}/delete
    // =========================================================================
    public function deleteAttachment(Request $request, $attachmentId)
    {
        $resp = $this->resp;
        if (!$resp['status']) {
            return response()->json(apiErrorResponse('Unauthorized. Please try again after sometime.'), 422);
        }

        // Fetch attachment — make sure it exists
        $attachment = DB::table('dealer_evaluation_attachments')
            ->where('id', $attachmentId)
            ->first();

        if (!$attachment) {
            return response()->json(apiErrorResponse('Attachment not found.'), 422);
        }

        // Delete DB record
        DB::table('dealer_evaluation_attachments')
            ->where('id', $attachmentId)
            ->delete();

        // Delete physical file
        $filePath = public_path('dealer_evaluations/attachments/' . $attachment->file);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return response()->json(apiSuccessResponse('Attachment deleted successfully.', []), 200);
    }

    // =========================================================================
    // HELPER — reshape flat answers into section → questions for response
    // =========================================================================
    protected function reshapeAnswersForResponse($answers)
    {
        $sections = [];
        foreach ($answers as $ans) {
            $sKey = $ans->section_key;
            if (!isset($sections[$sKey])) {
                $sections[$sKey] = [
                    'section_key'  => $sKey,
                    'section_name' => $ans->section_name,
                    'questions'    => [],
                ];
            }
            $sections[$sKey]['questions'][] = [
                'key'               => $ans->question_key,
                'question'          => $ans->question_text,
                'type'              => $ans->question_type,
                'available_options' => $ans->available_options ?? [],
                'selected_options'  => $ans->selected_options  ?? [],
                'custom_answer'     => $ans->custom_answer,
            ];
        }
        return array_values($sections);
    }

    // =========================================================================
    // EMAIL + PDF
    // =========================================================================
        protected function sendEvaluationEmail($dealer, $evaluation, $employee, $isNew = true)
    {
        try {
            $pdfHtml    = $this->buildEvaluationPdfHtml($dealer, $evaluation);
            $mpdf       = new Mpdf(['margin_top' => 15, 'margin_bottom' => 15, 'margin_left' => 15, 'margin_right' => 15]);
            $mpdf->WriteHTML($pdfHtml);
            $pdfContent = $mpdf->Output('', 'S');

            $pdfDir  = storage_path('app/temp_eval_pdfs');
            if (!file_exists($pdfDir)) mkdir($pdfDir, 0775, true);
            $pdfFile = $pdfDir . '/eval_' . $evaluation->id . '_' . time() . '.pdf';
            file_put_contents($pdfFile, $pdfContent);

            // Send only to employee — NOT to dealer
            $emailTo = array_filter([$employee['email'] ?? null]);
            if (!empty($emailTo)) {
                EmailService::send('dealer_evaluation_employee', [
                    'dealer'       => $dealer->toArray(),
                    'evaluation'   => $evaluation->toArray(),
                    'employee'     => $employee,
                    'submittedBy'  => $employee,
                    'submittedAt'  => Carbon::now()->format('d M Y, h:i A'),
                    'is_new'       => $isNew, // true = new submission, false = updated
                    '_attachments' => [$pdfFile],
                ], $emailTo);
            }

            if (file_exists($pdfFile)) unlink($pdfFile);

        } catch (\Exception $e) {
            \Log::error('Dealer evaluation email failed: ' . $e->getMessage());
        }
    }

        protected function buildEvaluationPdfHtml($dealer, $evaluation)
    {
        $submittedAt = Carbon::now()->format('d M Y, h:i A');

        // ── Sections B–E grouped ───────────────────────────────────────────────
        $grouped = $evaluation->answers->groupBy('section_key');

        // ── Build section rows HTML ───────────────────────────────────────────
        $sectionsHtml = '';
        foreach ($grouped as $sectionKey => $answers) {
            $sectionName = $answers->first()->section_name;

            $sectionsHtml .= '
            <tr>
                <td colspan="2" style="
                    background:#1a7f3c;
                    color:#ffffff;
                    font-size:10px;
                    font-weight:bold;
                    padding:8px 14px;
                    letter-spacing:0.5px;
                    text-transform:uppercase;
                ">Section ' . $sectionKey . ' &ndash; ' . htmlspecialchars($sectionName) . '</td>
            </tr>';

            foreach ($answers as $ans) {
                $selected  = $ans->selected_options ?? [];
                $valueHtml = '';

                if (!empty($selected)) {
                    foreach ($selected as $opt) {
                        $valueHtml .= '<span style="
                            display:inline-block;
                            background:#e8f5e9;
                            color:#1a7f3c;
                            border:1px solid #a5d6a7;
                            border-radius:10px;
                            padding:2px 9px;
                            margin:2px 2px 2px 0;
                            font-size:9px;
                            font-weight:bold;
                        ">' . htmlspecialchars($opt) . '</span>';
                    }
                } elseif (!empty($ans->custom_answer)) {
                    $valueHtml = '<span style="color:#1e293b;">' . htmlspecialchars($ans->custom_answer) . '</span>';
                } else {
                    $valueHtml = '<span style="color:#94a3b8;font-style:italic;">Not provided</span>';
                }

                // Strip * from question text for clean display
                $questionLabel = rtrim(trim($ans->question_text), '*');

                $sectionsHtml .= '
                <tr>
                    <td style="
                        width:42%;
                        padding:9px 14px;
                        border-bottom:1px solid #e2e8f0;
                        color:#475569;
                        font-weight:bold;
                        font-size:9px;
                        vertical-align:top;
                        background:#f8fafc;
                    ">' . htmlspecialchars($questionLabel) . '</td>
                    <td style="
                        padding:9px 14px;
                        border-bottom:1px solid #e2e8f0;
                        color:#1e293b;
                        font-size:9px;
                        vertical-align:top;
                        background:#ffffff;
                    ">' . $valueHtml . '</td>
                </tr>';
            }
        }

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 9px;
      color: #1e293b;
      background: #ffffff;
      line-height: 1.5;
  }
</style>
</head>
<body>

  <!-- ═══ HEADER ═══ -->
  <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:20px;">
    <tr>
      <td style="background:#1a7f3c;padding:18px 20px;border-radius:0;">
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td style="vertical-align:middle;">
              <div style="font-size:15px;font-weight:bold;color:#ffffff;letter-spacing:0.5px;">
                Greenwave
              </div>
              <div style="font-size:9px;color:#c8e6c9;margin-top:2px;">
                Bio &amp; Silicone Division
              </div>
            </td>
            <td style="vertical-align:middle;text-align:right;">
              <div style="font-size:12px;font-weight:bold;color:#ffffff;text-transform:uppercase;letter-spacing:1px;">
                Channel Partner Evaluation
              </div>
              <div style="font-size:8px;color:#c8e6c9;margin-top:3px;">
                Submitted: ' . $submittedAt . '
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- ═══ SECTION A — BASIC INFO ═══ -->
  <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-bottom:16px;">

    <!-- Section header -->
    <tr>
      <td colspan="2" style="
          background:#1a7f3c;
          color:#ffffff;
          font-size:10px;
          font-weight:bold;
          padding:8px 14px;
          letter-spacing:0.5px;
          text-transform:uppercase;
      ">Section A &ndash; Basic Information</td>
    </tr>

    <!-- Rows -->
    <tr>
      <td style="width:42%;padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:bold;font-size:9px;background:#f8fafc;">Firm Name</td>
      <td style="padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-size:9px;font-weight:bold;background:#ffffff;">' . htmlspecialchars($dealer->business_name) . '</td>
    </tr>
    <tr>
      <td style="width:42%;padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:bold;font-size:9px;background:#f8fafc;">Contact Person</td>
      <td style="padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-size:9px;background:#ffffff;">' . htmlspecialchars($dealer->name ?? '') . '</td>
    </tr>
    <tr>
      <td style="width:42%;padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:bold;font-size:9px;background:#f8fafc;">Mobile Number</td>
      <td style="padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-size:9px;background:#ffffff;">' . htmlspecialchars($dealer->owner_mobile) . '</td>
    </tr>
    <tr>
      <td style="width:42%;padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:bold;font-size:9px;background:#f8fafc;">Email</td>
      <td style="padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-size:9px;background:#ffffff;">' . htmlspecialchars($dealer->email ?: '—') . '</td>
    </tr>
    <tr>
      <td style="width:42%;padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:bold;font-size:9px;background:#f8fafc;">City</td>
      <td style="padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-size:9px;background:#ffffff;">' . htmlspecialchars($dealer->city) . '</td>
    </tr>
    <tr>
      <td style="width:42%;padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:bold;font-size:9px;background:#f8fafc;">Source of Lead</td>
      <td style="padding:9px 14px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-size:9px;background:#ffffff;">
        <span style="display:inline-block;background:#e8f5e9;color:#1a7f3c;padding:2px 10px;border-radius:10px;font-size:8px;font-weight:bold;">
          ' . htmlspecialchars($dealer->source_of_lead ?: '—') . '
        </span>
      </td>
    </tr>

  </table>

  <!-- ═══ SECTIONS B–E ═══ -->
  <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-bottom:16px;">
    ' . $sectionsHtml . '
  </table>

  <!-- ═══ FOOTER ═══ -->
  <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:20px;border-top:1px solid #e2e8f0;padding-top:10px;">
    <tr>
      <td style="font-size:7.5px;color:#475569;font-weight:bold;">Greenwave &bull; Channel Partner Evaluation</td>
      <td style="font-size:7px;color:#94a3b8;text-align:center;">Confidential &mdash; Internal Use Only</td>
      <td style="font-size:7px;color:#94a3b8;text-align:right;">' . $submittedAt . '</td>
    </tr>
  </table>

</body>
</html>';

        return $html;
    }
}