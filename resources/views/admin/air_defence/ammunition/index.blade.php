@extends('adminlte::page')

@section('title', 'Боєприпаси ППО')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Боєприпаси ППО</h1>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('air-defence.ammunition.create') }}" class="btn btn-primary float-right">
                <i class="fas fa-plus"></i> Додати БК
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
                        <th>Тип</th>
                        <th>Статус</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ammunition as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->type }}</td>
                            <td>
                                <span class="badge badge-{{ $item->status_color }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('air-defence.ammunition.edit', $item->id) }}" class="btn btn-sm btn-default">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('air-defence.ammunition.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Ви впевнені?')">
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
                            <td colspan="5" class="text-center">Боєприпасів не знайдено</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
