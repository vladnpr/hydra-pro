@extends('adminlte::page')

@section('title', 'Редагувати політ розвідки')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Редагувати політ розвідки #{{ $flight->id }}</h1>
        <a href="{{ route('recon.flights.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Назад до списку
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Помилка!</h5>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Дані польоту</h3>
                </div>
                <form action="{{ route('recon.flights.update', $flight->id) }}" method="POST" enctype="multipart/form-data" id="recon-flight-form">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="recon_drone_id">Дрон</label>
                            <select name="recon_drone_id" id="recon_drone_id" class="form-control @error('recon_drone_id') is-invalid @enderror" required>
                                @foreach($drones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('recon_drone_id', $flight->recon_drone_id) == $drone['id'] ? 'selected' : '' }}>
                                        {{ $drone['name'] }} ({{ $drone['serial_number'] ?? 'без S/N' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('recon_drone_id')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="shift_type">Зміна</label>
                            <select name="shift_type" id="shift_type" class="form-control @error('shift_type') is-invalid @enderror" required>
                                @foreach(\App\Enums\ShiftTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('shift_type', $flight->shift_type->value) === $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shift_type')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mission_type">Тип місії</label>
                            <select name="mission_type" id="mission_type" class="form-control @error('mission_type') is-invalid @enderror" required>
                                @foreach(\App\Enums\ReconMissionTypesEnum::cases() as $case)
                                    <option value="{{ $case->value }}" {{ old('mission_type', $flight->mission_type->value) == $case->value ? 'selected' : '' }}>
                                        @if($case->value === 'recon') Розвідка
                                        @elseif($case->value === 'combat') Бойова (скид)
                                        @elseif($case->value === 'delivery') Доставка
                                        @else {{ $case->value }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('mission_type')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="ammunition-section" style="{{ old('mission_type', $flight->mission_type->value) === 'combat' ? '' : 'display: none;' }}">
                            <div id="ammunition-container">
                                <label>Боєприпаси (до 4-х одиниць)</label>
                                @error('ammunition')
                                    <div class="text-danger small mb-2">{{ $message }}</div>
                                @enderror

                                @php
                                    $currentAmmunition = old('ammunition', $flight->ammunition->map(fn($a) => ['id' => $a->id, 'quantity' => $a->pivot->quantity])->toArray());
                                @endphp

                                @if(!empty($currentAmmunition))
                                    @foreach($currentAmmunition as $index => $oldAmmo)
                                    <div class="row mb-2 ammunition-row">
                                        <div class="col-8">
                                            <select name="ammunition[{{ $index }}][id]" class="form-control select2 @error("ammunition.$index.id") is-invalid @enderror">
                                                <option value="">Без боєприпасу</option>
                                                @foreach($ammunition as $item)
                                                    <option value="{{ $item['id'] }}" {{ $oldAmmo['id'] == $item['id'] ? 'selected' : '' }}>
                                                        {{ $item['name'] }} (Залишок: {{ $item['quantity'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("ammunition.$index.id")
                                                <span class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-4">
                                            <input type="number" name="ammunition[{{ $index }}][quantity]" class="form-control @error("ammunition.$index.quantity") is-invalid @enderror" value="{{ $oldAmmo['quantity'] ?? 1 }}" min="1" placeholder="К-ть">
                                            @error("ammunition.$index.quantity")
                                                <span class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-info mb-3" id="add-ammunition">
                                <i class="fas fa-plus"></i> Додати боєприпас
                            </button>
                        </div>

                        <div class="form-group" id="coordinates-section">
                            <label for="coordinates">Координати <span class="text-danger">*</span></label>
                            <input type="text" name="coordinates" id="coordinates" class="form-control @error('coordinates') is-invalid @enderror" value="{{ old('coordinates', $flight->coordinates) }}">
                            @error('coordinates')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" id="target-name-section" style="display: none;">
                            <label for="target_name">Назва цілі <span class="text-danger">*</span></label>
                            <input type="text" name="target_name" id="target_name" class="form-control @error('target_name') is-invalid @enderror" value="{{ old('target_name', $flight->target_name) }}" placeholder="напр. ПНГ 1">
                            @error('target_name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="stream_status" class="custom-control-input" id="stream_status" value="1" {{ old('stream_status', $flight->stream_status) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="stream_status">Стрім (наявність)</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="flight_time">Час вильоту</label>
                            <div class="input-group">
                                <input type="datetime-local" name="flight_time" id="flight_time" class="form-control @error('flight_time') is-invalid @enderror" value="{{ old('flight_time', $flight->flight_time->format('Y-m-d\TH:i')) }}" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="setCurrentTime('flight_time')" title="Зараз">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                </div>
                            </div>
                            @error('flight_time')
                                <span class="error invalid-feedback" style="display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="landing_time">Час посадки</label>
                            <div class="input-group">
                                <input type="datetime-local" name="landing_time" id="landing_time" class="form-control @error('landing_time') is-invalid @enderror" value="{{ old('landing_time', $flight->landing_time?->format('Y-m-d\TH:i')) }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="setCurrentTime('landing_time')" title="Зараз">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                </div>
                            </div>
                            @error('landing_time')
                                <span class="error invalid-feedback" style="display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="result">Результат місії</label>
                            <select name="result" id="result" class="form-control @error('result') is-invalid @enderror" required>
                                @foreach(\App\Enums\ReconMissionResultsEnum::cases() as $case)
                                    <option value="{{ $case->value }}" {{ old('result', $flight->result->value) == $case->value ? 'selected' : '' }}>
                                        @if($case->value === 'success') Успішно
                                        @elseif($case->value === 'board_loosed') Втрата борту
                                        @elseif($case->value === 'other') Інше
                                        @else {{ $case->value }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('result')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Опис / Нотатки</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Додайте опис або важливі деталі вильоту...">{{ old('description', $flight->description) }}</textarea>
                            @error('description')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="video">Відео вильоту (опціонально, max 75MB)</label>
                            @if($flight->video_path)
                                <div class="mb-2">
                                    <small class="text-muted">Поточне відео вже завантажено. Завантаження нового замінить старе.</small>
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
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-info">Оновити політ</button>
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

        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            let ammoCount = {{ count($currentAmmunition) }};
            $('#add-ammunition').on('click', function() {
                if (ammoCount >= 4) {
                    alert('Максимум 4 боєприпаси');
                    return;
                }

                let newRow = `
                    <div class="row mb-2 ammunition-row">
                        <div class="col-8">
                            <select name="ammunition[${ammoCount}][id]" class="form-control select2">
                                <option value="">Без боєприпасу</option>
                                @foreach($ammunition as $item)
                                    <option value="{{ $item['id'] }}">
                                        {{ $item['name'] }} (Залишок: {{ $item['quantity'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <input type="number" name="ammunition[${ammoCount}][quantity]" class="form-control" value="1" min="1" placeholder="К-ть">
                        </div>
                    </div>
                `;
                $('#ammunition-container').append(newRow);
                $('.select2').last().select2({
                    theme: 'bootstrap4'
                });
                ammoCount++;
            });

            function toggleMissionFields() {
                const missionType = $('#mission_type').val();
                if (missionType === 'combat') {
                    $('#ammunition-section').slideDown();
                } else {
                    $('#ammunition-section').slideUp();
                }

                if (missionType === 'delivery') {
                    $('#coordinates-section').hide();
                    $('#target-name-section').show();
                    $('#coordinates').removeAttr('required');
                    $('#target_name').attr('required', 'required');
                } else {
                    $('#coordinates-section').show();
                    $('#target-name-section').hide();
                    $('#coordinates').attr('required', 'required');
                    $('#target_name').removeAttr('required');
                }
            }

            $('#mission_type').on('change', toggleMissionFields);
            toggleMissionFields();

            $('.custom-file-input').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            $('#recon-flight-form').on('submit', function(e) {
                let totalAmmo = 0;
                $('input[name$="[quantity]"]').each(function() {
                    let val = parseInt($(this).val());
                    if (!isNaN(val)) totalAmmo += val;
                });

                if ($('#mission_type').val() === 'combat' && totalAmmo > 4) {
                    alert('Загальна кількість боєприпасів не може перевищувати 4 за один політ');
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
