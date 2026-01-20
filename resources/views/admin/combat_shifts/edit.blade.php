@extends('adminlte::page')

@section('title', 'Редагувати чергування')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Редагувати чергування #{{ $shift->id }}</h1>
        <div>
            <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до деталей
            </a>
            <button type="submit" form="edit-shift-form" class="btn btn-primary ml-2">
                <i class="fas fa-save"></i> Оновити
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('combat_shifts.update', $shift->id) }}" method="POST" id="edit-shift-form">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Основна інформація</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="user_ids">Користувачі (Екіпаж системи)</label>
                                    @php
                                        $currentUserIds = collect($shift->users)->pluck('id')->toArray();
                                    @endphp
                                    <select name="user_ids[]" id="user_ids" class="form-control select2 @error('user_ids') is-invalid @enderror" multiple required>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ (is_array(old('user_ids')) && in_array($user->id, old('user_ids'))) || (!old('user_ids') && in_array($user->id, $currentUserIds)) ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_ids')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="position_id">Позиція</label>
                                    <select name="position_id" id="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id', $shift->position_id) == $position->id ? 'selected' : '' }}>
                                                {{ $position->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position_id')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Статус</label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="opened" {{ old('status', $shift->status) == 'opened' ? 'selected' : '' }}>Відкрито</option>
                                        <option value="closed" {{ old('status', $shift->status) == 'closed' ? 'selected' : '' }}>Закрито</option>
                                    </select>
                                    @error('status')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="started_at">Час початку</label>
                                    <input type="datetime-local" name="started_at" id="started_at" class="form-control @error('started_at') is-invalid @enderror" value="{{ old('started_at', date('Y-m-d\TH:i', strtotime($shift->started_at))) }}" required>
                                    @error('started_at')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="ended_at">Час завершення</label>
                                    <input type="datetime-local" name="ended_at" id="ended_at" class="form-control @error('ended_at') is-invalid @enderror" value="{{ old('ended_at', $shift->ended_at ? date('Y-m-d\TH:i', strtotime($shift->ended_at)) : '') }}">
                                    @error('ended_at')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Екіпаж</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" id="add-crew-member">
                                        <i class="fas fa-plus"></i> Додати
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="crew-container">
                                    @php
                                        $crewRaw = old('crew', $shift->crew);
                                        $crew = is_array($crewRaw) ? $crewRaw : $crewRaw->toArray();
                                    @endphp
                                    @foreach($crew as $index => $member)
                                        @php
                                            $member = (array)$member;
                                            $callsign = $member['callsign'] ?? '';
                                            $role = $member['role'] ?? '';
                                        @endphp
                                        <div class="crew-member row mb-2">
                                            <div class="col-md-5">
                                                <input type="text" name="crew[{{ $index }}][callsign]" class="form-control form-control-sm" placeholder="Позивний" value="{{ $callsign }}" required>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="crew[{{ $index }}][role]" class="form-control form-control-sm" placeholder="Посада" value="{{ $role }}" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm remove-crew-member">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Вильоти</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" id="add-flight">
                                        <i class="fas fa-plus"></i> Додати виліт
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="flights-container">
                                    @php
                                        $flightsRaw = old('flights', $shift->flights);
                                        // If it's a grouped array from CombatShiftDTO (during show/edit initially), we need to flatten it
                                        // If it's from old(), it's already a flat array.
                                        // But wait, in edit() controller we pass $shift = $service->getShiftById($id) which is a CombatShiftDTO.
                                        // CombatShiftDTO->flights is grouped by date.
                                        $flights = [];
                                        if (is_array($flightsRaw)) {
                                            if (isset($flightsRaw[0]) && is_array($flightsRaw[0])) {
                                                // It's likely from old() or already flattened
                                                $flights = $flightsRaw;
                                            } else {
                                                // It's grouped by date (from CombatShiftDTO)
                                                foreach ($flightsRaw as $date => $dayFlights) {
                                                    foreach ($dayFlights as $f) {
                                                        $flights[] = $f;
                                                    }
                                                }
                                            }
                                        } else {
                                            // It's a Collection (shouldn't happen if using CombatShiftDTO)
                                            $flights = $flightsRaw;
                                        }
                                    @endphp
                                    @foreach($flights as $index => $flight)
                                        @php
                                            $flight = (array)$flight;
                                            $droneId = $flight['drone_id'] ?? null;
                                            $ammunitionId = $flight['ammunition_id'] ?? null;
                                            $coordinates = $flight['coordinates'] ?? '';
                                            $flightTime = $flight['flight_time'] ?? '';

                                            if ($flightTime instanceof \Carbon\Carbon) {
                                                $flightTime = $flightTime->format('Y-m-d\TH:i');
                                            } elseif (is_string($flightTime) && !empty($flightTime)) {
                                                $flightTime = date('Y-m-d\TH:i', strtotime($flightTime));
                                            }

                                            $result = $flight['result'] ?? '';
                                            $note = $flight['note'] ?? '';
                                        @endphp
                                        <div class="flight-item border p-2 mb-3 bg-light">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Дрон</label>
                                                        <select name="flights[{{ $index }}][drone_id]" class="form-control form-control-sm" required>
                                                            <option value="">Оберіть дрон</option>
                                                            @foreach($drones as $drone)
                                                                <option value="{{ $drone->id }}" {{ $droneId == $drone->id ? 'selected' : '' }}>{{ $drone->name }} ({{ $drone->model }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Боєприпас</label>
                                                        <select name="flights[{{ $index }}][ammunition_id]" class="form-control form-control-sm" required>
                                                            <option value="">Оберіть БК</option>
                                                            @foreach($ammunition as $item)
                                                                <option value="{{ $item->id }}" {{ $ammunitionId == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Координати</label>
                                                        <input type="text" name="flights[{{ $index }}][coordinates]" class="form-control form-control-sm" placeholder="Координати" value="{{ $coordinates }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Час вильоту</label>
                                                        <input type="datetime-local" name="flights[{{ $index }}][flight_time]" class="form-control form-control-sm" value="{{ $flightTime }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Результат</label>
                                                        <select name="flights[{{ $index }}][result]" class="form-control form-control-sm" required>
                                                            <option value="влучання" {{ $result == 'влучання' ? 'selected' : '' }}>Влучання</option>
                                                            <option value="удар в районі цілі" {{ $result == 'удар в районі цілі' ? 'selected' : '' }}>Удар в районі цілі</option>
                                                            <option value="недольот" {{ $result == 'недольот' ? 'selected' : '' }}>Недольот</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Детонація</label>
                                                        <select name="flights[{{ $index }}][detonation]" class="form-control form-control-sm" required>
                                                            <option value="так" {{ ($flight['detonation'] ?? '') == 'так' ? 'selected' : '' }}>Так</option>
                                                            <option value="ні" {{ ($flight['detonation'] ?? 'ні') == 'ні' ? 'selected' : '' }}>Ні</option>
                                                            <option value="інше" {{ ($flight['detonation'] ?? '') == 'інше' ? 'selected' : '' }}>Інше</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Стрім</label>
                                                        <input type="text" name="flights[{{ $index }}][stream]" class="form-control form-control-sm" placeholder="Стрім" value="{{ $flight['stream'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mb-2">
                                                        <label class="small">Примітка (опц.)</label>
                                                        <input type="text" name="flights[{{ $index }}][note]" class="form-control form-control-sm" placeholder="Примітка" value="{{ $note }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-danger btn-xs remove-flight mt-1"><i class="fas fa-trash"></i> Видалити виліт</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Наявність ресурсів</h3>
                            </div>
                            <div class="card-body">
                                <h5>Дрони</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Назва</th>
                                                <th style="width: 100px;">Кількість</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($drones as $drone)
                                                <tr>
                                                    <td>{{ $drone->name }} ({{ $drone->model }})</td>
                                                    <td>
                                                        <input type="number" name="drones[{{ $drone->id }}]" class="form-control form-control-sm" value="{{ old("drones.$drone->id", $currentDrones[$drone->id] ?? 0) }}" min="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <h5 class="mt-4">Боєприпаси</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Назва</th>
                                                <th style="width: 100px;">Кількість</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ammunition as $item)
                                                <tr>
                                                    <td>{{ $item->name }}</td>
                                                    <td>
                                                        <input type="number" name="ammunition[{{ $item->id }}]" class="form-control form-control-sm" value="{{ old("ammunition.$item->id", $currentAmmunition[$item->id] ?? 0) }}" min="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Оновити
                                </button>
                                <a href="{{ route('combat_shifts.show', $shift->id) }}" class="btn btn-default float-right">Скасувати</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Оберіть користувачів'
            });

            let crewIndex = {{ is_array($crew) ? count($crew) : 0 }};

            $('#add-crew-member').click(function() {
                const html = `
                    <div class="crew-member row mb-2">
                        <div class="col-md-5">
                            <input type="text" name="crew[${crewIndex}][callsign]" class="form-control form-control-sm" placeholder="Позивний" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="crew[${crewIndex}][role]" class="form-control form-control-sm" placeholder="Посада" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-crew-member">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#crew-container').append(html);
                crewIndex++;
            });

            $(document).on('click', '.remove-crew-member', function() {
                if ($('.crew-member').length > 1) {
                    $(this).closest('.crew-member').remove();
                } else {
                    $(this).closest('.crew-member').find('input').val('');
                }
            });

            let flightIndex = {{ is_array($flights) ? count($flights) : 0 }};
            const droneOptions = `@foreach($drones as $drone)<option value="{{ $drone->id }}">{{ $drone->name }} ({{ $drone->model }})</option>@endforeach`;
            const ammunitionOptions = `@foreach($ammunition as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach`;

            $('#add-flight').click(function() {
                const html = `
                    <div class="flight-item border p-2 mb-3 bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small">Дрон</label>
                                    <select name="flights[${flightIndex}][drone_id]" class="form-control form-control-sm" required>
                                        <option value="">Оберіть дрон</option>
                                        ${droneOptions}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small">Боєприпас</label>
                                    <select name="flights[${flightIndex}][ammunition_id]" class="form-control form-control-sm" required>
                                        <option value="">Оберіть БК</option>
                                        ${ammunitionOptions}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small">Координати</label>
                                    <input type="text" name="flights[${flightIndex}][coordinates]" class="form-control form-control-sm" placeholder="Координати" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small">Час вильоту</label>
                                    <input type="datetime-local" name="flights[${flightIndex}][flight_time]" class="form-control form-control-sm" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small">Результат</label>
                                    <select name="flights[${flightIndex}][result]" class="form-control form-control-sm" required>
                                        <option value="влучання">Влучання</option>
                                        <option value="удар в районі цілі">Удар в районі цілі</option>
                                        <option value="недольот">Недольот</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small">Детонація</label>
                                    <select name="flights[${flightIndex}][detonation]" class="form-control form-control-sm" required>
                                        <option value="так">Так</option>
                                        <option value="ні" selected>Ні</option>
                                        <option value="інше">Інше</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small">Стрім</label>
                                    <input type="text" name="flights[${flightIndex}][stream]" class="form-control form-control-sm" placeholder="Стрім">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small">Примітка (опц.)</label>
                                    <input type="text" name="flights[${flightIndex}][note]" class="form-control form-control-sm" placeholder="Примітка">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-xs remove-flight mt-1"><i class="fas fa-trash"></i> Видалити виліт</button>
                    </div>
                `;
                $('#flights-container').append(html);
                flightIndex++;
            });

            $(document).on('click', '.remove-flight', function() {
                $(this).closest('.flight-item').remove();
            });
        });
    </script>
@endsection
