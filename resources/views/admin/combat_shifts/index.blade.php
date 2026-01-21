@extends('adminlte::page')

@section('title', 'Бойові чергування')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Бойові чергування</h1>
        @if(!$activeShift)
            <a href="{{ route('combat_shifts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Розпочати нове чергування
            </a>
        @else
            <span class="badge badge-info">У вас вже є активне чергування</span>
        @endif
    </div>
@endsection

@section('content')
    @if($activeShift)
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1"></i>
                            Ваша активна зміна
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('flight_operations.index') }}" class="btn btn-tool">
                                <i class="fas fa-paper-plane mr-1"></i> До вильотів
                            </a>
                            <a href="{{ route('combat_shifts.show', $activeShift->id) }}" class="btn btn-tool">
                                <i class="fas fa-eye mr-1"></i> Деталі
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Позиція:</strong> {{ $activeShift->position_name }}
                            </div>
                            <div class="col-md-4">
                                <strong>Початок:</strong> {{ $activeShift->started_at }}
                            </div>
                            <div class="col-md-4 text-right">
                                <form action="{{ route('combat_shifts.finish', $activeShift->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Завершити чергування?')">
                                        <i class="fas fa-stop-circle mr-1"></i> Завершити
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Позиція</th>
                                <th class="d-none d-lg-table-cell">Екіпаж</th>
                                <th>Статус</th>
                                <th class="d-none d-md-table-cell">Початок</th>
                                <th class="d-none d-lg-table-cell">Завершення</th>
                                <th>Звіти</th>
                                <th style="width: 150px">Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                                <tr>
                                    <td>{{ $shift->id }}</td>
                                    <td>{{ $shift->position_name }}</td>
                                    <td class="d-none d-lg-table-cell">
                                        @foreach($shift->crew as $member)
                                            <span class="badge badge-info">{{ $member['callsign'] }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $shift->status_color }}">
                                            {{ $shift->status_label }}
                                        </span>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $shift->started_at }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $shift->ended_at ?? '-' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('combat_shifts.report', $shift->id) }}" class="btn btn-secondary btn-sm" title="Звіт">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                            <a href="{{ route('combat_shifts.flights_report', $shift->id) }}" class="btn btn-default btn-sm" title="Звіт по польотам">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $userIds = collect($shift->users)->pluck('id')->toArray();
                                            $isUserInShift = in_array(auth()->id(), $userIds);
                                        @endphp

                                        <div class="btn-group">
                                            <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-primary btn-sm" title="Перегляд">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($shift->status === 'opened' && !$isUserInShift && !$activeShift)
                                                <form action="{{ route('combat_shifts.join', $shift->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Приєднатися">
                                                        <i class="fas fa-sign-in-alt"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($shift->status === 'opened' && $isUserInShift)
                                                <form action="{{ route('combat_shifts.leave', $shift->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm" title="Відключитися" onclick="return confirm('Ви впевнені, що хочете покинути це чергування?')">
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('combat_shifts.finish', $shift->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Завершити" onclick="return confirm('Ви впевнені, що хочете завершити це чергування?')">
                                                        <i class="fas fa-stop-circle"></i>
                                                    </button>
                                                </form>
                                            @endif

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
                                    <td colspan="8" class="text-center">Чергувань не знайдено.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
