@extends('adminlte::page')

@section('title', 'Бойові чергування')

@section('content_header')
    <h1>Бойові чергування</h1>
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
                    <div class="card-tools">
                        <a href="{{ route('combat_shifts.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Розпочати нове чергування
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Позиція</th>
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
                                        <span class="badge badge-{{ $shift->status_color }}">
                                            {{ $shift->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $shift->started_at }}</td>
                                    <td>{{ $shift->ended_at ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('combat_shifts.edit', $shift->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('combat_shifts.destroy', $shift->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Ви впевнені?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Чергувань не знайдено.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
