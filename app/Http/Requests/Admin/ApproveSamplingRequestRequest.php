<?php

namespace App\Http\Requests\Admin;

/**
 * Approving carries the same editable fields as a plain edit — the admin can
 * correct the product or the address on the way through — plus the packing
 * that decides the approved qty (pack size x no of packs).
 */
class ApproveSamplingRequestRequest extends UpdateSamplingRequestRequest
{
    public function rules()
    {
        // array_merge, not `+`: the parent declares these as `sometimes` for
        // the plain edit form and `+` would keep the parent's version, so a
        // missing pack size would skip validation instead of failing.
        return array_merge(parent::rules(), [
            'approved_pack_size'   => 'required|numeric|min:0.001',
            'approved_no_of_packs' => 'required|integer|min:1',
            'approval_remarks'     => 'nullable|string|max:2000',
        ]);
    }

    public function messages()
    {
        return array_merge(parent::messages(), [
            'approved_pack_size.required'   => 'Please select the pack size to approve.',
            'approved_no_of_packs.required' => 'Please enter how many packs are approved.',
            'approved_no_of_packs.min'      => 'Approve at least one pack, or decline the request instead.',
        ]);
    }
}
