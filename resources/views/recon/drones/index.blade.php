@extends('adminlte::page')

@section('title', 'Розвідувальні Дрони')

@section('content_header')
    <h1>Розвідувальні Дрони</h1>
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
                    <h3 class="card-title">Список дронів</h3>
                    <div class="card-tools">
                        <a href="{{ route('recon.drones.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Додати новий дрон
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Назва</th>
                                <th>Серійний номер</th>
                                <th>Зміна</th>
                                <th>Позиція</th>
                                <th>Статус</th>
                                <th style="width: 150px">Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($drones as $drone)
                                <tr>
                                    <td>{{ $drone->id }}</td>
                                    <td>{{ $drone->name }}</td>
                                    <td>{{ $drone->serial_number ?: '-' }}</td>
                                    <td>
                                        @if($drone->shift_type?->value === 'day')
                                            <i class="fas fa-sun text-warning" title="Денний"></i>
                                        @elseif($drone->shift_type?->value === 'night')
                                            <i class="fas fa-moon text-secondary" title="Нічний"></i>
                                        @elseif($drone->shift_type?->value === 'both')
                                            <i class="fas fa-sun text-warning"></i> / <i class="fas fa-moon text-secondary"></i>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $drone->position->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $drone->status_color }}">
                                            @if($drone->status === 'active') Активний
                                            @elseif($drone->status === 'lost') Втрачений
                                            @elseif($drone->status === 'repair') В ремонті
                                            @elseif($drone->status === 'non_operational') Не боєготовий
                                            @else {{ $drone->status }} @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('recon.drones.show', $drone->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('recon.drones.edit', $drone->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('recon.drones.destroy', $drone->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Ви впевнені?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Дронів не знайдено.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
