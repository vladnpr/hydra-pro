@extends('adminlte::page')

@section('title', 'Дрони ППО')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Дрони ППО</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('air-defence.drones.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Додати дрон
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Назва</th>
                        <th>Модель</th>
                        <th>Статус</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drones as $drone)
                        <tr>
                            <td>{{ $drone->id }}</td>
                            <td>{{ $drone->name }}</td>
                            <td>{{ $drone->model }}</td>
                            <td>
                                <span class="badge badge-{{ $drone->status_color }}">
                                    {{ $drone->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('air-defence.drones.edit', $drone->id) }}" class="btn btn-sm btn-default">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('air-defence.drones.destroy', $drone->id) }}" method="POST" onsubmit="return confirm('Ви впевнені?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Дронів не знайдено</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
