@extends('adminlte::page')

@section('title', 'Вильоти Vampire')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Вильоти Vampire ({{ $userActiveShift->position_name }})</h1>
        <a href="{{ route('vampire.combat_shifts.show', $userActiveShift->id) }}" class="btn btn-default">
            <i class="fas fa-eye"></i> Деталі чергування
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Додати ціль до плану</h3>
                </div>
                <form action="{{ route('vampire.flight_plans.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="combat_shift_id" value="{{ $userActiveShift->id }}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="position_name">Назва позиції / Цілі</label>
                            <input type="text" name="position_name" id="position_name" class="form-control" placeholder="напр. ПНГ 1" required>
                        </div>
                        <div class="form-group">
                            <label for="coordinates">Координати</label>
                            <input type="text" name="coordinates" id="coordinates" class="form-control" placeholder="47.123, 37.456">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info btn-block">Додати в план</button>
                    </div>
                </form>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Зафіксувати новий виліт</h3>
                </div>
                <form action="{{ route('vampire.flights.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="combat_shift_id" value="{{ $userActiveShift->id }}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="vampire_flight_plan_id">Ціль з плану</label>
                            <select name="vampire_flight_plan_id" id="vampire_flight_plan_id" class="form-control">
                                <option value="">-- Оберіть ціль (не обов'язково) --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan['id'] }}">{{ $plan['position_name'] }} {{ $plan['coordinates'] ? "({$plan['coordinates']})" : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vampire_drone_id">Дрон</label>
                            <select name="vampire_drone_id" id="vampire_drone_id" class="form-control" required>
                                @foreach($drones as $drone)
                                    <option value="{{ $drone['id'] }}">{{ $drone['name'] }} ({{ $drone['serial_number'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="flight_time">Час</label>
                            <input type="datetime-local" name="flight_time" id="flight_time" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="mission_type">Місія</label>
                            <select name="mission_type" id="mission_type" class="form-control" required>
                                <option value="combat">бойова (мінування, бімба)</option>
                                <option value="logistics">логістика</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="result">Результат</label>
                            <select name="result" id="result" class="form-control" required>
                                <option value="worked">відпрацювали</option>
                                <option value="loss">втрата борту</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="stream_status" name="stream_status" value="1" checked>
                                <label class="custom-control-label" for="stream_status">Стрім</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Коментар</label>
                            <textarea name="comment" id="comment" class="form-control" rows="2" placeholder="450, збили, подавлення, інше"></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">Додати виліт</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">План польотів</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Ціль</th>
                                <th>Координати</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $plan['position_name'] }}</td>
                                    <td>{{ $plan['coordinates'] }}</td>
                                    <td>
                                        <form action="{{ route('vampire.flight_plans.destroy', $plan['id']) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Видалити з плану?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">План порожній</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Журнал вильотів</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Час</th>
                                    <th>Дрон</th>
                                    <th>Ціль</th>
                                    <th>Місія</th>
                                    <th>Результат</th>
                                    <th>Коментар</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($flights as $flight)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($flight->flight_time)->format('H:i') }}</td>
                                        <td>{{ $flight->drone?->name }}</td>
                                        <td>{{ $flight->flightPlan?->position_name ?? '-' }}</td>
                                        <td>
                                            @if($flight->mission_type === 'combat')
                                                <span class="badge badge-danger">бойова</span>
                                            @else
                                                <span class="badge badge-info">логістика</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($flight->result === 'worked')
                                                <span class="badge badge-success">відпрацювали</span>
                                            @else
                                                <span class="badge badge-dark">втрата</span>
                                            @endif
                                        </td>
                                        <td>{{ $flight->comment }}</td>
                                        <td>
                                            <form action="{{ route('vampire.flights.destroy', $flight->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Видалити цей виліт?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Вильотів ще не зафіксовано</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
