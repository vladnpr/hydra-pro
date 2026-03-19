@extends('adminlte::page')

@section('title', 'Редагувати виліт Vampire')

@section('content_header')
    <h1>Редагувати виліт</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Виліт від {{ $flight->start_time->format('H:i d.m.Y') }}</h3>
                </div>
                <form action="{{ route('vampire.flights.update', $flight->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="vampire_flight_plan_id">Ціль з плану</label>
                            <select name="vampire_flight_plan_id" id="vampire_flight_plan_id" class="form-control">
                                <option value="">-- Оберіть ціль (не обов'язково) --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan['id'] }}" {{ old('vampire_flight_plan_id', $flight->vampire_flight_plan_id) == $plan['id'] ? 'selected' : '' }}>
                                        {{ $plan['position_name'] }} {{ $plan['coordinates'] ? "({$plan['coordinates']})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vampire_drone_id">Дрон</label>
                            <select name="vampire_drone_id" id="vampire_drone_id" class="form-control" required>
                                @foreach($drones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('vampire_drone_id', $flight->vampire_drone_id) == $drone['id'] ? 'selected' : '' }}>
                                        {{ $drone['name'] }} ({{ $drone['serial_number'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_time">Час зльоту</label>
                            <div class="input-group">
                                <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', $flight->start_time->format('Y-m-d\TH:i')) }}" required>
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
                                <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', $flight->end_time?->format('Y-m-d\TH:i')) }}">
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
                                <option value="logistics" {{ old('mission_type', $flight->mission_type) === 'logistics' ? 'selected' : '' }}>логістика</option>
                                <option value="combat" {{ old('mission_type', $flight->mission_type) === 'combat' ? 'selected' : '' }}>бойова (мінування, бімба)</option>
                            </select>
                        </div>
                        <div class="form-group" id="coordinates-section" style="{{ old('mission_type', $flight->mission_type) === 'combat' ? '' : 'display: none;' }}">
                            <label for="flight_coordinates">Координати</label>
                            <input type="text" name="coordinates" id="flight_coordinates" class="form-control" value="{{ old('coordinates', $flight->coordinates) }}" placeholder="47.123, 37.456">
                        </div>
                        <div id="ammunition-section" style="{{ old('mission_type', $flight->mission_type) === 'combat' ? '' : 'display: none;' }}">
                            <div id="ammunition-container">
                                @php
                                    $currentAmmunition = old('ammunition', $flight->ammunition->map(fn($a) => ['id' => $a->id, 'quantity' => $a->pivot->quantity])->toArray());
                                    if (empty($currentAmmunition)) {
                                        $currentAmmunition = [['id' => '', 'quantity' => 1]];
                                    }
                                @endphp
                                @foreach($currentAmmunition as $index => $currentAmmo)
                                    <div class="form-group ammunition-row row mb-2">
                                        <div class="col-8">
                                            <select name="ammunition[{{ $index }}][id]" class="form-control select2">
                                                <option value="">-- Оберіть БК --</option>
                                                @foreach($userActiveShift->ammunition as $ammo)
                                                    <option value="{{ $ammo['id'] }}" {{ $currentAmmo['id'] == $ammo['id'] ? 'selected' : '' }}>
                                                        {{ $ammo['name'] }} (Залишок: {{ $ammo['quantity'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <input type="number" name="ammunition[{{ $index }}][quantity]" class="form-control" value="{{ $currentAmmo['quantity'] }}" min="1" placeholder="К-ть">
                                                @if($index > 0)
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-danger remove-ammo"><i class="fas fa-times"></i></button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-info mb-3" id="add-ammunition">
                                <i class="fas fa-plus"></i> Додати боєприпас
                            </button>
                        </div>
                        <div class="form-group">
                            <label for="result">Результат</label>
                            <select name="result" id="result" class="form-control" required>
                                <option value="worked" {{ old('result', $flight->result) === 'worked' ? 'selected' : '' }}>відпрацювали</option>
                                <option value="loss" {{ old('result', $flight->result) === 'loss' ? 'selected' : '' }}>втрата борту</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="stream_status" name="stream_status" value="1" {{ old('stream_status', $flight->stream_status) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="stream_status">Стрім</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Коментар</label>
                            <textarea name="comment" id="comment" class="form-control" rows="2" placeholder="450, збили, подавлення, інше">{{ old('comment', $flight->comment) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Зберегти зміни</button>
                        <a href="{{ route('vampire.flights.index') }}" class="btn btn-default">Скасувати</a>
                    </div>
                </form>
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
            function toggleCombatFields() {
                if ($('#mission_type').val() === 'combat') {
                    $('#ammunition-section').show();
                    $('#coordinates-section').show();
                } else {
                    $('#ammunition-section').hide();
                    $('#coordinates-section').hide();
                }
            }

            $('#mission_type').on('change', toggleCombatFields);

            let ammoCount = {{ count($currentAmmunition) }};
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
