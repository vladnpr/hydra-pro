@extends('adminlte::page')

@section('title', 'Деталі дрона')

@section('content_header')
    <h1>Деталі дрона</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Інформація про дрон</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-helicopter mr-1"></i> Назва</strong>
                    <p class="text-muted">{{ $drone->name }}</p>

                    <hr>

                    <strong><i class="fas fa-barcode mr-1"></i> Серійний номер</strong>
                    <p class="text-muted">{{ $drone->serial_number ?: 'Відсутній' }}</p>

                    <hr>

                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Позиція</strong>
                    <p class="text-muted">{{ $drone->position->name }}</p>

                    <hr>

                    <strong><i class="fas fa-info-circle mr-1"></i> Статус</strong>
                    <p class="text-muted">
                        <span class="badge badge-{{ $drone->status_color }}">
                            @if($drone->status === 'active') Активний
                            @elseif($drone->status === 'lost') Втрачений
                            @elseif($drone->status === 'repair') В ремонті
                            @else {{ $drone->status }} @endif
                        </span>
                    </p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('recon.drones.edit', $drone->id) }}" class="btn btn-info">Редагувати</a>
                    <a href="{{ route('recon.drones.index') }}" class="btn btn-default float-right">Назад до списку</a>
                </div>
            </div>
        </div>
    </div>
@endsection
