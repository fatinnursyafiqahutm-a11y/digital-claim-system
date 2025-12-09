<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Claims Summary' }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #0f172a; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        .meta { color: #475569; font-size: 12px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; color: #334155; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title ?? 'Claims Summary' }}</h1>
    <div class="meta">Period: {{ $period ?? '-' }} · Generated: {{ now()->format('Y-m-d H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                @if(isset($claims[0]) && $claims[0]->relationLoaded('user'))
                    <th>Employee</th>
                @endif
                <th>Title</th>
                <th>Category</th>
                <th class="right">Amount</th>
                <th>Status</th>
                <th>Claim Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($claims as $claim)
                <tr>
                    <td>{{ $claim->id }}</td>
                    @if($claim->relationLoaded('user'))
                        <td>{{ $claim->user->name ?? 'N/A' }}</td>
                    @endif
                    <td>{{ $claim->title }}</td>
                    <td>{{ $claim->category->display_name ?? $claim->category->name ?? 'N/A' }}</td>
                    <td class="right">RM {{ number_format($claim->amount, 2) }}</td>
                    <td>{{ ucfirst($claim->status) }}</td>
                    <td>{{ optional($claim->claim_date)->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No claims found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

