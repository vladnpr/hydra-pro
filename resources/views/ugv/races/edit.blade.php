@extends('adminlte::page')

@section('title', 'Редагувати рейс UGV')

@section('content_header')
    <h1>Редагувати рейс</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Рейс від {{ $race->start_time->format('H:i d.m.Y') }}</h3>
                </div>
                <form action="{{ route('ugv.races.update', $race->id) }}" method="POST" enctype="multipart/form-data">
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
                            <label for="ugv_race_plan_id">Рейс з плану</label>
                            <select name="ugv_race_plan_id" id="ugv_race_plan_id" class="form-control">
                                <option value="">-- Оберіть рейс (не обов'язково) --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan['id'] }}" {{ old('ugv_race_plan_id', $race->ugv_race_plan_id) == $plan['id'] ? 'selected' : '' }}>
                                        {{ $plan['position_name'] }} {{ $plan['coordinates'] ? "({$plan['coordinates']})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ugv_drone_id">НРК</label>
                            <select name="ugv_drone_id" id="ugv_drone_id" class="form-control" required>
                                @foreach($drones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('ugv_drone_id', $race->ugv_drone_id) == $drone['id'] ? 'selected' : '' }}>
                                        {{ $drone['name'] }} ({{ $drone['serial_number'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_time">Час зльоту</label>
                            <div class="input-group">
                                <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', $race->start_time->format('Y-m-d\TH:i')) }}" required>
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
                                <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', $race->end_time?->format('Y-m-d\TH:i')) }}">
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
                                <option value="logistics" {{ old('mission_type', $race->mission_type) === 'logistics' ? 'selected' : '' }}>логістика</option>
                                <option value="combat" {{ old('mission_type', $race->mission_type) === 'combat' ? 'selected' : '' }}>бойова</option>
                                <option value="evac" {{ old('mission_type', $race->mission_type) === 'evac' ? 'selected' : '' }}>евак</option>
                            </select>
                        </div>
                        <div class="form-group" id="coordinates-section" style="{{ old('mission_type', $race->mission_type) === 'combat' ? '' : 'display: none;' }}">
                            <label for="race_coordinates">Координати</label>
                            <input type="text" name="coordinates" id="race_coordinates" class="form-control" value="{{ old('coordinates', $race->coordinates) }}" placeholder="47.123, 37.456">
                        </div>
                        <div id="ammunition-section" style="{{ old('mission_type', $race->mission_type) === 'combat' ? '' : 'display: none;' }}">
                            <div id="ammunition-container">
                                @php
                                    $currentAmmunition = old('ammunition', $race->ammunition->map(fn($a) => ['id' => $a->id, 'quantity' => $a->pivot->quantity])->toArray());
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
                                <option value="worked" {{ old('result', $race->result) === 'worked' ? 'selected' : '' }}>відпрацювали</option>
                                <option value="not_worked" {{ old('result', $race->result) === 'not_worked' ? 'selected' : '' }}>не відпрацювали</option>
                                <option value="loss" {{ old('result', $race->result) === 'loss' ? 'selected' : '' }}>втрата борту</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="stream_status" name="stream_status" value="1" {{ old('stream_status', $race->stream_status) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="stream_status">Стрім</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Коментар</label>
                            <textarea name="comment" id="comment" class="form-control" rows="2" placeholder="450, збили, подавлення, інше">{{ old('comment', $race->comment) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="video">Відео рейсу (макс. 75мб)</label>
                            @if($race->video_path)
                                <div class="mb-2 p-2 bg-black text-center rounded">
                                    <small class="text-muted d-block mb-2">Відео вже завантажено. Новий файл замінить старий.</small>
                                    <video width="320" height="240" controls>
                                        <source src="{{ Storage::url($race->video_path) }}" type="video/mp4">
                                        Ваш браузер не підтримує відео.
                                    </video>
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" name="video" class="custom-file-input @error('video') is-invalid @enderror" id="video" accept="video/*">
                                <label class="custom-file-label" for="video">Оберіть файл</label>
                            </div>
                            @error('video')
                                <span class="error invalid-feedback" style="display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Зберегти зміни</button>
                        <a href="{{ route('ugv.races.index') }}" class="btn btn-default">Скасувати</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .bg-black { background-color: #000; }
    </style>
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

            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@endsection
