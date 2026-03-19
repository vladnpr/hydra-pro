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
                <form action="{{ route('vampire.flights.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="combat_shift_id" value="{{ $userActiveShift->id }}">
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
                                    <option value="{{ $plan['id'] }}" {{ old('vampire_flight_plan_id') == $plan['id'] ? 'selected' : '' }}>{{ $plan['position_name'] }} {{ $plan['coordinates'] ? "({$plan['coordinates']})" : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vampire_drone_id">Дрон</label>
                            <select name="vampire_drone_id" id="vampire_drone_id" class="form-control" required>
                                @foreach($drones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('vampire_drone_id') == $drone['id'] ? 'selected' : '' }}>{{ $drone['name'] }} ({{ $drone['serial_number'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_time">Час зльоту</label>
                            <div class="input-group">
                                <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}" required>
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
                                <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', now()->format('Y-m-d\TH:i')) }}">
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
                                <option value="logistics" {{ old('mission_type') === 'logistics' ? 'selected' : '' }}>логістика</option>
                                <option value="combat" {{ old('mission_type') === 'combat' ? 'selected' : '' }}>бойова (мінування, бімба)</option>
                            </select>
                        </div>
                        <div class="form-group" id="coordinates-section" style="{{ old('mission_type') === 'combat' ? '' : 'display: none;' }}">
                            <label for="flight_coordinates">Координати</label>
                            <input type="text" name="coordinates" id="flight_coordinates" class="form-control" value="{{ old('coordinates') }}" placeholder="47.123, 37.456">
                        </div>
                        <div id="ammunition-section" style="{{ old('mission_type') === 'combat' ? '' : 'display: none;' }}">
                            <div id="ammunition-container">
                                @php
                                    $oldAmmo = old('ammunition', []);
                                    if (empty($oldAmmo)) {
                                        $oldAmmo = [['id' => '', 'quantity' => 1]];
                                    }
                                @endphp
                                @foreach($oldAmmo as $index => $currentAmmo)
                                    <div class="form-group ammunition-row row mb-2">
                                        <div class="col-8">
                                            <select name="ammunition[{{ $index }}][id]" class="form-control select2">
                                                <option value="">-- Оберіть БК --</option>
                                                @foreach($userActiveShift->ammunition as $ammo)
                                                    <option value="{{ $ammo['id'] }}" {{ ($currentAmmo['id'] ?? '') == $ammo['id'] ? 'selected' : '' }}>{{ $ammo['name'] }} (Залишок: {{ $ammo['quantity'] }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <input type="number" name="ammunition[{{ $index }}][quantity]" class="form-control" value="{{ $currentAmmo['quantity'] ?? 1 }}" min="1" placeholder="К-ть">
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
                                <option value="worked" {{ old('result') === 'worked' ? 'selected' : '' }}>відпрацювали</option>
                                <option value="loss" {{ old('result') === 'loss' ? 'selected' : '' }}>втрата борту</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="stream_status" name="stream_status" value="1" {{ old('stream_status', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="stream_status">Стрім</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Коментар</label>
                            <textarea name="comment" id="comment" class="form-control" rows="2" placeholder="450, збили, подавлення, інше">{{ old('comment') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="video">Відео вильоту (макс. 75мб)</label>
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
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $plan['position_name'] }}</td>
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
                                    <td colspan="3" class="text-center">План порожній</td>
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
                        @forelse($flights as $date => $dayFlights)
                            <div class="p-2 bg-light border-bottom">
                                <h6 class="mb-0 font-weight-bold">
                                    <i class="fas fa-calendar-day mr-1"></i>
                                    {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                                </h6>
                            </div>
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Час</th>
                                        <th>Зміна</th>
                                        <th>Дрон</th>
                                        <th>Ціль</th>
                                        <th>Відео</th>
                                        <th>Місія</th>
                                        <th>БК</th>
                                        <th>Результат</th>
                                        <th>Коментар</th>
                                        <th>Дії</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayFlights as $flight)
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
                                            <td>{{ $flight->flightPlan?->position_name ?? '-' }} {{ $flight->coordinates ? "({$flight->coordinates})" : '' }}</td>
                                            <td>
                                                @if($flight->video_path)
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $flight->id }}" title="Переглянути">
                                                            <i class="fas fa-video"></i>
                                                        </button>
                                                        <a href="{{ route('vampire.flights.download', $flight->id) }}" class="btn btn-xs btn-success" title="Скачати">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>

                                                    <div class="modal fade" id="videoModal{{ $flight->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Відео польоту #{{ $flight->id }}</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body text-center bg-black">
                                                                    <video width="100%" controls>
                                                                        <source src="{{ Storage::url($flight->video_path) }}" type="video/mp4">
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
                                                @if($flight->mission_type === 'combat')
                                                    <span class="badge badge-danger"><i class="fas fa-crosshairs"></i> бойова</span>
                                                @else
                                                    <span class="badge badge-info"><i class="fas fa-truck-loading"></i> логістика</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach($flight->ammunition as $ammo)
                                                    <div><i class="fas fa-bomb small"></i> {{ $ammo->name }} ({{ $ammo->pivot->quantity }})</div>
                                                @endforeach
                                                @if($flight->ammunition->isEmpty()) - @endif
                                            </td>
                                            <td>
                                                @if($flight->result === 'worked')
                                                    <span class="badge badge-success">відпрацювали</span>
                                                @else
                                                    <span class="badge badge-danger">втрата</span>
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
                                    @endforeach
                                </tbody>
                            </table>
                        @empty
                            <div class="p-4 text-center">
                                <p class="text-muted">Вильотів ще не зафіксовано</p>
                            </div>
                        @endforelse
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
            toggleCombatFields();

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

            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            $('.modal').on('hidden.bs.modal', function () {
                let video = $(this).find('video')[0];
                if (video) video.pause();
            });
        });
    </script>
@endsection

@section('css')
    <style>
        .bg-black { background-color: #000; }
    </style>
@endsection
