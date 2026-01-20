@extends('adminlte::page')

@section('title', 'Бойові чергування')

@php
    $hasActiveShift = false;
    foreach($shifts as $shift) {
        if ($shift->status === 'opened') {
            $userIds = collect($shift->users)->pluck('id')->toArray();
            if (in_array(auth()->id(), $userIds)) {
                $hasActiveShift = true;
                break;
            }
        }
    }
@endphp

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Бойові чергування</h1>
        @if(!$hasActiveShift)
            <a href="{{ route('combat_shifts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Розпочати нове чергування
            </a>
        @else
            <span class="badge badge-info">У вас вже є активне чергування</span>
        @endif
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Успіх!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Помилка!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список чергувань</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Позиція</th>
                                <th>Екіпаж (Система / Позивні)</th>
                                <th>Статус</th>
                                <th>Початок</th>
                                <th>Завершення</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->id }}</td>
                                    <td>{{ $shift->position_name }}</td>
                                    <td>
                                        @foreach($shift->users as $user)
                                            <span class="badge badge-primary">{{ $user['name'] }}</span>
                                        @endforeach
                                        <hr class="my-1">
                                        @foreach($shift->crew as $member)
                                            <span class="badge badge-info">{{ $member['callsign'] }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $shift->status_color }}">
                                            {{ $shift->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $shift->started_at }}</td>
                                    <td>{{ $shift->ended_at ?? '-' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-primary btn-sm" title="Перегляд">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @php
                                                $userIds = collect($shift->users)->pluck('id')->toArray();
                                                $isUserInShift = in_array(auth()->id(), $userIds);
                                            @endphp

                                            @if($shift->status === 'opened')
                                                @if($isUserInShift)
                                                    <form action="{{ route('combat_shifts.leave', $shift->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning btn-sm" title="Покинути" onclick="return confirm('Ви впевнені, що хочете покинути це чергування?')">
                                                            <i class="fas fa-sign-out-alt"></i>
                                                        </button>
                                                    </form>
                                                @elseif(!$hasActiveShift)
                                                    <form action="{{ route('combat_shifts.join', $shift->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" title="Приєднатися">
                                                            <i class="fas fa-sign-in-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            <a href="{{ route('combat_shifts.report', $shift->id) }}" class="btn btn-secondary btn-sm" title="Звіт">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                            <a href="{{ route('combat_shifts.flights_report', $shift->id) }}" class="btn btn-default btn-sm" title="Звіт по польотам">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                            <a href="{{ route('combat_shifts.edit', $shift->id) }}" class="btn btn-info btn-sm" title="Редагувати">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('combat_shifts.destroy', $shift->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Ви впевнені?')" title="Видалити">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Чергувань не знайдено.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
