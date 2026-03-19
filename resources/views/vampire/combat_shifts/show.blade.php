@extends('adminlte::page')

@section('title', 'Деталі чергування Vampire')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Чергування Vampire "{{ $shift->position_name }}"</h1>
        <div>
            <a href="{{ route('vampire.combat_shifts.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до списку
            </a>
            <a href="{{ route('vampire.combat_shifts.report', $shift->id) }}" class="btn btn-primary ml-2">
                <i class="fas fa-file-alt"></i> Звіт по залишку
            </a>
            <a href="{{ route('vampire.combat_shifts.spending_report', $shift->id) }}" class="btn btn-info ml-2">
                <i class="fas fa-chart-line"></i> Звіт по витратах
            </a>
            <a href="{{ route('vampire.combat_shifts.flights_report', $shift->id) }}" class="btn btn-secondary ml-2">
                <i class="fas fa-paper-plane"></i> Звіт по польотам
            </a>
            @can('manage-vampire')
                @if($shift->status === 'opened')
                    @php
                        $userIds = collect($shift->users)->pluck('id')->toArray();
                        $isUserInShift = in_array(auth()->id(), $userIds);
                    @endphp

                    @if(!$isUserInShift && !$userActiveShift)
                        <form action="{{ route('vampire.combat_shifts.join', $shift->id) }}" method="POST" style="display:inline-block;" class="ml-2">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-sign-in-alt"></i> Приєднатися
                            </button>
                        </form>
                    @endif

                    @if($isUserInShift)
                        <form action="{{ route('vampire.combat_shifts.leave', $shift->id) }}" method="POST" style="display:inline-block;" class="ml-2">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Ви впевнені, що хочете покинути чергування?')">
                                <i class="fas fa-sign-out-alt"></i> Відключитися
                            </button>
                        </form>
                    @endif
                @endif
                <a href="{{ route('vampire.combat_shifts.edit', $shift->id) }}" class="btn btn-info ml-2">
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
                    @can('manage-vampire')
                        @if($shift->status === 'opened')
                            <form action="{{ route('vampire.combat_shifts.finish', $shift->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Завершити чергування?')">
                                    Завершити чергування
                                </button>
                            </form>
                        @else
                            <form action="{{ route('vampire.combat_shifts.reopen', $shift->id) }}" method="POST">
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
                        <li class="nav-item"><a class="nav-link active" href="#plans" data-toggle="tab">План польотів</a></li>
                        <li class="nav-item"><a class="nav-link" href="#flights" data-toggle="tab">Вильоти</a></li>
                        <li class="nav-item"><a class="nav-link" href="#inventory" data-toggle="tab">Майно</a></li>
                        <li class="nav-item"><a class="nav-link" href="#crew" data-toggle="tab">Екіпаж</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="plans">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>План польотів</h5>
                                @if($shift->status === 'opened')
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-add-plan">
                                        <i class="fas fa-plus"></i> Додати ціль
                                    </button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Назва позиції</th>
                                            <th>Координати</th>
                                            <th>Статус</th>
                                            <th>Дії</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($shift->vampire_flight_plans as $plan)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $plan['position_name'] }}</td>
                                                <td>{{ $plan['coordinates'] ?? '-' }}</td>
                                                <td>
                                                    @if($plan['status'] === 'completed')
                                                        <span class="badge badge-success">Відпрацьовано</span>
                                                    @else
                                                        <span class="badge badge-warning">Заплановано</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        @if($shift->status === 'opened')
                                                            <button type="button" class="btn btn-xs btn-success btn-start-flight"
                                                                    data-plan-id="{{ $plan['id'] }}"
                                                                    data-position-name="{{ $plan['position_name'] }}"
                                                                    data-coordinates="{{ $plan['coordinates'] }}"
                                                                    title="Зафіксувати виліт">
                                                                <i class="fas fa-plane-departure"></i>
                                                            </button>
                                                            <form action="{{ route('vampire.flight_plans.destroy', $plan['id']) }}" method="POST" style="display:inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Видалити ціль з плану?')" title="Видалити">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">План польотів порожній</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="flights">
                            <h5>Журнал вильотів</h5>
                            <div class="table-responsive">
                                @foreach($shift->vampire_flights as $date => $dayFlights)
                                    <h6>{{ $date }}</h6>
                                    <table class="table table-bordered table-striped table-sm mb-4">
                                        <thead>
                                            <tr>
                                                <th>Час</th>
                                                <th>Дрон</th>
                                                <th>Ціль</th>
                                                <th>Місія</th>
                                                <th>Стрім</th>
                                                <th>Результат</th>
                                                <th>Коментар</th>
                                                <th>Дії</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dayFlights as $flight)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($flight['start_time'])->format('H:i') }}</td>
                                                    <td>{{ $flight['drone_name'] }} ({{ $flight['drone_serial'] ?? '-' }})</td>
                                                    <td>
                                                        <strong>{{ $flight['position_name'] }}</strong><br>
                                                        <small>{{ $flight['coordinates'] }}</small>
                                                    </td>
                                                    <td>{{ $flight['mission_type_label'] }}</td>
                                                    <td>{!! $flight['stream_status'] ? '<span class="text-success">+</span>' : '<span class="text-danger">-</span>' !!}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $flight['result'] === 'worked' ? 'success' : 'danger' }}">
                                                            {{ $flight['result_label'] }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $flight['comment'] }}</td>
                                                    <td>
                                                        @if($shift->status === 'opened')
                                                            <form action="{{ route('vampire.flights.destroy', $flight['id']) }}" method="POST" style="display:inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Видалити запис про виліт?')" title="Видалити">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endforeach
                            </div>
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
                                    @forelse($shift->vampire_drones as $drone)
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

