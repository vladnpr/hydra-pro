@extends('adminlte::page')

@section('title', 'Деталі дрона')

@section('content_header')
    <h1>Деталі дрона: {{ $drone->name }}</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Інформація про дрон</h3>
                    <div class="card-tools">
                        <a href="{{ route('drones.edit', $drone->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-edit"></i> Редагувати
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <tr>
                            <th style="width: 30%">ID</th>
                            <td>{{ $drone->id }}</td>
                        </tr>
                        <tr>
                            <th>Назва</th>
                            <td>{{ $drone->name }}</td>
                        </tr>
                        <tr>
                            <th>Модель</th>
                            <td>{{ $drone->model }}</td>
                        </tr>
                        <tr>
                            <th>Статус</th>
                            <td>
                                <span class="badge badge-{{ $drone->status_color }}">
                                    {{ $drone->status == 1 ? 'активний' : 'неактивний' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <a href="{{ route('drones.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Назад до списку
                    </a>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
