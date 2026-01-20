@extends('adminlte::page')

@section('title', 'Список дронів')

@section('content_header')
    <h1>Список дронів</h1>
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
                    <h3 class="card-title">Усі дрони</h3>
                    <div class="card-tools">
                        <a href="{{ route('drones.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Додати новий дрон
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
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
                            @forelse($drones ?? [] as $drone)
                                <tr>
                                    <td>{{ $drone->id }}</td>
                                    <td>{{ $drone->name }}</td>
                                    <td>{{ $drone->model }}</td>
                                    <td>
                                        <span class="badge badge-{{ $drone->status_color }}">
                                            {{ $drone->status == 1 ? 'активний' : 'неактивний'}}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('drones.show', $drone->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('drones.edit', $drone->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('drones.destroy', $drone->id) }}" method="POST" style="display:inline-block;">
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
                                    <td colspan="5" class="text-center">Дронів не знайдено.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
