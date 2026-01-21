@extends('adminlte::page')

@section('title', 'Деталі чергування')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Чергування #{{ $shift->id }}</h1>
        <div>
            <a href="{{ route('combat_shifts.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до списку
            </a>
            <a href="{{ route('combat_shifts.report', $shift->id) }}" class="btn btn-primary ml-2">
                <i class="fas fa-file-alt"></i> Звіт по залишку
            </a>
            <a href="{{ route('combat_shifts.flights_report', $shift->id) }}" class="btn btn-secondary ml-2">
                <i class="fas fa-paper-plane"></i> Звіт по польотам
            </a>
            <a href="{{ route('combat_shifts.edit', $shift->id) }}" class="btn btn-info ml-2">
                <i class="fas fa-edit"></i> Редагувати
            </a>
            @if($shift->status === 'opened')
                <form action="{{ route('combat_shifts.finish', $shift->id) }}" method="POST" style="display:inline-block;" class="ml-2">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Ви впевнені, що хочете завершити чергування?')">
                        <i class="fas fa-stop-circle"></i> Завершити чергування
                    </button>
                </form>
            @else
                <form action="{{ route('combat_shifts.reopen', $shift->id) }}" method="POST" style="display:inline-block;" class="ml-2">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Ви впевнені, що хочете відновити чергування?')">
                        <i class="fas fa-undo"></i> Відновити чергування
                    </button>
                </form>
            @endif
        </div>
    </div>
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
                <div class="card-body table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <tr>
                            <th style="width: 30%">Позиція</th>
                            <td>{{ $shift->position_name }}</td>
                        </tr>
                        <tr>
                            <th>Екіпаж системи</th>
                            <td class="text-wrap">
                                @foreach($shift->users as $user)
                                    <span class="badge badge-primary">{{ $user['name'] }}</span>
                                @endforeach
                            </td>
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
                    <span class="text-muted small">ID чергування: {{ $shift->id }}</span>
                </div>
            </div>

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Екіпаж</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm text-nowrap">
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
                        $today = now()->format('Y-m-d');
                    @endphp
                    @forelse($shift->flights as $date => $flights)
                        <div class="card mb-0 shadow-none border-bottom">
                            <div class="card-header p-2">
                                <h3 class="card-title small">
                                    <strong>{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</strong>
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
                                                <th class="pl-3 text-nowrap">Час</th>
                                                <th>Дрон</th>
                                                <th>БК</th>
                                                <th class="d-none d-lg-table-cell">Координати</th>
                                                <th class="d-none d-xl-table-cell">Стрім</th>
                                                <th class="d-none d-md-table-cell">Дет.</th>
                                                <th>Рез.</th>
                                                <th class="d-none d-lg-table-cell">Примітка</th>
                                                <th>Дії</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($flights as $flight)
                                                <tr>
                                                    <td class="pl-3 text-nowrap">{{ \Carbon\Carbon::parse($flight['flight_time'])->format('H:i') }}</td>
                                                    <td>{{ $flight['drone_name'] }}</td>
                                                    <td>{{ $flight['ammunition_name'] }}</td>
                                                    <td class="d-none d-lg-table-cell">{{ $flight['coordinates'] }}</td>
                                                    <td class="d-none d-xl-table-cell">{{ $flight['stream'] }}</td>
                                                    <td class="d-none d-md-table-cell">{{ $flight['detonation'] ?? 'ні' }}</td>
                                                    <td>
                                                        @php
                                                            $badgeClass = match($flight['result']) {
                                                                'влучання' => 'success',
                                                                'удар в районі цілі' => 'warning',
                                                                'втрата борту' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                            $shortResult = match($flight['result']) {
                                                                'влучання' => 'вл.',
                                                                'удар в районі цілі' => 'уд.',
                                                                'втрата борту' => 'втрата',
                                                                default => $flight['result']
                                                            };
                                                        @endphp
                                                        <span class="badge badge-{{ $badgeClass }} d-none d-md-inline">{{ $flight['result'] }}</span>
                                                        <span class="badge badge-{{ $badgeClass }} d-inline d-md-none" title="{{ $flight['result'] }}">{{ $shortResult }}</span>
                                                    </td>
                                                    <td class="small d-none d-lg-table-cell">{{ $flight['note'] }}</td>
                                                    <td>
                                                        <div class="btn-group">
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
                                                        </div>
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
                <div class="card-body table-responsive">
                    <h5>Дрони (залишок)</h5>
                    <table class="table table-sm text-nowrap">
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
                    <table class="table table-sm text-nowrap">
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
