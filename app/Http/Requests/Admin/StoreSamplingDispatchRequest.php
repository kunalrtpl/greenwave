<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * One dispatch, in either of two modes.
 *
 *  request    — send out approved-and-ready lines picked from the request list.
 *  voluntary  — the admin sends material with nothing having been requested;
 *               a sampling_requests row flagged is_voluntarily is created for
 *               each product so the history and the stock still line up.
 *
 * `answerable_type` says whether the dispatch belongs to an employee or to a
 * dealer. A dealer only ever has voluntary dispatches.
 *
 * Challan and transport are entered once; the "dispatch to" block repeats per
 * row because two rows on one challan can go to different parties.
 */
class StoreSamplingDispatchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * A dealer's samples are always voluntary — a dealer raises nothing through
     * the executive app — so the dispatch type is forced rather than asked for.
     */
    protected function prepareForValidation()
    {
        if ($this->input('answerable_type') === 'dealer') {
            $this->merge(['dispatch_type' => 'voluntary']);
        }
    }

    public function rules()
    {
        $isDealer    = $this->input('answerable_type') === 'dealer';
        $isVoluntary = $isDealer || $this->input('dispatch_type') === 'voluntary';

        $rules = [
            'answerable_type' => 'required|in:user,dealer',
            'dispatch_type'   => 'required|in:request,voluntary',

            /* ── Challan & transport (common) ── */
            'dispatch_date' => 'required|date',
            'invoice_no'    => 'required|string|max:100',
            'invoice_date'  => 'nullable|date',
            'transport'     => 'required|string|max:100',
            'lr_no'         => 'required|string|max:100',
            'remarks'       => 'nullable|string|max:2000',
        ];

        // "Dispatch to" repeats per row in both modes, because two rows on one
        // challan can go to different parties.
        $party = function ($prefix) {
            return [
                $prefix . '.batch_no'             => 'nullable|string|max:100',
                $prefix . '.dispatch_party_type'  => 'nullable|in:dealer,customer,user',
                $prefix . '.dispatch_dealer_id'   => 'nullable|required_if:' . $prefix . '.dispatch_party_type,dealer|exists:dealers,id',
                $prefix . '.dispatch_customer_id' => 'nullable|required_if:' . $prefix . '.dispatch_party_type,customer|exists:customers,id',
                $prefix . '.dispatch_user_id'     => 'nullable|exists:users,id',
                $prefix . '.dispatch_address'     => 'nullable|string|max:2000',
            ];
        };

        if ($isVoluntary) {
            // No request came from the field: the admin names who the sample is
            // answerable to and the products going out.
            $rules += [
                'user_id'   => $isDealer ? 'nullable' : 'required|exists:users,id',
                'dealer_id' => $isDealer ? 'required|exists:dealers,id' : 'nullable',
                'voluntary_items'                        => 'required|array|min:1',
                'voluntary_items.*.product_id'           => 'required|exists:products,id',
                'voluntary_items.*.approved_pack_size'   => 'required|numeric|min:0.001',
                'voluntary_items.*.approved_no_of_packs' => 'required|integer|min:1',
                'voluntary_items.*.remarks'              => 'nullable|string|max:2000',
            ] + $party('voluntary_items.*');
        } else {
            $rules += [
                'items'                       => 'required|array|min:1',
                'items.*.sampling_request_id' => 'required|distinct|exists:sampling_requests,id',
                'items.*.dispatch_qty'        => 'required|numeric|min:0.001',
            ] + $party('items.*');
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'items.required'                       => 'Select at least one sample request to dispatch.',
            'voluntary_items.required'             => 'Add at least one product to dispatch.',
            'voluntary_items.*.product_id.required'           => 'Please select the product.',
            'voluntary_items.*.approved_pack_size.required'   => 'Please select the pack size.',
            'voluntary_items.*.approved_no_of_packs.required' => 'Please enter the no of packs.',
            'answerable_type.required'             => 'Choose whether this dispatch is for an employee or a dealer.',
            'user_id.required'                     => 'Please select which employee this sample is answerable to.',
            'dealer_id.required'                   => 'Please select which dealer this sample is answerable to.',
            'items.*.sampling_request_id.distinct' => 'The same request has been added twice.',
            'items.*.dispatch_qty.required'        => 'Enter the dispatch qty.',
            'items.*.dispatch_qty.min'             => 'Dispatch qty must be greater than zero.',
            'items.*.dispatch_dealer_id.required_if'   => 'Select the dealer for this request.',
            'items.*.dispatch_customer_id.required_if' => 'Select the customer for this request.',
        ];
    }

    public function attributes()
    {
        return [
            'invoice_no'    => 'challan no',
            'invoice_date'  => 'challan date',
            'lr_no'         => 'LR no',
            'dispatch_date' => 'dispatch date',
        ];
    }
}
