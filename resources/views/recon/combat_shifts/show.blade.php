@extends('adminlte::page')

@section('title', 'Деталі чергування розвідки')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Чергування розвідки #{{ $shift->id }}</h1>
        <div>
            <a href="{{ route('recon.combat_shifts.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до списку
            </a>
            <a href="{{ route('combat_shifts.spending_fpv_report', $shift->id) }}" class="btn btn-warning ml-2">
                <i class="fas fa-bomb"></i> Звіт по витратам
            </a>
            <a href="{{ route('recon.combat_shifts.report', $shift->id) }}" class="btn btn-primary ml-2">
                <i class="fas fa-file-alt"></i> Звіт по залишку
            </a>
            <a href="{{ route('recon.combat_shifts.flights_report', $shift->id) }}" class="btn btn-secondary ml-2">
                <i class="fas fa-paper-plane"></i> Звіт по польотам
            </a>
            @can('manage-recon')
                @if($shift->status === 'opened')
                    @php
                        $userIds = collect($shift->users)->pluck('id')->toArray();
                        $isUserInShift = in_array(auth()->id(), $userIds);
                    @endphp

                    @if(!$isUserInShift && !$userActiveShift)
                        <form action="{{ route('recon.combat_shifts.join', $shift->id) }}" method="POST" style="display:inline-block;" class="ml-2">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-sign-in-alt"></i> Приєднатися
                            </button>
                        </form>
                    @endif

                    @if($isUserInShift)
                        <form action="{{ route('recon.combat_shifts.leave', $shift->id) }}" method="POST" style="display:inline-block;" class="ml-2">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Ви впевнені, що хочете покинути чергування?')">
                                <i class="fas fa-sign-out-alt"></i> Відключитися
                            </button>
                        </form>
                    @endif
                @endif
                <a href="{{ route('recon.flights.index') }}" class="btn btn-success ml-2">
                    <i class="fas fa-paper-plane"></i> Польоти
                </a>
                <a href="{{ route('recon.combat_shifts.edit', $shift->id) }}" class="btn btn-info ml-2">
                    <i class="fas fa-edit"></i> Редагувати
                </a>
            @endcan
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Інформація</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Позиція</strong>
                    <p class="text-muted">{{ $shift->position_name }}</p>
                    <hr>
                    <strong><i class="fas fa-info-circle mr-1"></i> Статус</strong>
                    <p><span class="badge badge-{{ $shift->status_color }}">{{ $shift->status_label }}</span></p>
                    <hr>
                    <strong><i class="fas fa-clock mr-1"></i> Початок</strong>
                    <p class="text-muted">{{ $shift->started_at }}</p>
                    <hr>
                    <strong><i class="fas fa-calendar-check mr-1"></i> Завершення</strong>
                    <p class="text-muted">{{ $shift->ended_at ?? '-' }}</p>
                </div>
                <div class="card-footer">
                    @can('manage-recon')
                        @if($shift->status === 'opened')
                            <form action="{{ route('recon.combat_shifts.finish', $shift->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Завершити чергування?')">
                                    Завершити чергування
                                </button>
                            </form>
                        @else
                            <form action="{{ route('recon.combat_shifts.reopen', $shift->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Відновити чергування?')">
                                    Відновити чергування
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Екіпаж системи</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($shift->users as $user)
                            <li class="list-group-item">
                                {{ $user['name'] }}
                                @if(Auth::id() === $user['id'])
                                    <span class="badge badge-primary float-right">Ви</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#inventory" data-toggle="tab">Майно</a></li>
                        <li class="nav-item"><a class="nav-link" href="#crew" data-toggle="tab">Екіпаж</a></li>
                        <li class="nav-item"><a class="nav-link" href="#flights" data-toggle="tab">Останні вильоти</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="inventory">
                            <h5>Дрони на позиції</h5>
                            <table class="table table-bordered table-sm mb-4">
                                <thead>
                                    <tr>
                                        <th>Назва</th>
                                        <th>Серійний номер</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shift->recon_drones as $drone)
                                        <tr>
                                            <td>{{ $drone['name'] }}</td>
                                            <td>{{ $drone['serial_number'] ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $drone['status_color'] }}">
                                                    @if($drone['status'] === 'active') Активний
                                                    @elseif($drone['status'] === 'lost') Втрачений
                                                    @elseif($drone['status'] === 'repair') В ремонті
                                                    @elseif($drone['status'] === 'non_operational') Не боєготовий
                                                    @else {{ $drone['status'] }} @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Дрони на позиції відсутні</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <h5>Боєприпаси</h5>
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Назва</th>
                                        <th>Кількість</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shift->ammunition as $item)
                                        <tr>
                                            <td>{{ $item['name'] }}</td>
                                            <td>{{ $item['quantity'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane" id="crew">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Позивний</th>
                                        <th>Посада</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shift->crew as $member)
                                        <tr>
                                            <td>{{ $member['callsign'] }}</td>
                                            <td>{{ $member['role'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="flights">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Останні зафіксовані польоти розвідки</h5>
                                <a href="{{ route('recon.combat_shifts.flights_report', $shift->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-file-alt"></i> Повний звіт по польотам
                                </a>
                            </div>
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Час</th>
                                        <th>Зміна</th>
                                        <th>Дрон</th>
                                        <th>Місія</th>
                                        <th>Результат</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $count = 0; @endphp
                                    @forelse($shift->recon_flights as $day => $flights)
                                        @foreach($flights as $flight)
                                            @if($count < 10)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($day)->format('d.m.Y') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($flight['flight_time'])->format('H:i') }}</td>
                                                    <td>
                                                        @if($flight['shift_type'] === 'day')
                                                            <i class="fas fa-sun text-warning"></i> Денна
                                                        @else
                                                            <i class="fas fa-moon text-secondary"></i> Нічна
                                                        @endif
                                                    </td>
                                                    <td>{{ $flight['drone_name'] }}</td>
                                                    <td>{{ $flight['mission_type_label'] }}</td>
                                                    <td>{{ $flight['result_label'] }}</td>
                                                </tr>
                                                @php $count++; @endphp
                                            @endif
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Польотів ще не зафіксовано</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            @if(count($shift->recon_flights) > 0)
                                <div class="text-center mt-3">
                                    <a href="{{ route('recon.combat_shifts.flights_report', $shift->id) }}" class="btn btn-default">
                                        Переглянути всі польоти
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
