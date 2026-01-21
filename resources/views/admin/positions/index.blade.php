@extends('adminlte::page')

@section('title', 'Список позицій')

@section('content_header')
    <h1>Список позицій</h1>
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
                    <h3 class="card-title">Усі позиції</h3>
                    <div class="card-tools">
                        <a href="{{ route('positions.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Додати нову позицію
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Назва</th>
                                <th class="d-none d-lg-table-cell">Опис</th>
                                <th>Статус</th>
                                <th style="width: 150px">Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($positions ?? [] as $position)
                                <tr>
                                    <td>{{ $position->id }}</td>
                                    <td>{{ $position->name }}</td>
                                    <td class="d-none d-lg-table-cell">{{ Str::limit($position->description, 50) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $position->status_color }}">
                                            {{ $position->status ? 'акт.' : 'неакт.' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('positions.show', $position->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('positions.edit', $position->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('positions.destroy', $position->id) }}" method="POST" style="display:inline-block;">
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
                                    <td colspan="5" class="text-center">Позицій не знайдено.</td>
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
