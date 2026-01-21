@extends('adminlte::page')

@section('title', 'Звіти по активним змінам')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Звіти по активним змінам</h1>
        <a href="{{ route('combat_shifts.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Назад до чергувань
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if($activeShifts->isEmpty())
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Наразі немає активних змін.</h5>
                </div>
            @else
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-broadcast-tower mr-1"></i>
                            Список активних змін та звітів
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Позиція</th>
                                    <th>Екіпаж</th>
                                    <th>Початок</th>
                                    <th class="text-center">Доступні звіти</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeShifts as $shift)
                                    <tr>
                                        <td>{{ $shift->id }}</td>
                                        <td>{{ $shift->position_name }}</td>
                                        <td>
                                            @foreach($shift->crew as $member)
                                                <span class="badge badge-info">{{ $member['callsign'] }}</span>
                                            @endforeach
                                        </td>
                                        <td>{{ $shift->started_at }}</td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('combat_shifts.report', $shift->id) }}" class="btn btn-primary btn-sm" title="Звіт по залишку">
                                                    <i class="fas fa-file-alt mr-1"></i> Залишки
                                                </a>
                                                <a href="{{ route('combat_shifts.flights_report', $shift->id) }}" class="btn btn-secondary btn-sm ml-1" title="Звіт по польотам">
                                                    <i class="fas fa-paper-plane mr-1"></i> Польоти
                                                </a>
                                                <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-default btn-sm ml-1" title="Деталі">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
