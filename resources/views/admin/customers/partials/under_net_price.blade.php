<hr class="dark-line">
@php
    $productTypes = product_types();
@endphp

<div class="form-group">
    <label class="col-md-12">
        <p class="highlight-sub-label">Products Under Net Price Model</p>
    </label>
    <br><br>
    <div class="col-md-12">
        <table class="table table-bordered" id="netPriceProductsTable">
            <thead>
                <tr style="background:#f9f9f9;">
                    <th>S.No.</th>
                    <th width="15%">Product Type</th>
                    <th width="15%">Product Name</th>
                    <th>Packing Type</th>
                    <th>MOQ (kg)</th>
                    <th>Net Price (Rs.)</th>
                    <th>For Qty (kg)</th>
                    <th width="20%">Select</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" class="btn btn-primary"
                onclick="addNetPriceProductRow()">Add New</button>
    </div>
</div>

@php
$productTypesJson = json_encode($productTypes);
$existingNetProductsJson = json_encode($existingNetProducts ?? []);
@endphp

<script>

let netRowIndex = 0;
const productTypes = {!! $productTypesJson !!};
const existingNetProducts = {!! $existingNetProductsJson !!};

$(document).ready(function() {

    if (existingNetProducts.length > 0) {
        existingNetProducts.forEach(function(product) {
            addNetPriceProductRow(product);
        });
    }
});

/* ========================================= */
/* ADD ROW */
/* ========================================= */
function addNetPriceProductRow(productData = null) {

    netRowIndex++;

    let typeOptions = '<option value="">Select Type</option>';
    for (const [key, value] of Object.entries(productTypes)) {
        typeOptions += `<option value="${key}"
                ${(productData && productData.product_type == key) ? 'selected' : ''}>
                ${value}
            </option>`;
    }

    const row = $(`
        <tr>
            <td>${netRowIndex}</td>

            <td>
                <select class="form-control product-type-select"
                        name="net_products[${netRowIndex}][product_type]" required>
                    ${typeOptions}
                </select>
            </td>

            <td>
                <select class="form-control product-name-select"
                        name="net_products[${netRowIndex}][product_id]"
                        style="min-width:160px;" required>
                    <option value="">Select Product</option>
                </select>
                <small class="text-success market-price-display"></small>
            </td>

            <td>
                <select name="net_products[${netRowIndex}][packing_type]"
                        class="form-control packing-type-select"
                        data-saved="${productData ? productData.packing_type ?? '' : ''}">
                    <option value="">Please Select</option>
                    <option value="standard"
                        ${(productData && productData.packing_type=='standard')?'selected':''}>
                        Standard Packing
                    </option>
                </select>
            </td>

            <td>
                <input type="number"
                       name="net_products[${netRowIndex}][moq]"
                       class="form-control"
                       step="0.001"
                       min="0"
                       value="${productData ? productData.moq : ''}" required>
            </td>

            <td>
                <input type="number"
                       name="net_products[${netRowIndex}][net_price]"
                       class="form-control net-price-input"
                       step="0.01"
                       min="0"
                       value="${productData ? productData.net_price : ''}" required>
                <small class="text-primary net-price-display"></small>
            </td>

            <td>
                <input type="number"
                       name="net_products[${netRowIndex}][for_qty]"
                       class="form-control"
                       step="0.001"
                       min="0"
                       value="${productData ? productData.for_qty ?? '' : ''}">
            </td>

            <td>
                <div style="display:flex; gap:5px;">
                    <select name="net_products[${netRowIndex}][applicable_type]"
                            class="form-control applicable-type-select">
                        <option value="net_price"
                            ${(productData && productData.applicable_type=='net_price')?'selected':''}>
                            Net Price
                        </option>
                        <option value="percentage"
                            ${(productData && productData.applicable_type=='percentage')?'selected':''}>
                            % Discount
                        </option>
                    </select>

                    <input type="number"
                           name="net_products[${netRowIndex}][value]"
                           class="form-control value-input"
                           step="0.01"
                           min="0"
                           value="${productData ? productData.value ?? '' : ''}">
                </div>
                <small class="text-primary calculated-display"></small>
            </td>

            <td>
                <button type="button"
                        class="btn btn-danger btn-sm"
                        onclick="removeNetPriceRow(this)">
                    <i class="fa fa-times"></i>
                </button>
            </td>
        </tr>
    `);

    $('#netPriceProductsTable tbody').append(row);
    row.find('.product-name-select').select2({ width: 'resolve' });

    /* EVENTS */
    row.on('change','.product-type-select',function(){
        loadProductsByType(this);
    });

    row.on('change','.product-name-select',function(){
        updateMarketPrice(row);
        updatePackingOptions(row);
    });

    row.on('change','.packing-type-select',function(){
        validateDuplicateCombination(row);
    });

    row.on('input change',
        '.net-price-input, .value-input, .applicable-type-select',
        function(){ updateCalculation(row); });

    /* EDIT MODE */
    if (productData) {

        loadProductsByType(
            row.find('.product-type-select')[0],
            productData.product_id
        );

        setTimeout(function(){
            updatePackingOptions(row, productData.packing_type);
            updateCalculation(row);
        },400);
    }
}

