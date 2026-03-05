@extends('layouts.app')

@section('content')
<div class="attendance-list-page">
  <div class="attendance-container">

    <h1 class="page-title">申請詳細</h1>

    <div class="table-card" style="padding: 18px;">
      <p><strong>状態：</strong>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</p>
      <p><strong>申請者：</strong>{{ $request->user?->name ?? '-' }}</p>
      <p><strong>申請日時：</strong>{{ optional($request->created_at)->format('Y/m/d H:i') }}</p>
      <p><strong>対象日：</strong>
        @if($request->attendance?->work_date)
          {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
        @else
          -
        @endif
      </p>
    </div>

    <div class="table-card" style="margin-top: 18px;">
      <table class="attendance-table">
        <thead>
          <tr>
            <th></th>
            <th>出勤</th>
            <th>退勤</th>
            <th>備考</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>実績</strong></td>
            <td>
              {{ $request->attendance?->clock_in_time ? \Carbon\Carbon::parse($request->attendance->clock_in_time)->format('H:i') : '-' }}
            </td>
            <td>
              {{ $request->attendance?->clock_out_time ? \Carbon\Carbon::parse($request->attendance->clock_out_time)->format('H:i') : '-' }}
            </td>
            <td>-</td>
          </tr>

          <tr>
            <td><strong>申請</strong></td>
            <td>
              {{ $request->requested_clock_in_time ? \Carbon\Carbon::parse($request->requested_clock_in_time)->format('H:i') : '-' }}
            </td>
            <td>
              {{ $request->requested_clock_out_time ? \Carbon\Carbon::parse($request->requested_clock_out_time)->format('H:i') : '-' }}
            </td>
            <td>{{ $request->requested_note ?? '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- 承認ボタン（承認待ちの時だけ） --}}
    @if($request->status === 'pending')
      <div style="margin-top: 24px; display:flex; justify-content:flex-end;">
        <form method="POST" action="{{ route('stamp_correction_request.approve', ['attendance_correct_request_id' => $request->id]) }}">
          @csrf
          <button type="submit" class="btn-black">承認</button>
        </form>
      </div>
    @endif

    <div style="margin-top: 18px;">
      <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}">← 一覧へ戻る</a>
    </div>

  </div>
</div>
@endsection