@extends('adminlte::page')

@section('title', 'Деталі чергування розвідки')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Чергування розвідки "{{ $shift->position_name }}"</h1>
        <div>
            <a href="{{ route('recon.combat_shifts.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до списку
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
                        @if($shift->status !== 'opened')
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
                        <li class="nav-item"><a class="nav-link active" href="#flights" data-toggle="tab">Польоти</a></li>
                        <li class="nav-item"><a class="nav-link" href="#inventory" data-toggle="tab">Майно</a></li>
                        <li class="nav-item"><a class="nav-link" href="#crew" data-toggle="tab">Екіпаж</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="flights">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Журнал польотів розвідки</h5>
                                <a href="{{ route('recon.combat_shifts.flights_report', $shift->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-file-alt"></i> Повний звіт по польотам
                                </a>
                            </div>

                            @php
                                $today = now()->format('Y-m-d');
                            @endphp

                            @forelse($shift->recon_flights as $date => $dayFlights)
                                <div class="card card-outline card-secondary mb-2">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}
                                            @if($date == $today)
                                                <span class="badge badge-primary ml-2">Сьогодні</span>
                                            @endif
                                            <span class="ml-2 text-muted">({{ count($dayFlights) }})</span>
                                        </h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas {{ $loop->first ? 'fa-minus' : 'fa-plus' }}"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0" style="{{ $loop->first ? '' : 'display: none;' }}">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="pl-3">Час</th>
                                                        <th>Зміна</th>
                                                        <th>Дрон</th>
                                                        <th>Ціль/Координати</th>
                                                        <th class="d-none d-md-table-cell">Місія</th>
                                                        <th class="d-none d-xl-table-cell">Стрім</th>
                                                        <th>Результат</th>
                                                        <th>Відео</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dayFlights as $flight)
                                                        <tr>
                                                            <td class="pl-3 text-nowrap">{{ \Carbon\Carbon::parse($flight['flight_time'])->format('H:i') }}</td>
                                                            <td>
                                                                @if($flight['shift_type'] === 'day')
                                                                    <i class="fas fa-sun text-warning" title="Денна"></i>
                                                                @else
                                                                    <i class="fas fa-moon text-secondary" title="Нічна"></i>
                                                                @endif
                                                            </td>
                                                            <td>{{ $flight['drone_name'] }}</td>
                                                            <td>
                                                                @if($flight['mission_type'] === 'delivery')
                                                                    <strong>{{ $flight['target_name'] }}</strong>
                                                                @else
                                                                    <span class="small">{{ $flight['coordinates'] }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="d-none d-md-table-cell text-nowrap">{{ $flight['mission_type_label'] }}</td>
                                                            <td class="d-none d-xl-table-cell">
                                                                {!! $flight['stream_status'] ? '<span class="text-success">+</span>' : '<span class="text-danger">-</span>' !!}
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-{{ $flight['result'] === 'success' ? 'success' : 'danger' }}">
                                                                    {{ $flight['result_label'] }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if(!empty($flight['video_path']))
                                                                    <div class="btn-group">
                                                                        <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $flight['id'] }}" title="Переглянути">
                                                                            <i class="fas fa-video"></i>
                                                                        </button>
                                                                        <a href="{{ route('recon.flights.download', $flight['id']) }}" class="btn btn-xs btn-success" title="Скачати">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    </div>

                                                                    <div class="modal fade" id="videoModal{{ $flight['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                                        <div class="modal-dialog modal-lg" role="document">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Відео польоту ({{ \Carbon\Carbon::parse($flight['flight_time'])->format('H:i') }})</h5>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body text-center bg-black">
                                                                                    <video width="100%" controls>
                                                                                        <source src="{{ Storage::url($flight['video_path']) }}" type="video/mp4">
                                                                                        Ваш браузер не підтримує відео.
                                                                                    </video>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-paper-plane fa-3x mb-3"></i>
                                    <p>Польотів ще не зафіксовано</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="tab-pane" id="inventory">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
