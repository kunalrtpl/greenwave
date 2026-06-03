<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Session;

class ProductDocumentController extends Controller
{
    /**
     * GET /admin/product-documents
     */
    public function index()
    {
        Session::put('active','productDocuments');
        $title = 'Product Documents';

        $products = DB::table('products')
            ->where('status', 1)
            ->orderBy('product_name')
            ->select(
                'id', 'product_name', 'product_code',
                'technical_literature', 'msds', 'is_trader_product',
                'gots_certification', 'zdhc_certification', 'zdhc_pid', 'oekotex_certified'
            )
            ->get();

        return view('admin.product_documents.index', compact('title', 'products'));
    }

    /**
     * POST /admin/product-documents/upload/{id}
     * Handles: TL file, MSDS file, GOTS, ZDHC, ZDHC PID, Oekotex
     */
    public function upload(Request $request, $productId)
    {
        $product = DB::table('products')->where('id', $productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $destination  = 'images/ProductDocuments/';
        $dataToUpdate = ['updated_at' => now()];
        $uploadedFiles = [];

        DB::beginTransaction();
        try {

            // ── Certifications (always sent) ──
            $dataToUpdate['gots_certification'] = in_array($request->input('gots_certification'), ['Yes', 'No'])
                ? $request->input('gots_certification') : 'No';

            $zdhcVal = in_array($request->input('zdhc_certification'), ['Yes', 'No'])
                ? $request->input('zdhc_certification') : 'No';
            $dataToUpdate['zdhc_certification'] = $zdhcVal;

            // ── Server-side PID mandatory check ──
            if ($zdhcVal === 'Yes' && empty(trim($request->input('zdhc_pid', '')))) {
                return response()->json([
                    'success' => false,
                    'message' => 'ZDHC PID Number is mandatory when ZDHC is Yes.',
                ], 422);
            }

            $dataToUpdate['zdhc_pid'] = ($zdhcVal === 'Yes') ? trim($request->input('zdhc_pid', '')) : null;

            $dataToUpdate['oekotex_certified'] = in_array($request->input('oekotex_certified'), ['Yes', 'No'])
                ? $request->input('oekotex_certified') : 'No';

            // ── Technical Literature ──
            if ($request->hasFile('technical_literature') && $request->file('technical_literature')->isValid()) {
                $file     = $request->file('technical_literature');
                $filename = time() . '_tl_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->move($destination, $filename);
                $dataToUpdate['technical_literature'] = $filename;
                $uploadedFiles['technical_literature'] = [
                    'filename'   => $filename,
                    'view_url'   => url('images/ProductDocuments/' . $filename),
                    'delete_url' => url('admin/product-documents/delete/' . $productId . '/technical_literature'),
                ];
            }

            // ── MSDS ──
            if ($request->hasFile('msds') && $request->file('msds')->isValid()) {
                $file     = $request->file('msds');
                $filename = time() . '_msds_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->move($destination, $filename);
                $dataToUpdate['msds'] = $filename;
                $uploadedFiles['msds'] = [
                    'filename'   => $filename,
                    'view_url'   => url('images/ProductDocuments/' . $filename),
                    'delete_url' => url('admin/product-documents/delete/' . $productId . '/msds'),
                ];
            }

            DB::table('products')->where('id', $productId)->update($dataToUpdate);
            DB::commit();

            return response()->json([
                'success'             => true,
                'message'             => 'Updated successfully.',
                'files'               => $uploadedFiles,
                // Return saved cert values so JS can update data-orig-* attrs
                'gots_certification'  => $dataToUpdate['gots_certification'],
                'zdhc_certification'  => $dataToUpdate['zdhc_certification'],
                'zdhc_pid'            => $dataToUpdate['zdhc_pid'] ?? '',
                'oekotex_certified'   => $dataToUpdate['oekotex_certified'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /admin/product-documents/delete/{id}/{field}
     */
    public function deleteDocument(Request $request, $productId, $field)
    {
        if (!in_array($field, ['technical_literature', 'msds'])) {
            return response()->json(['success' => false, 'message' => 'Invalid field.'], 422);
        }

        $product = DB::table('products')->where('id', $productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $filename = $product->{$field};
        if ($filename) {
            $path = public_path('images/ProductDocuments/' . $filename);
            if (file_exists($path)) @unlink($path);
        }

        DB::table('products')->where('id', $productId)->update([
            $field       => null,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }
}