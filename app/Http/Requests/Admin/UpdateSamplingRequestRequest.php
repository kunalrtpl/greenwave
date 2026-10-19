<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Corrections an admin makes to a pending sample request on the detail screen.
 *
 * Every field is `sometimes`: the screen can post the product block alone, the
 * competitor block alone, or the whole form, and only what is sent is touched.
 */
class UpdateSamplingRequestRequest extends FormRequest
{
    public function authorize()
    {
        // Admin access is already enforced by the `user` middleware on the route group.
        return true;
    }

    public function rules()
    {
        return [
            'product_id'           => 'sometimes|required|exists:products,id',
            // Qty is never posted: it is always pack size x no of packs.
            'approved_pack_size'   => 'sometimes|required|numeric|min:0.001',
            'approved_no_of_packs' => 'sometimes|required|integer|min:1',
            'customer_id'          => 'sometimes|nullable|exists:customers,id',

            'purpose_reason_for_request' => 'sometimes|nullable|string|max:5000',
            'application_plan'           => 'sometimes|nullable|string|max:5000',

            'dispatch_party_type'  => 'sometimes|nullable|in:dealer,customer,user',
            'dispatch_to'          => 'sometimes|nullable|string|max:255',
            'dispatch_dealer_id'   => 'sometimes|nullable|exists:dealers,id',
            'dispatch_customer_id' => 'sometimes|nullable|exists:customers,id',
            'dispatch_user_id'     => 'sometimes|nullable|exists:users,id',
            'dispatch_address'     => 'sometimes|nullable|string|max:2000',

            'is_specific_customer' => 'sometimes|boolean',
            'product_known'        => 'sometimes|boolean',
            'target_competitor'    => 'sometimes|boolean',

            'competitor_category'               => 'sometimes|nullable|string|max:255',
            'competitor_product_name'           => 'sometimes|nullable|string|max:255',
            'competitor_make'                   => 'sometimes|nullable|string|max:255',
            'competitor_dealer_price'           => 'sometimes|nullable|numeric|min:0',
            'competitor_customer_price'         => 'sometimes|nullable|numeric|min:0',
            'competitor_dosage'                 => 'sometimes|nullable|string|max:100',
            'competitor_monthly_requirement_kg' => 'sometimes|nullable|numeric|min:0',
            'competitor_specialty'              => 'sometimes|nullable|string|max:5000',
            'competitor_application_details'    => 'sometimes|nullable|string|max:5000',
            'competitor_expectation'            => 'sometimes|nullable|string|max:5000',

            'change_remarks' => 'nullable|string|max:1000',
        ];
    }

    /**
     * The party pickers are mutually exclusive, so blank out the two that the
     * chosen type does not use before the service compares old with new.
     */
    protected function prepareForValidation()
    {
        $type = $this->input('dispatch_party_type');
        if (!$type) {
            return;
        }

        $this->merge([
            'dispatch_to'          => ucfirst($type === 'user' ? 'employee' : $type),
            'dispatch_dealer_id'   => $type === 'dealer' ? $this->input('dispatch_dealer_id') : null,
            'dispatch_customer_id' => $type === 'customer' ? $this->input('dispatch_customer_id') : null,
            'dispatch_user_id'     => $type === 'user' ? $this->input('dispatch_user_id') : null,
        ]);
    }

    public function messages()
    {
        return [
            'product_id.required'            => 'Please select the product.',
            'approved_pack_size.required'    => 'Please select the pack size.',
            'approved_no_of_packs.required'  => 'Please enter the no of packs.',
            'approved_no_of_packs.min'       => 'No of packs must be at least 1.',
        ];
    }
}
