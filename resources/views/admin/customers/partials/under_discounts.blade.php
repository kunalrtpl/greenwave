<div class="form-group">
    <label class="col-md-12">
        Product Under Discounts Model
    </label>
    <div class="col-md-12">
        <table class="table table-bordered" id="discountProductsTable">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th>S.No.</th>
                    <th>Product Type</th>
                    <th width="20%">Product Name</th>
                    <th>MOQ</th>
                    <th>Disc. (%)</th>
                    <th>Special Disc. (%)</th>
                    <th>Min Qty</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customerDiscounts as $index => $discount)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>Greenwave Textile Products</td>
                    <td>
                        <select name="discount_products[{{ $index + 1 }}][product_id]" class="form-control product-select select2" style="min-width: 250px;" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product['id'] }}" @if($product['id'] == $discount->product_id) selected @endif>
                                    {{ $product['product_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="discount_products[{{ $index + 1 }}][moq]" class="form-control" step="0.001" min="0" value="{{ $discount->moq }}" placeholder="Enter MOQ" required>
                    </td>
                    <td>
                        <input type="number" name="discount_products[{{ $index + 1 }}][discount]" class="form-control" step="0.01" min="0" value="{{ $discount->discount }}" placeholder="Enter Discount" required>
                    </td>
                    <td>
                        <input type="number" name="discount_products[{{ $index + 1 }}][special_discount]" class="form-control" step="0.01" min="0" value="{{ $discount->special_discount ?? '' }}" placeholder="Enter Special Discount">
                    </td>
                    <td>
                        <input step="0.001" type="number" name="discount_products[{{ $index + 1 }}][min_qty]" class="form-control" step="1" min="0" value="{{ $discount->min_qty ?? '' }}" placeholder="Enter Min Qty">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(this)"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="btn btn-primary" onclick="addDiscountProductRow()">Add New</button>
    </div>
</div>

@php
    // Encoding products for JS usage
    $productsJson = json_encode($products);
@endphp

<script>
    let rowIndex = document.querySelectorAll('#discountProductsTable tbody tr').length;
    const products = {!! $productsJson !!};

    function addDiscountProductRow() {
        rowIndex++;
        const tableBody = document.querySelector('#discountProductsTable tbody');

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${rowIndex}</td>
            <td>Greenwave Textile Products</td>
            <td>
                <select name="discount_products[${rowIndex}][product_id]" class="form-control product-select select2" style="min-width: 250px;" required>
                    <option value="">Select Product</option>
                    ${products.map(p => `<option value="${p.id}">${p.product_name}</option>`).join('')}
                </select>
            </td>
            <td><input type="number" name="discount_products[${rowIndex}][moq]" class="form-control" step="1" min="1" placeholder="Enter MOQ" ></td>
            <td><input type="number" name="discount_products[${rowIndex}][discount]" class="form-control" step="0.01" min="0" placeholder="Enter Discount" required></td>
            <td><input type="number" name="discount_products[${rowIndex}][special_discount]" class="form-control" step="0.01" min="0" placeholder="Enter Special Discount" ></td>
            <td><input type="number" name="discount_products[${rowIndex}][min_qty]" class="form-control" step="1" min="0" placeholder="Enter Min Qty" ></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(this)"><i class="fa fa-times"></i></button></td>
        `;

        tableBody.appendChild(row);

        // Apply select2
        $(row).find('.select2').select2({ width: 'resolve' });

        // Attach change event to validate duplicate product
        $(row).find('.product-select').on('change', function () {
            validateDuplicateProduct(this);
        });
    }

    function removeProductRow(button) {
        const row = button.closest('tr');
        row.remove();
        updateRowNumbers();
    }

    function updateRowNumbers() {
        $('#discountProductsTable tbody tr').each(function(i, row) {
            $(row).find('td:first').text(i + 1);
        });
        rowIndex = $('#discountProductsTable tbody tr').length;
    }

    function validateDuplicateProduct(currentSelect) {
        const selectedVal = parseInt($(currentSelect).val());
        if (!selectedVal) return;

        let isDuplicate = false;
        $('.product-select').not(currentSelect).each(function () {
            if (parseInt($(this).val()) === selectedVal) {
                isDuplicate = true;
            }
        });

        if (isDuplicate) {
            alert('This product is already selected in another row. Please select a different product.');
            $(currentSelect).val('').trigger('change');
        }
    }

    $(document).ready(function() {
        $('.select2').select2({ width: 'resolve' });

        // Validate all existing selects also
        $('.product-select').on('change', function () {
            validateDuplicateProduct(this);
        });
    });
</script>