/* ========================================= */
/* LOAD PRODUCTS */
/* ========================================= */
function loadProductsByType(selectElement, selectedProductId=null){

    const row = $(selectElement).closest('tr');
    const productNameSelect = row.find('.product-name-select');
    const selectedType = $(selectElement).val();

    if(!selectedType) return;

    $.ajax({
        url:'{{ route('customer.fetch.products.by.type') }}',
        type:'GET',
        data:{ type:selectedType },
        success:function(response){

            let options='<option value="">Select Product</option>';

            response.forEach(function(product){

                let marketPrice=0;
                if(product.pricings && product.pricings.length>0){
                    marketPrice=product.pricings[0].market_price;
                }

                options+=`<option value="${product.id}"
                                  data-market="${marketPrice}"
                                  data-physical="${product.physical_form}"
                                  ${(selectedProductId && selectedProductId==product.id)?'selected':''}>
                                  ${product.product_name}
                              </option>`;
            });

            productNameSelect.html(options).trigger('change');
        }
    });
}

/* ========================================= */
/* MARKET PRICE */
/* ========================================= */
function updateMarketPrice(row){

    const selected=row.find('.product-name-select option:selected');
    const marketPrice=selected.data('market');

    if(marketPrice){
        row.find('.market-price-display')
           .html("Rs. "+parseFloat(marketPrice).toFixed(2));
    } else {
        row.find('.market-price-display').html('');
    }
}

/* ========================================= */
/* PACKING OPTIONS */
/* ========================================= */
function updatePackingOptions(row,savedPacking=null){

    const selected=row.find('.product-name-select option:selected');
    const physicalForm=selected.data('physical');
    const packingSelect=row.find('.packing-type-select');

    let options=`<option value="">Please Select</option>
                 <option value="standard">Standard Packing</option>`;

    if(physicalForm==='Liquid'){
        options+=`<option value="1kg*10">1kg * 10</option>
                  <option value="5kg*2">5kg * 2</option>`;
    }

    if(physicalForm==='Powder'){
        options+=`<option value="1kg*12">1kg * 12</option>`;
    }

    packingSelect.html(options);

    if(savedPacking){
        packingSelect.val(savedPacking);
    }
}

/* ========================================= */
/* DUPLICATE VALIDATION */
/* ========================================= */
function validateDuplicateCombination(currentRow){

    const product=currentRow.find('.product-name-select').val();
    const packing=currentRow.find('.packing-type-select').val();

    if(!product || !packing) return;

    let duplicate=false;

    $('#netPriceProductsTable tbody tr').each(function(){

        if($(this)[0]===currentRow[0]) return;

        const otherProduct=$(this).find('.product-name-select').val();
        const otherPacking=$(this).find('.packing-type-select').val();

        if(product===otherProduct && packing===otherPacking){
            duplicate=true;
        }
    });

    if(duplicate){
        alert('Same product with same packing type already exists.');
        currentRow.find('.packing-type-select').val('');
    }
}

/* ========================================= */
/* CALCULATION */
/* ========================================= */
function updateCalculation(row){

    const netPrice=parseFloat(row.find('.net-price-input').val())||0;
    const value=parseFloat(row.find('.value-input').val())||0;
    const type=row.find('.applicable-type-select').val();

    row.find('.net-price-display')
       .html(netPrice?"Rs. "+netPrice.toFixed(2):'');

    if(!value){
        row.find('.calculated-display').html('');
        return;
    }

    if(type==='net_price'){
        row.find('.calculated-display')
           .html("Rs. "+value.toFixed(2));
    }

    if(type==='percentage'){
        const result=netPrice-(netPrice*value/100);
        row.find('.calculated-display')
           .html("Rs. "+result.toFixed(2));
    }
}

/* ========================================= */
function removeNetPriceRow(btn){
    $(btn).closest('tr').remove();
}
</script>
