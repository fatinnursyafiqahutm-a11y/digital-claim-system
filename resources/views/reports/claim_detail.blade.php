<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Report #{{ $claim->id }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; margin: 0; padding: 24px; background: #f8fafc; color: #0f172a; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 16px; box-shadow: 0 10px 30px rgba(15,23,42,0.05); }
        h1 { margin: 0 0 4px; font-size: 20px; }
        h2 { margin: 16px 0 8px; font-size: 16px; color: #0f172a; }
        p { margin: 0; }
        .meta { color: #475569; font-size: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .row:last-child { border-bottom: none; }
        .label { color: #475569; }
        .value { font-weight: 600; color: #0f172a; text-align: right; }
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .status-draft { background: #f1f5f9; color: #334155; }
        .status-submitted { background: #fef9c3; color: #854d0e; }
        .status-under_review { background: #e0f2fe; color: #075985; }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-paid { background: #e0f2fe; color: #075985; }
        .status-returned_for_info { background: #ffedd5; color: #9a3412; }
        .print-btn { display: inline-flex; align-items: center; padding: 10px 14px; background: #2563eb; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; margin-right: 12px; }
        .print-btn:hover { background: #1d4ed8; }
        .section-title { font-weight: 700; font-size: 14px; margin-bottom: 8px; color: #0f172a; }
        .list { padding-left: 16px; margin: 6px 0; color: #334155; font-size: 13px; }
        .small { font-size: 12px; color: #475569; }
        .footer { margin-top: 12px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #475569; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; }
        .table th, .table td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        .table th { background: #f8fafc; color: #334155; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>
    <div class="card no-print" style="display:flex; justify-content: space-between; align-items:center;">
        <div>
            <h1>Claim Report</h1>
            <p class="meta">Claim ID: #{{ $claim->id }} · Viewer: {{ ucfirst($viewer) }}</p>
        </div>
        <div>
            <button class="print-btn" onclick="window.print()">🖨️ Print</button>
        </div>
    </div>

    @php
        $categoryData = is_array($claim->category_data)
            ? $claim->category_data
            : (is_string($claim->category_data) ? json_decode($claim->category_data, true) : []);
    @endphp

    <div class="card">
        <div style="display:flex; justify-content: space-between; align-items: baseline;">
            <div>
                <h1>{{ $claim->title }}</h1>
                <p class="meta">Created: {{ optional($claim->created_at)->format('Y-m-d H:i') }} · Claim Date: {{ optional($claim->claim_date)->format('Y-m-d') }}</p>
            </div>
            <span class="badge status-{{ str_replace(' ', '_', $claim->status) }}">
                {{ $claim->formatted_status['text'] ?? ucfirst($claim->status) }}
            </span>
        </div>

        <div class="grid" style="margin-top:16px;">
            <div>
                <h2>Basic Information</h2>
                <div class="row"><span class="label">Category</span><span class="value">{{ $claim->category->display_name ?? $claim->category->name ?? 'N/A' }}</span></div>
                <div class="row"><span class="label">Amount</span><span class="value">RM {{ number_format($claim->amount, 2) }}</span></div>
                @if($claim->approved_amount)
                    <div class="row"><span class="label">Approved Amount</span><span class="value text-green-600">RM {{ number_format($claim->approved_amount, 2) }}</span></div>
                @endif
                <div class="row"><span class="label">Priority</span><span class="value">{{ ucfirst($claim->priority ?? 'normal') }}</span></div>
                @if(!empty($claim->payment_reference))
                    <div class="row"><span class="label">Payment Ref</span><span class="value">{{ $claim->payment_reference }}</span></div>
                @endif
            </div>
            <div>
                <h2>People</h2>
                <div class="row"><span class="label">Submitted By</span><span class="value">{{ $claim->user->name ?? 'N/A' }}</span></div>
                <div class="row"><span class="label">Employee ID</span><span class="value">{{ $claim->user->employee_id ?? 'N/A' }}</span></div>
                <div class="row"><span class="label">Department</span><span class="value">{{ $claim->user->department ?? 'N/A' }}</span></div>
            </div>
            <div>
                <h2>Timeline</h2>
                <div class="row"><span class="label">Submitted</span><span class="value">{{ optional($claim->submitted_at)->format('Y-m-d H:i') ?? '-' }}</span></div>
                <div class="row"><span class="label">Reviewed</span><span class="value">{{ optional($claim->reviewed_at)->format('Y-m-d H:i') ?? '-' }}</span></div>
                <div class="row"><span class="label">Approved</span><span class="value">{{ optional($claim->approved_at)->format('Y-m-d H:i') ?? '-' }}</span></div>
                <div class="row"><span class="label">Rejected</span><span class="value">{{ optional($claim->rejected_at)->format('Y-m-d H:i') ?? '-' }}</span></div>
                <div class="row"><span class="label">Paid</span><span class="value">{{ optional($claim->payment_date)->format('Y-m-d') ?? '-' }}</span></div>
            </div>
        </div>

        <h2>Description</h2>
        <p style="margin-top:6px; line-height:1.6;">{{ $claim->description }}</p>

        @if($claim->rejection_reason)
            <h2>Rejection Reason</h2>
            <p style="margin-top:6px; color:#b91c1c;">{{ $claim->rejection_reason }}</p>
        @endif

        @if($claim->admin_notes)
            <h2>Admin Notes</h2>
            <p style="margin-top:6px;">{{ $claim->admin_notes }}</p>
        @endif

        @if(!empty($categoryData))
            <h2>Category Data</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categoryData as $key => $value)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($claim->receipts && $claim->receipts->count())
            <h2>Receipts</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Type</th>
                        <th>Size (KB)</th>
                        <th>Uploaded At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($claim->receipts as $receipt)
                        <tr>
                            <td>{{ $receipt->original_filename }}</td>
                            <td>{{ $receipt->mime_type }}</td>
                            <td>{{ number_format($receipt->file_size / 1024, 1) }}</td>
                            <td>{{ optional($receipt->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($claim->approvals && $claim->approvals->count())
            <h2>Approval History</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>By</th>
                        <th>Comments</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($claim->approvals as $approval)
                        <tr>
                            <td>{{ ucfirst(str_replace('_',' ', $approval->action)) }}</td>
                            <td>{{ $approval->approver->name ?? 'N/A' }}</td>
                            <td>{{ $approval->comments ?? '-' }}</td>
                            <td>{{ optional($approval->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($claim->auditLogs && $claim->auditLogs->count())
            <h2>Audit Trail</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($claim->auditLogs as $log)
                        <tr>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            <span>Generated on {{ now()->format('Y-m-d H:i') }}</span>
            <span>Digital Claim System</span>
        </div>
    </div>
</body>
</html>

