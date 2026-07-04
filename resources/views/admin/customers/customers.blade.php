@extends('layouts.adminLayout.backendLayout')
@section('content')
<style>
    .cust-wrap {
        --brand:#2f7de1; --brand-dark:#1f5fb3;
        --line:#e3e8ef; --line-strong:#d5dce6;
        --muted:#7a8598; --ink:#2b3648; --bg-soft:#f7f9fc;
        --green:#1e8f4e; --red:#c0392b; --amber:#e08e0b; --purple:#7c5cd6;
    }
    .cust-card {
        background:#fff; border-radius:16px;
        box-shadow:0 4px 20px rgba(30,50,90,.07);
        padding:24px 26px; margin-bottom:22px;
    }
    .cust-head {
        display:flex; align-items:center; justify-content:space-between;
        flex-wrap:wrap; gap:12px; margin-bottom:20px;
        padding-bottom:18px; border-bottom:1px solid var(--line);
    }
    .cust-head h2 {
        font-size:22px; font-weight:700; color:var(--brand);
        margin:0; display:flex; align-items:center; gap:10px;
    }
    .btn-add {
        background:var(--brand); color:#fff; border:none;
        padding:10px 20px; border-radius:9px; font-weight:600;
        font-size:13px; text-decoration:none; transition:.15s;
        box-shadow:0 2px 8px rgba(47,125,225,.3);
    }
    .btn-add:hover { background:var(--brand-dark); color:#fff; box-shadow:0 4px 12px rgba(47,125,225,.4); }

    /* Stat / breakdown pills */
    .stat-row { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:20px; }
    .stat-pill {
        display:flex; align-items:center; gap:9px; flex:1; min-width:150px;
        background:var(--bg-soft); border:1px solid var(--line);
        border-radius:12px; padding:14px 18px; font-size:13px;
        color:var(--muted); font-weight:600; transition:.15s;
    }
    .stat-pill:hover { border-color:var(--line-strong); box-shadow:0 2px 8px rgba(30,50,90,.06); }
    .stat-pill.clickable { cursor:pointer; }
    .stat-pill.clickable.active { border-color:var(--brand); background:#eef5ff; }
    .stat-pill .dot { width:11px; height:11px; border-radius:50%; flex-shrink:0; }
    .stat-pill .lbl { flex:1; }
    .stat-pill b { color:var(--ink); font-size:19px; font-weight:700; }

    /* Filter bar */
    .filter-bar {
        background:var(--bg-soft); border:1px solid var(--line);
        border-radius:14px; padding:18px 20px; margin-bottom:22px;
    }
    .filter-grid {
        display:grid; grid-template-columns:repeat(auto-fit,minmax(155px,1fr));
        gap:14px 16px; align-items:end;
    }
    .filter-field label {
        display:block; font-size:11px; font-weight:700; text-transform:uppercase;
        letter-spacing:.5px; color:var(--muted); margin-bottom:6px;
    }
    .filter-field input, .filter-field select {
        width:100%; height:40px; border:1px solid var(--line-strong);
        border-radius:9px; padding:0 12px; font-size:13px; color:var(--ink);
        background:#fff; outline:none; transition:.15s;
    }
    .filter-field input:focus, .filter-field select:focus {
        border-color:var(--brand); box-shadow:0 0 0 3px rgba(47,125,225,.13);
    }
    .filter-actions { display:flex; gap:8px; }
    .btn-search, .btn-reset {
        height:40px; border:none; border-radius:9px; padding:0 18px;
        font-size:13px; font-weight:600; cursor:pointer; transition:.15s;
        display:flex; align-items:center; gap:7px; white-space:nowrap;
    }
    .btn-search { background:var(--brand); color:#fff; box-shadow:0 2px 8px rgba(47,125,225,.3); }
    .btn-search:hover { background:var(--brand-dark); }
    .btn-reset { background:#fff; color:var(--muted); border:1px solid var(--line-strong); }
    .btn-reset:hover { background:#eef1f6; }

    /* Table — WITH BORDERS */
    .cust-table-wrap { overflow-x:auto; border:1px solid var(--line-strong); border-radius:12px; }
    table.cust-table {
        width:100%; border-collapse:collapse; font-size:13px; color:var(--ink);
    }
    table.cust-table thead th {
        background:var(--bg-soft); text-align:left; padding:13px 14px;
        font-size:11px; font-weight:700; text-transform:uppercase;
        letter-spacing:.5px; color:var(--muted);
        border:1px solid var(--line-strong); white-space:nowrap;
    }
    table.cust-table thead th.col-id { width:54px; text-align:center; }
    table.cust-table thead th.col-center { text-align:center; }
    table.cust-table tbody td {
        padding:12px 14px; border:1px solid var(--line);
        vertical-align:middle;
    }
    table.cust-table tbody tr:nth-child(even) { background:#fafbfd; }
    table.cust-table tbody tr:hover { background:#f2f8ff; }
    table.cust-table td.col-id {
        text-align:center; color:var(--muted); font-weight:700; width:54px;
    }
    table.cust-table td.col-center { text-align:center; }
    .cust-name { font-weight:600; color:var(--ink); }

    .badge-linking {
        display:inline-block; padding:4px 12px; border-radius:20px;
        font-size:12px; font-weight:600; white-space:nowrap;
    }
    .badge-open   { background:#fdecec; color:var(--red); }
    .badge-direct { background:#e8f6ee; color:var(--green); }
    .badge-hybrid { background:#f3eefc; color:var(--purple); }
    .badge-dealer { background:#eef3fb; color:var(--brand); }
    .badge-muted  { background:#eef1f5; color:var(--muted); }

    .ic-tick  { color:var(--green); font-size:16px; font-weight:700; }
    .ic-cross { color:var(--red);   font-size:16px; font-weight:700; }

    /* Proper status toggle switch */
    .switch {
        position:relative; display:inline-flex; align-items:center;
        cursor:pointer; user-select:none;
    }
    .switch .track {
        width:70px; height:26px; border-radius:14px; position:relative;
        transition:.2s; display:flex; align-items:center;
    }
    .switch.on  .track { background:var(--green); }
    .switch.off .track { background:#b8c0cc; }
    .switch .knob {
        position:absolute; top:3px; width:20px; height:20px; border-radius:50%;
        background:#fff; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,.25);
    }
    .switch.on  .knob { left:47px; }
    .switch.off .knob { left:3px; }
    .switch .txt {
        font-size:10px; font-weight:700; color:#fff; text-transform:uppercase;
        letter-spacing:.3px; width:100%; text-align:center; padding:0 6px;
    }
    .switch.on  .txt { padding-right:22px; }
    .switch.off .txt { padding-left:22px; }

    .btn-edit {
        display:inline-flex; align-items:center; justify-content:center;
        width:36px; height:36px; border-radius:9px;
        background:#e8f6ee; color:var(--green); text-decoration:none; transition:.15s;
    }
    .btn-edit:hover { background:var(--green); color:#fff; }

    /* Footer / pagination */
    .cust-foot {
        display:flex; align-items:center; justify-content:space-between;
        flex-wrap:wrap; gap:12px; margin-top:18px;
    }
    .cust-foot .info { font-size:13px; color:var(--muted); font-weight:500; }
    .pager { display:flex; gap:6px; flex-wrap:wrap; }
    .pager button {
        min-width:38px; height:38px; padding:0 11px; border:1px solid var(--line-strong);
        background:#fff; color:var(--ink); border-radius:9px; font-size:13px;
        cursor:pointer; transition:.15s; font-weight:600;
    }
    .pager button:hover:not(:disabled) { background:#eef1f6; border-color:var(--brand); }
    .pager button.active { background:var(--brand); color:#fff; border-color:var(--brand); }
    .pager button:disabled { opacity:.4; cursor:not-allowed; }

    .cust-empty { text-align:center; padding:44px; color:var(--muted); font-size:14px; }
    .cust-loading { text-align:center; padding:44px; color:var(--muted); }
    /* Proper status toggle switch */
    .cust-switch {
        position:relative; display:inline-flex; align-items:center;
        cursor:pointer; user-select:none;
    }
    .cust-switch .track {
        width:70px; height:26px; border-radius:14px; position:relative;
        transition:.2s; display:flex; align-items:center;
    }
    .cust-switch.on  .track { background:var(--green); }
    .cust-switch.off .track { background:#b8c0cc; }
    .cust-switch .knob {
        position:absolute; top:3px; width:20px; height:20px; border-radius:50%;
        background:#fff; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,.25);
    }
    .cust-switch.on  .knob { left:47px; }
    .cust-switch.off .knob { left:3px; }
    .cust-switch .txt {
        font-size:10px; font-weight:700; color:#fff; text-transform:uppercase;
        letter-spacing:.3px; width:100%; text-align:center; padding:0 6px;
    }
    .cust-switch.on  .txt { padding-right:22px; }
    .cust-switch.off .txt { padding-left:22px; }
</style>

<div class="page-content-wrapper">
    <div class="page-content cust-wrap">

        <div class="page-head">
            <div class="page-title"><h1>Customers Management</h1></div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
        </ul>

        @if(Session::has('flash_message_error'))
            <div role="alert" class="alert alert-danger alert-dismissible fade in">
                <button aria-label="Close" data-dismiss="alert" style="text-indent:0;" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                <strong>Error!</strong> {!! session('flash_message_error') !!}
            </div>
        @endif
        @if(isset($_GET['s']))
            <div role="alert" class="alert alert-success alert-dismissible fade in">
                <button aria-label="Close" data-dismiss="alert" style="text-indent:0;" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                <strong>Success!</strong> Record has been updated successfully.
            </div>
        @endif

        <div class="cust-card">

            <div class="cust-head">
                <h2><i class="fa fa-users"></i> Customers</h2>
                <a href="{{ action('Admin\CustomerController@addEditCustomer') }}" class="btn-add">
                    <i class="fa fa-plus"></i>&nbsp; Add Customer
                </a>
            </div>

            <!-- Breakdown stat pills -->
            <div class="stat-row" id="statRow">
                <div class="stat-pill"><span class="dot" style="background:#2f7de1;"></span><span class="lbl">Total</span><b id="stTotal">—</b></div>
                <div class="stat-pill clickable" data-filter="Direct Customer"><span class="dot" style="background:#1e8f4e;"></span><span class="lbl">Direct</span><b id="stDirect">—</b></div>
                <div class="stat-pill clickable" data-filter="Open"><span class="dot" style="background:#c0392b;"></span><span class="lbl">Open</span><b id="stOpen">—</b></div>
                <div class="stat-pill clickable" data-filter="Hybrid"><span class="dot" style="background:#7c5cd6;"></span><span class="lbl">Hybrid</span><b id="stHybrid">—</b></div>
                <div class="stat-pill clickable" data-filter="__dealer"><span class="dot" style="background:#e08e0b;"></span><span class="lbl">Dealer&nbsp;Linked</span><b id="stDealer">—</b></div>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <div class="filter-grid">
                    <div class="filter-field">
                        <label>Customer Name</label>
                        <input type="text" id="f_name" placeholder="Search name...">
                    </div>
                    <div class="filter-field">
                        <label>City</label>
                        <input type="text" id="f_city" placeholder="City...">
                    </div>
                    <div class="filter-field">
                        <label>Business Linking</label>
                        <select id="f_linking">
                            <option value="All">All</option>
                            <option value="Direct Customer">Direct Customer</option>
                            <option value="Open">Open</option>
                            <option value="Hybrid">Hybrid</option>
                            <optgroup label="Dealers">
                                @foreach($linkedDealers as $dealer)
                                    <option value="{{ $dealer->id }}">{{ $dealer->business_name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Linked Executive</label>
                        <select id="f_exec">
                            <option value="All">All</option>
                            @foreach($executives as $executive)
                                <option value="{{ $executive->name }}">{{ $executive->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Email</label>
                        <select id="f_email">
                            <option value="All">All</option>
                            <option value="tick">Has Email</option>
                            <option value="cross">No Email</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>B. Card</label>
                        <select id="f_bcard">
                            <option value="All">All</option>
                            <option value="tick">Has Card</option>
                            <option value="cross">No Card</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>Status</label>
                        <select id="f_status">
                            <option value="All">All</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>&nbsp;</label>
                        <div class="filter-actions">
                            <button class="btn-search" id="btnSearch"><i class="fa fa-search"></i> Search</button>
                            <button class="btn-reset" id="btnReset"><i class="fa fa-refresh"></i> Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="cust-table-wrap">
                <table class="cust-table">
                    <thead>
                        <tr>
                            <th class="col-id">Id</th>
                            <th>Customer Name</th>
                            <th>City</th>
                            <th>Business Linking</th>
                            <th>Linked Executive</th>
                            <th class="col-center">Email</th>
                            <th class="col-center">B. Card</th>
                            <th class="col-center">Status</th>
                            <th class="col-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="custBody">
                        <tr><td colspan="9" class="cust-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="cust-foot">
                <div class="info" id="custInfo"></div>
                <div class="pager" id="custPager"></div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    window.history.pushState("", "", "/admin/customers");

    (function () {
        var ajaxUrl = "{{ url('admin/customers') }}";
        var toggleUrl = "{{ url('admin/customer-status-toggle') }}";
        var csrf    = "{{ csrf_token() }}";
        var currentPage = 1;

        function getFilters() {
            return {
                name:             document.getElementById('f_name').value,
                city_name:        document.getElementById('f_city').value,
                business_linking: document.getElementById('f_linking').value,
                linked_executive: document.getElementById('f_exec').value,
                email_status:     document.getElementById('f_email').value,
                b_card_status:    document.getElementById('f_bcard').value,
                status:           document.getElementById('f_status').value
            };
        }

        function load(page) {
            currentPage = page || 1;
            var body = document.getElementById('custBody');
            body.innerHTML = '<tr><td colspan="9" class="cust-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

            var params = getFilters();
            params.page = currentPage;
            var qs = Object.keys(params).map(function (k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
            }).join('&');

            fetch(ajaxUrl + '?' + qs, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
            })
            .then(function (r) { return r.json(); })
            .then(function (res) { render(res); })
            .catch(function () {
                body.innerHTML = '<tr><td colspan="9" class="cust-empty">Failed to load records.</td></tr>';
            });
        }

        function tick(v) {
            return v ? '<span class="ic-tick">&#10004;</span>' : '<span class="ic-cross">&#10006;</span>';
        }
        function switchHtml(id, on) {
            var cls = on ? 'on' : 'off';
            var txt = on ? 'Active' : 'Inactive';
            return '<span class="cust-switch ' + cls + '" data-id="' + id + '" data-on="' + (on?1:0) + '">' +
                        '<span class="track"><span class="txt">' + txt + '</span><span class="knob"></span></span>' +
                   '</span>';
        }

        function render(res) {
            var body = document.getElementById('custBody');
            body.innerHTML = '';

            if (res.counts) {
                document.getElementById('stTotal').textContent  = res.counts.total;
                document.getElementById('stDirect').textContent = res.counts.direct;
                document.getElementById('stOpen').textContent   = res.counts.open;
                document.getElementById('stHybrid').textContent = res.counts.hybrid;
                document.getElementById('stDealer').textContent = res.counts.dealer;
            }

            if (!res.data || res.data.length === 0) {
                body.innerHTML = '<tr><td colspan="9" class="cust-empty">No customers found.</td></tr>';
                document.getElementById('custInfo').textContent = '';
                document.getElementById('custPager').innerHTML = '';
                return;
            }

            res.data.forEach(function (c) {
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td class="col-id">' + c.id + '</td>' +
                    '<td><span class="cust-name">' + c.name + '</span></td>' +
                    '<td>' + (c.city || '<span style="color:#b8c0cc;">—</span>') + '</td>' +
                    '<td>' + c.linking + '</td>' +
                    '<td>' + (c.executive || '<span style="color:#b8c0cc;">—</span>') + '</td>' +
                    '<td class="col-center">' + tick(c.email) + '</td>' +
                    '<td class="col-center">' + tick(c.b_card) + '</td>' +
                    '<td class="col-center">' + switchHtml(c.id, c.status) + '</td>' +
                    '<td class="col-center"><a class="btn-edit" href="' + c.edit_url + '" title="Edit"><i class="fa fa-edit"></i></a></td>';
                body.appendChild(tr);
            });

            document.getElementById('custInfo').textContent =
                'Showing ' + res.from + '–' + res.to + ' of ' + res.total + ' records';

            renderPager(res.current_page, res.last_page);
            bindToggles();
        }

        function renderPager(cur, last) {
            var pager = document.getElementById('custPager');
            pager.innerHTML = '';
            if (last <= 1) return;
            function btn(label, page, opts) {
                opts = opts || {};
                var b = document.createElement('button');
                b.innerHTML = label;
                if (opts.active) b.className = 'active';
                if (opts.disabled) b.disabled = true;
                else b.onclick = function () { load(page); window.scrollTo({top:0,behavior:'smooth'}); };
                return b;
            }
            pager.appendChild(btn('&laquo;', cur - 1, { disabled: cur === 1 }));
            var start = Math.max(1, cur - 2), end = Math.min(last, cur + 2);
            if (start > 1) { pager.appendChild(btn('1', 1)); if (start > 2) pager.appendChild(btn('…', 0, {disabled:true})); }
            for (var i = start; i <= end; i++) pager.appendChild(btn(String(i), i, { active: i === cur }));
            if (end < last) { if (end < last - 1) pager.appendChild(btn('…', 0, {disabled:true})); pager.appendChild(btn(String(last), last)); }
            pager.appendChild(btn('&raquo;', cur + 1, { disabled: cur === last }));
        }

        // Status toggle — flips UI + hits your existing endpoint.
        function bindToggles() {
            document.querySelectorAll('.cust-switch').forEach(function (el) {
                if (el.dataset.bound === '1') return;
                el.dataset.bound = '1';

                el.addEventListener('click', function () {
                    if (el.dataset.busy === '1') return;
                    el.dataset.busy = '1';

                    var id = el.getAttribute('data-id');
                    var newVal = el.classList.contains('on') ? 0 : 1;

                    el.classList.toggle('on');
                    el.classList.toggle('off');
                    el.querySelector('.txt').textContent = newVal ? 'Active' : 'Inactive';

                    fetch(toggleUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: 'id=' + encodeURIComponent(id) + '&status=' + newVal
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (!res.status) throw new Error(res.message || 'Update failed');
                        el.dataset.busy = '0';
                    })
                    .catch(function () {
                        el.classList.toggle('on');
                        el.classList.toggle('off');
                        el.querySelector('.txt').textContent = newVal ? 'Inactive' : 'Active';
                        el.dataset.busy = '0';
                        alert('Failed to update status.');
                    });
                });
            });
        }

        // Clickable stat pills → filter by business linking
        document.querySelectorAll('.stat-pill.clickable').forEach(function (pill) {
            pill.addEventListener('click', function () {
                var val = pill.getAttribute('data-filter');
                var sel = document.getElementById('f_linking');
                if (val === '__dealer') { sel.value = 'All'; }
                else { sel.value = val; }
                document.querySelectorAll('.stat-pill.clickable').forEach(function(p){ p.classList.remove('active'); });
                pill.classList.add('active');
                load(1);
            });
        });

        document.getElementById('btnSearch').onclick = function () { load(1); };
        document.getElementById('btnReset').onclick = function () {
            ['f_name','f_city'].forEach(function(id){ document.getElementById(id).value=''; });
            ['f_linking','f_exec','f_email','f_bcard','f_status'].forEach(function(id){ document.getElementById(id).value='All'; });
            document.querySelectorAll('.stat-pill.clickable').forEach(function(p){ p.classList.remove('active'); });
            load(1);
        };
        ['f_name','f_city'].forEach(function (id) {
            document.getElementById(id).addEventListener('keyup', function (e) { if (e.key === 'Enter') load(1); });
        });

        load(1);
    })();
</script>
@stop