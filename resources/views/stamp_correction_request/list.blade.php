@extends('layouts.app')

@section('content')
<div class="attendance-list-page">

    <div class="attendance-container">
        <h1 class="page-title">申請一覧</h1>

        {{-- タブ --}}
        <div class="tab-menu">
            <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}"
               class="{{ $status === 'pending' ? 'active' : '' }}">
                承認待ち
            </a>

            <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}"
               class="{{ $status === 'approved' ? 'active' : '' }}">
                承認済み
            </a>
        </div>

        <div class="table-card">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td>
    {{ $request->status === 'pending' ? '承認待ち' : ($request->status === 'approved' ? '承認済み' : '却下') }}
</td>
                            <td>{{ $request->user?->name ?? '-' }}</td>

                            <td>
                                @if($request->attendance?->work_date)
                                    {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ $request->requested_note ?? '' }}</td>
                            <td>{{ optional($request->created_at)->format('Y/m/d') }}</td>

                            <td>
    <a href="{{ route('stamp_correction_request.show', $request->id) }}">詳細</a>
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 20px;">
                                該当データがありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection