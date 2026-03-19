@extends('adminlte::page')

@section('title', 'Вильоти Vampire')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Вильоти Vampire ({{ $userActiveShift->position_name }})</h1>
        <div class="d-flex align-items-center">
            <div class="mr-4">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-outline-warning {{ $activeShiftType === 'day' ? 'active' : '' }}">
                        <input type="radio" name="global_shift_type" value="day" {{ $activeShiftType === 'day' ? 'checked' : '' }}>
                        <i class="fas fa-sun"></i> Денна
                    </label>
                    <label class="btn btn-outline-secondary {{ $activeShiftType === 'night' ? 'active' : '' }}">
                        <input type="radio" name="global_shift_type" value="night" {{ $activeShiftType === 'night' ? 'checked' : '' }}>
                        <i class="fas fa-moon"></i> Нічна
                    </label>
                </div>
            </div>
            <a href="{{ route('vampire.combat_shifts.show', $userActiveShift->id) }}" class="btn btn-default">
                <i class="fas fa-eye"></i> Деталі чергування
            </a>
        </div>
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
                            <label for="start_time">Час зльоту</label>
                            <div class="input-group">
                                <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="setCurrentTime('start_time')" title="Зараз">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_time">Час посадки</label>
                            <div class="input-group">
                                <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="setCurrentTime('end_time')" title="Зараз">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="mission_type">Місія</label>
                            <select name="mission_type" id="mission_type" class="form-control" required>
                                <option value="combat">бойова (мінування, бімба)</option>
                                <option value="logistics">логістика</option>
                            </select>
                        </div>
                        <div id="ammunition-section" style="display: none;">
                            <div id="ammunition-container">
                                <div class="form-group ammunition-row">
                                    <label>Боєприпаси</label>
                                    <div class="row mb-2">
                                        <div class="col-8">
                                            <select name="ammunition[0][id]" class="form-control select2">
                                                <option value="">-- Оберіть БК --</option>
                                                @foreach($userActiveShift->ammunition as $ammo)
                                                    <option value="{{ $ammo['id'] }}">{{ $ammo['name'] }} (Залишок: {{ $ammo['quantity'] }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" name="ammunition[0][quantity]" class="form-control" value="1" min="1" placeholder="К-ть">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-info mb-3" id="add-ammunition">
                                <i class="fas fa-plus"></i> Додати боєприпас
                            </button>
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
                                        <div class="btn-group">
                                            <a href="{{ route('vampire.flight_plans.edit', $plan['id']) }}" class="btn btn-xs btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('vampire.flight_plans.destroy', $plan['id']) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Видалити з плану?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
                                    <th>Зміна</th>
                                    <th>Дрон</th>
                                    <th>Ціль</th>
                                    <th>Місія</th>
                                    <th>БК</th>
                                    <th>Результат</th>
                                    <th>Коментар</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($flights as $flight)
                                    <tr>
                                    <td>
                                        <div class="text-nowrap">{{ \Carbon\Carbon::parse($flight->start_time)->format('H:i') }}</div>
                                        @if($flight->end_time)
                                            <div class="text-nowrap text-muted small">{{ \Carbon\Carbon::parse($flight->end_time)->format('H:i') }}</div>
                                        @endif
                                    </td>
                                        <td>
                                            @if($flight->shift_type?->value === 'day')
                                                <i class="fas fa-sun text-warning" title="Денна"></i>
                                            @elseif($flight->shift_type?->value === 'night')
                                                <i class="fas fa-moon text-secondary" title="Нічна"></i>
                                            @else
                                                -
                                            @endif
                                        </td>
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
                                            @foreach($flight->ammunition as $ammo)
                                                <div>{{ $ammo->name }} ({{ $ammo->pivot->quantity }})</div>
                                            @endforeach
                                            @if($flight->ammunition->isEmpty()) - @endif
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
                                            <div class="btn-group">
                                                <a href="{{ route('vampire.flights.edit', $flight->id) }}" class="btn btn-xs btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('vampire.flights.destroy', $flight->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Видалити цей виліт?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
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

@section('js')
    <script>
        function setCurrentTime(fieldId) {
            const now = new Date();
            const offset = now.getTimezoneOffset() * 60000;
            const localISOTime = (new Date(now - offset)).toISOString().slice(0, 16);
            document.getElementById(fieldId).value = localISOTime;
        }

        $(document).ready(function() {
            function toggleAmmunition() {
                if ($('#mission_type').val() === 'combat') {
                    $('#ammunition-section').show();
                } else {
                    $('#ammunition-section').hide();
                }
            }

            $('#mission_type').on('change', toggleAmmunition);
            toggleAmmunition();

            $('input[name="global_shift_type"]').on('change', function() {
                let shiftType = $(this).val();
                $.ajax({
                    url: '{{ route('vampire.flights.set_shift_type') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        shift_type: shiftType
                    },
                    success: function() {
                        location.reload();
                    },
                    error: function() {
                        alert('Помилка при зміні типу зміни');
                    }
                });
            });

            let ammoCount = 1;
            $('#add-ammunition').on('click', function() {
                let newRow = `
                    <div class="row mb-2">
                        <div class="col-8">
                            <select name="ammunition[${ammoCount}][id]" class="form-control">
                                <option value="">-- Оберіть БК --</option>
                                @foreach($userActiveShift->ammunition as $ammo)
                                    <option value="{{ $ammo['id'] }}">{{ $ammo['name'] }} (Залишок: {{ $ammo['quantity'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="number" name="ammunition[${ammoCount}][quantity]" class="form-control" value="1" min="1" placeholder="К-ть">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger remove-ammo"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#ammunition-container').append(newRow);
                ammoCount++;
            });

            $(document).on('click', '.remove-ammo', function() {
                $(this).closest('.row').remove();
            });
        });
    </script>
@endsection
