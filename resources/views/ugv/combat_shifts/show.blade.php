@extends('adminlte::page')

@section('title', 'Деталі чергування UGV')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Чергування UGV "{{ $shift->position_name }}"</h1>
        <div>
            <a href="{{ route('ugv.combat_shifts.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до списку
            </a>
            <a href="{{ route('ugv.combat_shifts.report', $shift->id) }}" class="btn btn-primary ml-2">
                <i class="fas fa-file-alt"></i> Звіт по залишку
            </a>
            <a href="{{ route('ugv.combat_shifts.spending_report', $shift->id) }}" class="btn btn-info ml-2">
                <i class="fas fa-chart-line"></i> Звіт по витратах
            </a>
            <a href="{{ route('ugv.combat_shifts.races_report', $shift->id) }}" class="btn btn-secondary ml-2">
                <i class="fas fa-truck"></i> Звіт по рейсам
            </a>
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
                        <li class="nav-item"><a class="nav-link active" href="#races" data-toggle="tab">Рейси</a></li>
                        <li class="nav-item"><a class="nav-link" href="#inventory" data-toggle="tab">Майно</a></li>
                        <li class="nav-item"><a class="nav-link" href="#crew" data-toggle="tab">Екіпаж</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="races">
                            <h5>Журнал вильотів</h5>
                            @php
                                $today = now()->format('Y-m-d');
                            @endphp

                            @forelse($shift->ugv_races as $date => $dayRaces)
                                <div class="card card-outline card-secondary mb-2">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            {{ $date }}
                                            @if($date == $today)
                                                <span class="badge badge-primary ml-2">Сьогодні</span>
                                            @endif
                                            <span class="ml-2 text-muted">({{ count($dayRaces) }})</span>
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
                                                        <th>НРК</th>
                                                        <th>Ціль</th>
                                                        <th class="d-none d-lg-table-cell">Координати</th>
                                                        <th class="d-none d-md-table-cell">Місія</th>
                                                        <th class="d-none d-xl-table-cell">Стрім</th>
                                                        <th>Відео</th>
                                                        <th>Результат</th>
                                                        <th class="d-none d-lg-table-cell">Коментар</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dayRaces as $race)
                                                        <tr>
                                                            <td class="pl-3 text-nowrap">{{ \Carbon\Carbon::parse($race['start_time'])->format('H:i') }}</td>
                                                            <td>{{ $race['drone_name'] }} <small class="text-muted d-none d-md-inline">({{ $race['drone_serial'] ?? '-' }})</small></td>
                                                            <td><strong>{{ $race['position_name'] }}</strong></td>
                                                            <td class="d-none d-lg-table-cell small">{{ $race['coordinates'] }}</td>
                                                            <td class="d-none d-md-table-cell text-nowrap">{{ $race['mission_type_label'] }}</td>
                                                            <td class="d-none d-xl-table-cell">
                                                                {!! $race['stream_status'] ? '<span class="text-success">+</span>' : '<span class="text-danger">-</span>' !!}
                                                            </td>
                                                            <td>
                                                                @if(!empty($race['video_path']))
                                                                    <div class="btn-group">
                                                                        <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $race['id'] }}" title="Переглянути">
                                                                            <i class="fas fa-video"></i>
                                                                        </button>
                                                                        <a href="{{ route('ugv.races.download', $race['id']) }}" class="btn btn-xs btn-success" title="Скачати">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    </div>

                                                                    <div class="modal fade" id="videoModal{{ $race['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                                        <div class="modal-dialog modal-lg" role="document">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Відео рейсу ({{ \Carbon\Carbon::parse($race['start_time'])->format('H:i') }})</h5>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body text-center bg-black">
                                                                                    <video width="100%" controls>
                                                                                        <source src="{{ Storage::url($race['video_path']) }}" type="video/mp4">
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
                                                            <td>
                                                                @php
                                                                    $badgeClass = match($race['result']) {
                                                                        'worked' => 'success',
                                                                        'loss' => 'danger',
                                                                        'not_worked' => 'warning',
                                                                        default => 'secondary'
                                                                    };
                                                                @endphp
                                                                <span class="badge badge-{{ $badgeClass }}">{{ $race['result_label'] }}</span>
                                                            </td>
                                                            <td class="d-none d-lg-table-cell small">{{ $race['comment'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-center">
                                    <span class="text-muted">Рейсів не зафіксовано</span>
                                </div>
                            @endforelse
                        </div>

                        <div class="tab-pane" id="inventory">
                            <h5>НРК на позиції</h5>
                            <table class="table table-bordered table-sm mb-4">
                                <thead>
                                    <tr>
                                        <th>Назва</th>
                                        <th>Серійний номер</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shift->ugv_drones as $drone)
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
                                            <td colspan="3" class="text-center">НРК на позиції відсутні</td>
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

@section('js')
@endsection
