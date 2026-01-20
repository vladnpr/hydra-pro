@extends('adminlte::page')

@section('title', 'Бойові чергування')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Бойові чергування</h1>
        <a href="{{ route('combat_shifts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Розпочати нове чергування
        </a>
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
                                <th>Екіпаж</th>
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
                                        <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-primary btn-sm" title="Перегляд">
                                            <i class="fas fa-eye"></i>
                                        </a>
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