@section('js')
<div class="modal fade" id="modal-add-plan">
    <div class="modal-dialog">
        <form action="{{ route('vampire.flight_plans.store') }}" method="POST">
            @csrf
            <input type="hidden" name="combat_shift_id" value="{{ $shift->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Додати ціль до плану</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Назва позиції</label>
                        <input type="text" name="position_name" class="form-control" required placeholder="Наприклад: лимон">
                    </div>
                    <div class="form-group">
                        <label>Координати</label>
                        <input type="text" name="coordinates" class="form-control" placeholder="Наприклад: 37U CP 62346 55715">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрити</button>
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modal-add-flight">
    <div class="modal-dialog">
        <form action="{{ route('vampire.flights.store') }}" method="POST">
            @csrf
            <input type="hidden" name="combat_shift_id" value="{{ $shift->id }}">
            <input type="hidden" name="vampire_flight_plan_id" id="flight_plan_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Фіксація вильоту: <span id="display-position-name"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Час</label>
                        <input type="datetime-local" name="flight_time" class="form-control" required value="{{ date('Y-m-d\TH:i') }}">
                    </div>
                    <div class="form-group">
                        <label>Дрон</label>
                        <select name="vampire_drone_id" class="form-control" required>
                            @foreach($shift->vampire_drones as $drone)
                                <option value="{{ $drone['id'] }}">{{ $drone['name'] }} ({{ $drone['serial_number'] ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Місія</label>
                        <select name="mission_type" class="form-control" required>
                            <option value="combat">бойова (мінування, бімба)</option>
                            <option value="logistics">логістика</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Результат</label>
                        <select name="result" class="form-control" required>
                            <option value="worked">відпрацювали</option>
                            <option value="loss">втрата борту</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="stream_status" name="stream_status" value="1" checked>
                            <label for="stream_status" class="custom-control-label">Стрім +</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Коментар</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="450, збили, подавлення, інше..."></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрити</button>
                    <button type="submit" class="btn btn-primary">Зберегти виліт</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.btn-start-flight').on('click', function() {
            var planId = $(this).data('plan-id');
            var positionName = $(this).data('position-name');

            $('#flight_plan_id').val(planId);
            $('#display-position-name').text(positionName);
            $('#modal-add-flight').modal('show');
        });
    });
</script>
@endsection
