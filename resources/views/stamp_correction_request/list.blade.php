@extends('layouts.app')

@section('content')
<div class="request-page">
    <div class="request-container">

        <h2 class="request-title">申請一覧</h2>

        <div class="request-tabs">
            <span class="tab active">承認待ち</span>
            <span class="tab">承認済み</span>
        </div>

        <div class="request-table-wrapper">
            @if ($requests->isEmpty())
                <p class="no-data">申請はありません。</p>
            @else
                <table class="request-table">
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
                        @foreach ($requests as $req)
                            <tr>
                                <td>
    @if ($req->status === 'pending')
        申請待ち
    @else
        {{ $req->status }}
    @endif
</td>
                                <td>{{ $req->user->name ?? '-' }}</td>
                                <td>{{ $req->requested_work_date ?? '-' }}</td>
                                <td>{{ $req->requested_note }}</td>
                                <td>{{ $req->created_at->format('Y/m/d') }}</td>
                                <td>
                                    <a href="{{ route('attendance.detail', $req->attendance_id) }}" class="detail-link">
                                        詳細
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>
</div>
@endsection