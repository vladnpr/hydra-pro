@extends('adminlte::page')

@section('title', 'Дашборд')

@section('content_header')
    <h1>Статистика бойової роботи</h1>
@endsection

@section('content')
    @if($activeShift)
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1"></i>
                            Ваша активна зміна
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('flight_operations.index') }}" class="btn btn-tool">
                                <i class="fas fa-paper-plane mr-1"></i> До вильотів
                            </a>
                            <a href="{{ route('combat_shifts.show', $activeShift->id) }}" class="btn btn-tool">
                                <i class="fas fa-eye mr-1"></i> Деталі
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Позиція:</strong> {{ $activeShift->position_name }}
                            </div>
                            <div class="col-md-4">
                                <strong>Початок:</strong> {{ $activeShift->started_at }}
                            </div>
                            <div class="col-md-4 text-right">
                                <form action="{{ route('combat_shifts.finish', $activeShift->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Завершити чергування?')">
                                        <i class="fas fa-stop-circle mr-1"></i> Завершити
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if(!empty($activeShift->crew))
                            <div class="mt-2">
                                <strong>Екіпаж:</strong>
                                @foreach($activeShift->crew as $member)
                                    <span class="badge badge-info">{{ $member['callsign'] }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_flights'] }}</h3>
                    <p>Всього вильотів</p>
                </div>
                <div class="icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <a href="{{ route('combat_shifts.index') }}" class="small-box-footer">Детальніше <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['total_hits'] }}</h3>
                    <p>Влучання</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="small-box-footer" style="height: 30px;"></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_area_hits'] }}</h3>
                    <p>В районі цілі</p>
                </div>
                <div class="icon">
                    <i class="fas fa-crosshairs"></i>
                </div>
                <div class="small-box-footer" style="height: 30px;"></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_misses'] }}</h3>
                    <p>Недольоти</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="small-box-footer" style="height: 30px;"></div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Статистика детонацій</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-success"><i class="fas fa-bomb"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Детонація відбулася</span>
                            <span class="info-box-number text-success">{{ $stats['total_detonations'] }}</span>
                        </div>
                    </div>
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-danger"><i class="fas fa-skull-crossbones"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Не розірвався</span>
                            <span class="info-box-number text-danger">{{ $stats['total_non_detonations'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Ефективність</h3>
                </div>
                <div class="card-body">
                    @php
                        $hitRate = $stats['total_flights'] > 0 ? round(($stats['total_hits'] / $stats['total_flights']) * 100, 1) : 0;
                        $detonationRate = $stats['total_flights'] > 0 ? round(($stats['total_detonations'] / $stats['total_flights']) * 100, 1) : 0;
                    @endphp

                    <div class="progress-group">
                        Відсоток влучань
                        <span class="float-right"><b>{{ $stats['total_hits'] }}</b>/{{ $stats['total_flights'] }}</span>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: {{ $hitRate }}%"></div>
                        </div>
                        <small>{{ $hitRate }}% від загальної кількості</small>
                    </div>

                    <div class="progress-group mt-4">
                        Надійність БК (детонація)
                        <span class="float-right"><b>{{ $stats['total_detonations'] }}</b>/{{ $stats['total_flights'] }}</span>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: {{ $detonationRate }}%"></div>
                        </div>
                        <small>{{ $detonationRate }}% від загальної кількості</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
