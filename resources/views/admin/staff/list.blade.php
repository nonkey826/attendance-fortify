@extends('layouts.app')

@section('content')
<div class="attendance-list-page">
    <div class="attendance-container">

        <h1 class="page-title">スタッフ一覧</h1>

        <div class="table-card">
            <table class="attendance-table">

                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.staff.monthly', ['user' => $user->id]) }}">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    </div>
</div>
@endsection