{{-- Design tokens for the revamped sampling module.
     Scoped to .sdx so both the request screens and the dispatch screens can
     wrap their content in <div class="sdx"> and share one stylesheet. --}}
<style>
   .sdx{
   --ink:#14241c;--ink2:#3a4a42;--muted:#7b8a82;--line:#e6ebe8;--line2:#eef2f0;
   --g:#1f7a44;--gd:#155e34;--gsoft:#e8f3ec;--gsoft2:#f1f8f3;
   --amber:#9a6a12;--amberS:#fdf4e3;--blue:#1f5e9a;--blueS:#e9f1fa;--red:#d14343;--redS:#fdeceb;
   --slate:#55636d;--slateS:#eef1f3;--violet:#5b3bbd;--violetS:#efeafe;
   --r:11px;--rs:8px;--mono:'JetBrains Mono',Menlo,Consolas,monospace;
   font-family:'Inter','Segoe UI',system-ui,sans-serif;color:var(--ink);
   }
   .sdx *{box-sizing:border-box}

   /* ── Page head ─────────────────────────────────────────────── */
   .sdx .sdx-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}
   .sdx .sdx-head h1{font-size:21px;font-weight:800;margin:0 0 6px;letter-spacing:-.3px;color:var(--ink)}
   .sdx .sdx-meta{color:var(--muted);font-size:12.5px}
   .sdx .sdx-tools{display:flex;gap:8px;flex-wrap:wrap}

   /* ── Status tabs ───────────────────────────────────────────── */
   .sdx .sdx-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px;border-bottom:1px solid var(--line);padding-bottom:10px}
   .sdx .sdx-tabs a{display:inline-flex;align-items:center;gap:8px;padding:8px 15px;border-radius:9px;font-size:13.5px;font-weight:600;color:var(--ink2);text-decoration:none;border:1.4px solid transparent;transition:background-color .15s,color .15s,border-color .15s}
   .sdx .sdx-tabs a:hover{background:#f4f6f5;color:var(--ink)}
   .sdx .sdx-tabs a.on{background:var(--gsoft);color:var(--gd);border-color:#cfe6d8}
   .sdx .sdx-tabs a .n{font-family:var(--mono);font-size:11.5px;background:#fff;border:1px solid var(--line);border-radius:20px;padding:1px 8px}
   .sdx .sdx-tabs a.on .n{border-color:#cfe6d8}

   /* ── Status badges: the status filter, one clean URL each ──── */
   .sdx .sdx-badges{display:flex;gap:8px;flex-wrap:wrap}
   .sdx .sdx-badges .sdx-badge{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:30px;font-size:12.5px;font-weight:600;text-decoration:none;border:1.4px solid var(--line);background:#fff;color:var(--ink2);transition:background-color .15s,color .15s,border-color .15s}
   .sdx .sdx-badges .sdx-badge:hover{border-color:#c6d2cc;color:var(--ink)}
   .sdx .sdx-badges .sdx-badge .n{font-family:var(--mono);font-size:11px;background:#f1f4f2;border-radius:20px;padding:1px 8px;color:var(--ink2)}
   .sdx .sdx-badges .sdx-badge.on{background:var(--gsoft);border-color:#cfe6d8;color:var(--gd)}
   .sdx .sdx-badges .sdx-badge.on.pending{background:var(--amberS);border-color:#f0e0bd;color:var(--amber)}
   .sdx .sdx-badges .sdx-badge.on.approved{background:var(--blueS);border-color:#cfe0f0;color:var(--blue)}
   .sdx .sdx-badges .sdx-badge.on.declined{background:var(--redS);border-color:#f3c9c6;color:var(--red)}
   .sdx .sdx-badges .sdx-badge.on .n{background:#fff}

   /* A value shown for reference rather than edited — same box as an input
      so it lines up with the fields beside it. */
   .sdx .sdx-readonly{border:1.4px dashed var(--line);border-radius:var(--rs);background:#f8faf9;padding:8px 11px;font-size:13px;color:var(--ink);min-height:34px}

   /* ── Filter bar: compact dropdowns, badges take the rest of the
         line, search drops to its own row ───────────────────────── */
   .sdx .sdx-filterbar{display:flex;flex-wrap:wrap;gap:15px 20px;align-items:flex-start}
   .sdx .sdx-filterbar>.sdx-f{flex:0 0 230px;max-width:230px}
   .sdx .sdx-filterbar>.sdx-f.badges{flex:1 1 300px;max-width:none}
   .sdx .sdx-filterbar>.sdx-f.search{flex:1 1 100%;max-width:none}
   @media(max-width:700px){.sdx .sdx-filterbar>.sdx-f{flex:1 1 100%;max-width:none}}

   /* ── Search row: input grows, buttons sit beside it ─────────── */
   .sdx .sdx-search{display:flex;gap:9px;align-items:center;flex-wrap:wrap}
   .sdx .sdx-search>.sdx-in{flex:1 1 auto;min-width:220px}
   .sdx .sdx-search>.sdx-btn{flex:0 0 auto}

   /* ── Chips ─────────────────────────────────────────────────── */
   .sdx .sdx-chip{display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:30px;font-size:10.5px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;white-space:nowrap}
   .sdx .sdx-chip.req{background:var(--blueS);color:var(--blue)}
   .sdx .sdx-chip.vol{background:var(--violetS);color:var(--violet)}
   .sdx .sdx-chip.warn{background:var(--amberS);color:var(--amber)}
   .sdx .sdx-chip.ok{background:var(--gsoft);color:var(--gd)}
   .sdx .sdx-chip.bad{background:var(--redS);color:var(--red)}
   .sdx .sdx-chip.mute{background:var(--slateS);color:var(--slate)}

   /* ── Cards ─────────────────────────────────────────────────── */
   .sdx .sdx-card{background:#fff;border:1px solid var(--line);border-radius:var(--r);box-shadow:0 1px 2px rgba(20,36,28,.04),0 6px 18px rgba(20,36,28,.04);margin-bottom:18px}
   .sdx .sdx-ch{padding:16px 24px;border-bottom:1px solid var(--line2);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
   .sdx .sdx-ch .t{font-size:15px;font-weight:700;display:flex;align-items:center;gap:9px}
   .sdx .sdx-ch .t .ix{width:26px;height:26px;border-radius:7px;background:var(--gsoft);color:var(--gd);display:grid;place-items:center;font-size:12px;flex:none}
   .sdx .sdx-ch .t small{display:block;font-weight:400;color:var(--muted);font-size:12px;margin-top:1px}
   .sdx .sdx-cb{padding:22px 24px}

   /* ── Forms ─────────────────────────────────────────────────── */
   .sdx .sdx-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px 20px}
   .sdx .sdx-grid.g3{grid-template-columns:repeat(3,1fr)}
   .sdx .sdx-grid.g4{grid-template-columns:repeat(4,1fr)}
   /* Challan & transport: a 6-column track so each line fills the card edge to
      edge — three fields of 2 on the first line, two of 3 on the second. */
   .sdx .sdx-grid.sdx-challan{grid-template-columns:repeat(6,1fr)}
   .sdx .sdx-grid.sdx-challan>.sdx-f{grid-column:span 2}
   .sdx .sdx-grid.sdx-challan>.sdx-f.start{grid-column:1/span 2}
   .sdx .sdx-grid.sdx-challan>.sdx-f.rest{grid-column:3/-1}
   .sdx .sdx-grid.sdx-challan>.sdx-f.full{grid-column:1/-1}

   @media(max-width:900px){
   .sdx .sdx-grid.sdx-challan{grid-template-columns:1fr}
   .sdx .sdx-grid.sdx-challan>.sdx-f,.sdx .sdx-grid.sdx-challan>.sdx-f.start,.sdx .sdx-grid.sdx-challan>.sdx-f.rest{grid-column:1/-1}
   }
   .sdx .sdx-f{display:flex;flex-direction:column;gap:5px;min-width:0}
   .sdx .sdx-f.full{grid-column:1/-1}
   /* Transport and the LR no share a line: transport takes only the width its
      buttons need and the LR field starts right after it, running to the edge. */
   .sdx .sdx-f.sdx-transport-row{flex-direction:row;align-items:flex-start;gap:20px}
   .sdx .sdx-f.sdx-transport-row>.sdx-f{flex:0 0 auto}
   .sdx .sdx-f.sdx-transport-row>.sdx-f.grow{flex:1 1 auto;min-width:0}
   @media(max-width:600px){
   .sdx .sdx-f.sdx-transport-row{flex-direction:column;gap:15px}
   .sdx .sdx-f.sdx-transport-row>.sdx-f{width:100%}
   }
   .sdx .sdx-f>label{font-size:11.5px;font-weight:600;color:var(--ink2);text-transform:uppercase;letter-spacing:.3px;margin:0}
   .sdx .sdx-f .rq{color:var(--red)}
   .sdx .sdx-in{height:40px;border:1.4px solid var(--line);border-radius:var(--rs);padding:0 12px;font-size:13.5px;background:#fff;color:var(--ink);width:100%;transition:border-color .15s,box-shadow .15s}
   .sdx .sdx-in:focus{outline:none;border-color:var(--g);box-shadow:0 0 0 3px var(--gsoft)}
   .sdx textarea.sdx-in{height:auto;min-height:84px;padding:10px 12px;resize:vertical}
   .sdx .sdx-in[readonly]{background:#f6f8f7;color:var(--ink2)}
   .sdx .sdx-in.data{font-family:var(--mono);font-size:13px}
   .sdx .sdx-in.sm{height:34px;font-size:13px;padding:0 9px}
   .sdx textarea.sdx-in.sm{height:auto;min-height:56px;padding:7px 9px}
   .sdx .sdx-hint{font-size:11px;color:var(--muted)}
   /* Filter rows: every label sits on one line regardless of which fields
      carry a hint underneath, so the row reads as a single strip. A column
      holding only buttons keeps an empty label so it lines up with the rest. */
   .sdx .sdx-filters{align-items:start}
   .sdx .sdx-filters .sdx-f.act>label{visibility:hidden}
   .sdx .sdx-filters .sdx-f.act>.btns{display:flex;align-items:center;gap:9px;flex-wrap:wrap;min-height:40px}
   .sdx .sdx-filters .sdx-f.act .check{display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--ink2);margin:0;cursor:pointer;font-weight:500;text-transform:none;letter-spacing:0}
   .sdx .sdx-err{font-size:12px;color:var(--red);display:none}
   .sdx .sdx-in.has-err,.sdx .has-err .select2-selection{border-color:var(--red)!important}
   /* The native select select2 hides is absolutely positioned; without this it
      keeps our 100% width and pushes the page into a horizontal scroll. */
   .sdx select.sdx-in.select2-hidden-accessible{width:1px!important;height:1px!important;padding:0!important;border:0!important;overflow:hidden!important;white-space:nowrap!important}
   .sdx .select2-container{width:100%!important}
   .sdx .select2-container .select2-selection--single{height:40px;border:1.4px solid var(--line);border-radius:var(--rs)}
   .sdx .select2-container .select2-selection--single .select2-selection__rendered{line-height:38px;padding-left:12px;font-size:13.5px}
   .sdx .select2-container .select2-selection--single .select2-selection__arrow{height:38px}
   .sdx .sdx-table .select2-container .select2-selection--single,.sdx .sdx-sub .select2-container .select2-selection--single{height:34px}
   .sdx .sdx-table .select2-container .select2-selection--single .select2-selection__rendered,.sdx .sdx-sub .select2-container .select2-selection--single .select2-selection__rendered{line-height:32px;font-size:13px}
   .sdx .sdx-table .select2-container .select2-selection--single .select2-selection__arrow,.sdx .sdx-sub .select2-container .select2-selection--single .select2-selection__arrow{height:32px}
   .sdx .sdx-seg{display:inline-flex;background:#f1f4f2;border:1px solid var(--line);border-radius:9px;padding:3px;gap:3px;align-self:flex-start;flex-wrap:wrap}
   .sdx .sdx-seg label{margin:0;border-radius:7px;font-size:13px;font-weight:600;color:var(--ink2);padding:7px 16px;cursor:pointer;transition:background-color .15s,color .15s,box-shadow .15s}
   .sdx .sdx-seg input{display:none}
   .sdx .sdx-seg label.on{background:#fff;color:var(--gd);box-shadow:0 1px 3px rgba(20,36,28,.1)}
   .sdx .sdx-seg.sm label{padding:5px 12px;font-size:12.5px}

   /* ── Tables ────────────────────────────────────────────────── */
   .sdx .sdx-table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--line);border-radius:var(--rs);overflow:hidden}
   .sdx .sdx-table th{background:#f4f6f5;font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);padding:9px 12px;border-bottom:1px solid var(--line);white-space:nowrap;text-align:left}
   .sdx .sdx-table td{padding:10px 12px;border-bottom:1px solid var(--line2);font-size:13px;vertical-align:top}
   .sdx .sdx-table tr:last-child td{border-bottom:none}
   .sdx .sdx-table tr.locked td{background:#fafbfa;color:var(--muted)}
   .sdx .sdx-table tr.picked td{background:var(--gsoft2)}
   .sdx .sdx-table .num{font-family:var(--mono);white-space:nowrap}
   .sdx .sdx-table .pn{font-weight:600}
   .sdx .sdx-table .pc{font-size:11.5px;color:var(--muted)}
   /* Every table is a fully ruled grid: vertical separators as well as the row
      lines. Deliberately no zebra striping — the rows already use their
      background to mean something (locked, picked). */
   .sdx .sdx-table th,.sdx .sdx-table td{border-right:1px solid var(--line2)}
   .sdx .sdx-table th{border-right-color:var(--line)}
   .sdx .sdx-table th:last-child,.sdx .sdx-table td:last-child{border-right:none}
   /* Percentage column widths are only honoured with a fixed layout. */
   .sdx .sdx-table.fixed{table-layout:fixed}
   .sdx .sdx-table.fixed td{overflow:hidden}

   /* ── Tables built to fit the page ───────────────────────────────
      A sampling listing has a lot of columns, and reading one by dragging it
      sideways is miserable. `fit` makes the table narrow enough to sit inside
      the page instead: tighter cells, smaller secondary text, and long values
      (names, addresses, reasons) wrapping rather than widening a column. */
   .sdx .sdx-table.fit th,.sdx .sdx-table.fit td{padding:8px 8px}
   .sdx .sdx-table.fit td{font-size:12.5px}
   .sdx .sdx-table.fit th{font-size:10.5px;letter-spacing:.2px}
   .sdx .sdx-table.fit .pc{font-size:11px;line-height:1.45;overflow-wrap:anywhere}
   .sdx .sdx-table.fit td,.sdx .sdx-table.fit .pn{overflow-wrap:anywhere}
   .sdx .sdx-table.fit .sdx-chip{padding:3px 8px;font-size:10px}
   .sdx .sdx-table.fit .sdx-btn.sm{padding:5px 8px}
   .sdx .sdx-table.fit .sdx-qbtn{padding:4px 8px;font-size:11.5px}
   /* Qty columns: the number is what sets the width, not the word above it. */
   .sdx .sdx-table.fit .num{font-size:12px}

   /* Last resort only — with `fit` tables this should never be reached on a
      desktop; it keeps a phone usable. */
   .sdx .sdx-scroll{overflow-x:auto}
   .sdx .sdx-empty{text-align:center;padding:30px 20px;border:1.5px dashed var(--line);border-radius:var(--rs);color:var(--muted);font-size:13px}
   .sdx .sdx-empty i{display:block;font-size:26px;margin-bottom:8px;color:#b9c5bf}

   /* ── Key/value blocks on the detail screen ─────────────────── */
   .sdx .sdx-kv{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 26px}
   .sdx .sdx-kv.one{grid-template-columns:1fr}
   .sdx .sdx-kv .k{font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);margin-bottom:3px}
   .sdx .sdx-kv .v{font-size:13.5px;color:var(--ink);white-space:pre-line;word-break:break-word}
   .sdx .sdx-kv .v.num{font-family:var(--mono)}
   .sdx .sdx-kv .v.empty{color:#b3bdb8}

   /* ── Stat strip ────────────────────────────────────────────── */
   .sdx .sdx-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}
   .sdx .sdx-stat{background:#fff;border:1px solid var(--line);border-radius:var(--rs);padding:13px 16px}
   .sdx .sdx-stat .k{font-size:10.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted)}
   .sdx .sdx-stat .v{font-family:var(--mono);font-size:19px;font-weight:700;margin-top:4px;color:var(--ink)}
   .sdx .sdx-stat.hi .v{color:var(--gd)}
   .sdx .sdx-stat.warn .v{color:var(--amber)}

   /* ── Query thread ──────────────────────────────────────────── */
   .sdx .sdx-qbtn{display:inline-flex;align-items:center;gap:6px;border:1.4px solid var(--line);background:#fff;color:var(--ink2);border-radius:20px;padding:5px 11px;font-size:12px;font-weight:600;cursor:pointer;transition:background-color .15s,color .15s,border-color .15s}
   .sdx .sdx-qbtn:hover{border-color:var(--g);color:var(--gd);background:var(--gsoft2)}
   .sdx .sdx-qbtn .n{font-family:var(--mono);font-size:10.5px;background:#f1f4f2;border-radius:20px;padding:1px 7px}
   .sdx .sdx-qbtn.unread{background:var(--redS);border-color:#f3c9c6;color:var(--red)}
   .sdx .sdx-qbtn.unread .n{background:var(--red);color:#fff}

   .sdx .sq-thread{max-height:360px;overflow-y:auto;padding:4px 2px;display:flex;flex-direction:column;gap:12px}
   .sdx .sq-msg{max-width:78%;border-radius:12px;padding:10px 13px;font-size:13px;line-height:1.6;white-space:pre-line;word-break:break-word}
   .sdx .sq-msg .who{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;opacity:.75}
   .sdx .sq-msg .when{font-size:10.5px;margin-top:6px;opacity:.6}
   /* Employee on the left, admin on the right — the admin is "us" here. */
   .sdx .sq-msg.employee{align-self:flex-start;background:#f4f6f5;border:1px solid var(--line);color:var(--ink)}
   .sdx .sq-msg.admin{align-self:flex-end;background:var(--gsoft);border:1px solid #cfe6d8;color:var(--gd)}
   .sdx .sq-empty{text-align:center;padding:26px 16px;color:var(--muted);font-size:13px}

   /* ── Timeline ──────────────────────────────────────────────── */
   .sdx .sdx-tl{list-style:none;margin:0;padding:0 0 0 22px;border-left:2px solid var(--line)}
   .sdx .sdx-tl li{position:relative;padding:0 0 18px 14px}
   .sdx .sdx-tl li:last-child{padding-bottom:0}
   .sdx .sdx-tl li:before{content:'';position:absolute;left:-23px;top:4px;width:10px;height:10px;border-radius:50%;background:#fff;border:2.5px solid var(--g)}
   .sdx .sdx-tl li.mute:before{border-color:#c3cec8}
   .sdx .sdx-tl .a{font-size:13.5px;font-weight:700}
   .sdx .sdx-tl .m{font-size:11.5px;color:var(--muted);margin-top:2px}
   .sdx .sdx-tl .d{font-size:12.5px;color:var(--ink2);margin-top:6px;white-space:pre-line}
   .sdx .sdx-tl .chg{font-size:12.5px;margin-top:6px;background:#f7f9f8;border:1px solid var(--line2);border-radius:6px;padding:8px 10px}
   .sdx .sdx-tl .chg div+div{margin-top:4px}
   .sdx .sdx-tl .chg .was{color:var(--muted);text-decoration:line-through}
   .sdx .sdx-tl .chg .now{color:var(--gd);font-weight:600}

   /* ── Buttons ───────────────────────────────────────────────── */
   .sdx .sdx-btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:9px;font-size:13.5px;font-weight:600;padding:10px 18px;cursor:pointer;transition:background-color .15s,color .15s,border-color .15s;text-decoration:none;line-height:1.2}
   .sdx .sdx-btn.g{background:var(--g);color:#fff}.sdx .sdx-btn.g:hover{background:var(--gd);color:#fff}
   .sdx .sdx-btn.r{background:var(--red);color:#fff}.sdx .sdx-btn.r:hover{background:#b93636;color:#fff}
   .sdx .sdx-btn.b{background:var(--blue);color:#fff}.sdx .sdx-btn.b:hover{background:#184c7d;color:#fff}
   .sdx .sdx-btn.ghost{background:#fff;border:1.4px solid var(--line);color:var(--ink2)}.sdx .sdx-btn.ghost:hover{border-color:#c6d2cc;color:var(--ink)}
   .sdx .sdx-btn.sm{padding:7px 13px;font-size:12.5px}
   .sdx .sdx-btn.icon{padding:7px 10px;background:var(--redS);color:var(--red)}
   .sdx .sdx-btn[disabled]{opacity:.5;cursor:not-allowed}

   /* ── Sticky action bar, alerts ─────────────────────────────── */
   .sdx .sdx-actions{position:sticky;bottom:0;background:#fff;border:1px solid var(--line);border-top:2px solid var(--g);border-radius:var(--r);box-shadow:0 -4px 18px rgba(20,36,28,.06);padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:6px;z-index:20}
   .sdx .sdx-actions .note{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:7px}
   .sdx .sdx-alert{display:none;background:var(--redS);border:1px solid #f3c9c6;color:#a33131;border-radius:var(--rs);padding:12px 16px;margin-bottom:16px;font-size:13px}
   .sdx .sdx-alert ul{margin:6px 0 0;padding-left:18px}
   .sdx .sdx-note{background:var(--amberS);border:1px solid #f0e0bd;color:var(--amber);border-radius:var(--rs);padding:11px 15px;font-size:12.5px;margin-bottom:16px}
   .sdx .sdx-note.ok{background:var(--gsoft);border-color:#cfe6d8;color:var(--gd)}
   .sdx .sdx-hide{display:none!important}

   /* ── Per-request sub-panel on the dispatch screen ──────────── */
   .sdx .sdx-sub{background:#fbfcfb;border:1px solid var(--line2);border-radius:var(--rs);padding:13px 15px;margin-top:10px}
   .sdx .sdx-sub>.h{font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);margin-bottom:9px}
   /* Dispatch To sits on one row: send-to, party and address side by side.
      The address needs two lines, so the row is sized to it and the other two
      controls sit at the top rather than stretching to match. */
   /* The per-request dispatch line: qty and batch, then the whole dispatch-to
      stacked in the third column — send-to, the party, then its address. */
   .sdx .sdx-grid.sdx-row5{grid-template-columns:1fr 1fr 1.7fr;align-items:start}
   .sdx .sdx-grid.sdx-row5 .select2-container{margin-top:7px}
   .sdx .sdx-grid.sdx-row5 textarea.sdx-in.sm{min-height:62px;height:62px;white-space:pre-wrap;overflow-y:auto;overflow-x:hidden}
   @media(max-width:1100px){.sdx .sdx-grid.sdx-row5{grid-template-columns:repeat(2,1fr)}}
   @media(max-width:768px){.sdx .sdx-grid.sdx-row5{grid-template-columns:1fr}}
   .sdx .sdx-sub .sdx-grid.g3,.sdx .sdx-dispatch-to{align-items:start}
   .sdx .sdx-sub .sdx-grid.g3 textarea.sdx-in.sm,.sdx .sdx-dispatch-to textarea.sdx-in.sm{min-height:62px;height:62px;white-space:pre-wrap;overflow-y:auto;overflow-x:hidden}

   /* ── Modal ─────────────────────────────────────────────────── */
   .sdx-modal{position:fixed;inset:0;background:rgba(20,36,28,.45);z-index:10050;display:none;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto}
   .sdx-modal.on{display:flex}
   .sdx-modal .box{background:#fff;border-radius:12px;max-width:560px;width:100%;box-shadow:0 20px 60px rgba(20,36,28,.28);font-family:'Inter','Segoe UI',system-ui,sans-serif}
   .sdx-modal .box>.h{padding:17px 22px;border-bottom:1px solid #eef2f0;font-size:16px;font-weight:700;display:flex;justify-content:space-between;align-items:center;color:#14241c}
   .sdx-modal .box>.h .x{cursor:pointer;color:#7b8a82;font-size:20px;line-height:1}
   .sdx-modal .box>.b{padding:20px 22px}
   .sdx-modal .box>.f{padding:14px 22px;border-top:1px solid #eef2f0;display:flex;justify-content:flex-end;gap:9px}

   @media(max-width:1100px){.sdx .sdx-stats{grid-template-columns:repeat(2,1fr)}}
   @media(max-width:768px){
   .sdx .sdx-grid,.sdx .sdx-grid.g3,.sdx .sdx-grid.g4,.sdx .sdx-kv{grid-template-columns:1fr}
   .sdx .sdx-ch,.sdx .sdx-cb{padding-left:16px;padding-right:16px}
   }
</style>
