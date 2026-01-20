@extends('adminlte::page')

@section('title', 'Деталі позиції')

@section('content_header')
    <h1>Деталі позиції</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Інформація про позицію</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <strong><i class="fas fa-tag mr-1"></i> Назва</strong>
                    <p class="text-muted">{{ $position->name }}</p>

                    <hr>

                    <strong><i class="fas fa-file-alt mr-1"></i> Опис</strong>
                    <p class="text-muted">{{ $position->description ?: 'Опис відсутній' }}</p>

                    <hr>

                    <strong><i class="fas fa-info-circle mr-1"></i> Статус</strong>
                    <p class="text-muted">
                        <span class="badge badge-{{ $position->status_color }}">
                            {{ $position->status ? 'активна' : 'неактивна' }}
                        </span>
                    </p>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <a href="{{ route('positions.edit', $position->id) }}" class="btn btn-info">Редагувати</a>
                    <a href="{{ route('positions.index') }}" class="btn btn-default float-right">Назад до списку</a>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
