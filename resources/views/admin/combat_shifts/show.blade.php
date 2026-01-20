@extends('adminlte::page')

@section('title', 'Деталі чергування')

@section('content_header')
    <h1>Чергування #{{ $shift->id }}</h1>
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
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Основна інформація</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%">Позиція</th>
                            <td>{{ $shift->position_name }}</td>
                        </tr>
                        <tr>
                            <th>Статус</th>
                            <td>
                                <span class="badge badge-{{ $shift->status_color }}">
                                    {{ $shift->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Час початку</th>
                            <td>{{ $shift->started_at }}</td>
                        </tr>
                        <tr>
                            <th>Час завершення</th>
                            <td>{{ $shift->ended_at ?? 'В процесі' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('combat_shifts.index') }}" class="btn btn-default">Назад до списку</a>
                    <a href="{{ route('combat_shifts.edit', $shift->id) }}" class="btn btn-info float-right">Редагувати</a>
                </div>
            </div>

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Екіпаж</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Позивний</th>
                                <th>Посада</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shift->crew as $member)
                                <tr>
                                    <td>{{ $member['callsign'] }}</td>
                                    <td>{{ $member['role'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">Екіпаж не вказано</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Вильоти</h3>
                </div>
                <div class="card-body p-0">
                    @php
                        $today = date('Y-m-d');
                    @endphp
                    @forelse($shift->flights as $date => $flights)
                        <div class="card mb-0 shadow-none border-bottom">
                            <div class="card-header p-2">
                                <h3 class="card-title small">
                                    <strong>{{ date('d.m.Y', strtotime($date)) }}</strong>
                                    @if($date == $today)
                                        <span class="badge badge-primary ml-2">Сьогодні</span>
                                    @endif
                                    <span class="ml-2 text-muted">({{ count($flights) }})</span>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas {{ $date == $today ? 'fa-minus' : 'fa-plus' }}"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0" style="{{ $date == $today ? '' : 'display: none;' }}">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pl-3">Час</th>
                                                <th>Дрон</th>
                                                <th>БК</th>
                                                <th>Координати</th>
                                                <th>Результат</th>
                                                <th>Примітка</th>
                                                <th>Дії</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($flights as $flight)
                                                <tr>
                                                    <td class="pl-3 text-nowrap">{{ date('H:i', strtotime($flight['flight_time'])) }}</td>
                                                    <td>{{ $flight['drone_name'] }}</td>
                                                    <td>{{ $flight['ammunition_name'] }}</td>
                                                    <td>{{ $flight['coordinates'] }}</td>
                                                    <td>
                                                        @php
                                                            $badgeClass = match($flight['result']) {
                                                                'влучання' => 'success',
                                                                'удар в районі цілі' => 'warning',
                                                                'недольот' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge badge-{{ $badgeClass }}">{{ $flight['result'] }}</span>
                                                    </td>
                                                    <td class="small">{{ $flight['note'] }}</td>
                                                    <td>
                                                        <a href="{{ route('flights.edit', $flight['id']) }}" class="btn btn-xs btn-info">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('flights.destroy', $flight['id']) }}" method="POST" style="display:inline-block;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Ви впевнені?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-center">
                            <span class="text-muted">Вильотів не зафіксовано</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Ресурси на чергуванні</h3>
                </div>
                <div class="card-body">
                    <h5>Дрони (залишок)</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Назва</th>
                                <th>Кількість</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shift->drones as $drone)
                                <tr>
                                    <td>{{ $drone['name'] }}</td>
                                    <td>{{ $drone['quantity'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">Дрони не вказані</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <h5 class="mt-4">Боєприпаси (залишок)</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Назва</th>
                                <th>Кількість</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shift->ammunition as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">Боєприпаси не вказані</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
