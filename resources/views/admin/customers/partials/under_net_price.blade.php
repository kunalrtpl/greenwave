<br>
@php
    $productTypes = product_types();
@endphp

<div class="form-group">
    <label class="col-md-12">
        Products Under Net Price Model
    </label>
    <div class="col-md-12">
        <table class="table table-bordered" id="netPriceProductsTable">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th>S.No.</th>
                    <th width="30%">Product Type</th>
                    <th width="30%">Product Name</th>
                    <th>MOQ</th>
                    <th>Net Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Rows will be dynamically added or prepopulated --}}
            </tbody>
        </table>

        <button type="button" class="btn btn-primary" onclick="addNetPriceProductRow()">Add New</button>
    </div>
</div>

@php
    $productTypesJson = json_encode($productTypes);
    $existingNetProductsJson = json_encode($existingNetProducts ?? []);
@endphp

<script>
    let netRowIndex = document.querySelectorAll('#netPriceProductsTable tbody tr').length;
    const productTypes = {!! $productTypesJson !!};
    const existingNetProducts = {!! $existingNetProductsJson !!};

    $(document).ready(function() {
        // Initialize select2 for the dynamic rows
        $('.product-name-select').select2({ width: 'resolve' });

        $('.product-name-select').on('change', function () {
            validateDuplicateNetProduct(this);
        });

        // Prepopulate existing net products when editing
        if (existingNetProducts.length > 0) {
            existingNetProducts.forEach(function(product) {
                addNetPriceProductRow(product);
            });
        }
    });

    function addNetPriceProductRow(productData = null) {
        netRowIndex++;
        const tableBody = document.querySelector('#netPriceProductsTable tbody');

        let typeOptions = '<option value="">Select Type</option>';
        for (const [key, value] of Object.entries(productTypes)) {
            typeOptions += `<option value="${key}" ${(productData && productData.product_type == key) ? 'selected' : ''}>${value}</option>`;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${netRowIndex}</td>
            <td>
                <select class="form-control product-type-select" name="net_products[${netRowIndex}][product_type]" required>
                    ${typeOptions}
                </select>
            </td>
            <td>
                <select class="form-control product-name-select" name="net_products[${netRowIndex}][product_id]" style="min-width: 250px;" required>
                    <option value="">Select Product</option>
                </select>
            </td>
            <td>
                <input type="number" name="net_products[${netRowIndex}][moq]" class="form-control" step="0.001" min="0" value="${productData ? productData.moq : ''}" placeholder="Enter MOQ" required>
            </td>
            <td>
                <input type="number" name="net_products[${netRowIndex}][net_price]" class="form-control" step="0.01" min="0" value="${productData ? productData.net_price : ''}" placeholder="Enter Net Price" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeNetPriceRow(this)"><i class="fa fa-times"></i></button>
            </td>
        `;

        tableBody.appendChild(row);

        const $productTypeSelect = $(row).find('.product-type-select');
        const $productNameSelect = $(row).find('.product-name-select');

        $productNameSelect.select2({ width: 'resolve' });

        $productTypeSelect.on('change', function () {
            loadProductsByType(this);
        });

        $productNameSelect.on('change', function () {
            validateDuplicateNetProduct(this);
        });

        // If preloading data, load product names based on selected type
        if (productData) {
            loadProductsByType($productTypeSelect[0], productData.product_id);
        }
    }

    function removeNetPriceRow(button) {
        const row = button.closest('tr');
        row.remove();
        updateNetRowNumbers();
    }

    function updateNetRowNumbers() {
        $('#netPriceProductsTable tbody tr').each(function(i, row) {
            $(row).find('td:first').text(i + 1);
        });
        netRowIndex = $('#netPriceProductsTable tbody tr').length;
    }

    function loadProductsByType(selectElement, selectedProductId = null) {
        const selectedType = $(selectElement).val();
        const row = $(selectElement).closest('tr');
        const productNameSelect = row.find('.product-name-select');

        productNameSelect.html('<option value="">Loading...</option>').trigger('change');

        if (!selectedType) {
            productNameSelect.html('<option value="">Select Product</option>').trigger('change');
            return;
        }

        $.ajax({
            url: '{{ route('customer.fetch.products.by.type') }}',
            type: 'GET',
            data: { type: selectedType },
            success: function(response) {
                let options = '<option value="">Select Product</option>';

                response.forEach(function(product) {
                    options += `<option value="${product.id}" ${(selectedProductId && selectedProductId == product.id) ? 'selected' : ''}>${product.product_name}</option>`;
                });

                productNameSelect.html(options).trigger('change');
            },
            error: function() {
                alert('Failed to fetch products. Please try again.');
                productNameSelect.html('<option value="">Select Product</option>').trigger('change');
            }
        });
    }

    function validateDuplicateNetProduct(currentSelect) {
        const selectedVal = $(currentSelect).val();
        if (!selectedVal) return;

        let isDuplicate = false;
        $('.product-name-select').not(currentSelect).each(function () {
            if ($(this).val() === selectedVal) {
                isDuplicate = true;
            }
        });

        if (isDuplicate) {
            alert('This product is already selected in another row. Please select a different product.');
            $(currentSelect).val('').trigger('change');
        }
    }
</script>
