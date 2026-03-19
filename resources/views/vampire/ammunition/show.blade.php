@extends('adminlte::page')

@section('title', 'Деталі боєприпасу')

@section('content_header')
    <h1>Деталі боєприпасу</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Інформація</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-tag mr-1"></i> Назва</strong>
                    <p class="text-muted">{{ $ammunition->name }}</p>

                    <hr>

                    <strong><i class="fas fa-info-circle mr-1"></i> Статус</strong>
                    <p class="text-muted">
                        <span class="badge badge-{{ $ammunition->status_color }}">
                            {{ $ammunition->status ? 'Активний' : 'Неактивний' }}
                        </span>
                    </p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('vampire.ammunition.edit', ['ammunition' => $ammunition->id]) }}" class="btn btn-info">Редагувати</a>
                    <a href="{{ route('vampire.ammunition.index') }}" class="btn btn-default float-right">Назад до списку</a>
                </div>
            </div>
        </div>
    </div>
@endsection
