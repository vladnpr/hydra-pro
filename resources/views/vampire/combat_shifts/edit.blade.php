@extends('adminlte::page')

@section('title', 'Редагувати чергування Vampire "' . $shift->position_name . '"')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Редагувати чергування Vampire "{{ $shift->position_name }}"</h1>
        <div>
            <a href="{{ route('vampire.combat_shifts.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до списку
            </a>
            <button type="submit" form="edit-shift-form" class="btn btn-primary ml-2">
                <i class="fas fa-save"></i> Оновити
            </button>
        </div>
    </div>
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Виправте помилки!</h5>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Успіх!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Помилка!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('vampire.combat_shifts.update', $shift->id) }}" method="POST" id="edit-shift-form">
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
                                    <select name="user_ids[]" id="user_ids" class="form-control select2 @error('user_ids') is-invalid @enderror" multiple required>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ (is_array(old('user_ids', collect($shift->users)->pluck('id')->toArray())) && in_array($user->id, old('user_ids', collect($shift->users)->pluck('id')->toArray()))) ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_ids')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="position_id">Позиція (Vampire)</label>
                                    <select name="position_id" id="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                                        <option value="">Оберіть позицію</option>
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
                                    <input type="datetime-local" name="started_at" id="started_at" class="form-control @error('started_at') is-invalid @enderror" value="{{ old('started_at', \Carbon\Carbon::parse($shift->started_at)->format('Y-m-d\TH:i')) }}" required>
                                    @error('started_at')
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
                                    @php $crewData = old('crew', $shift->crew); @endphp
                                    @foreach($crewData as $index => $member)
                                        <div class="crew-member row mb-2">
                                            <div class="col-md-4">
                                                <input type="text" name="crew[{{ $index }}][callsign]" class="form-control form-control-sm" placeholder="Позивний" value="{{ is_array($member) ? $member['callsign'] : $member['callsign'] }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="crew[{{ $index }}][role]" class="form-control form-control-sm" placeholder="Посада" value="{{ is_array($member) ? $member['role'] : $member['role'] }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="crew[{{ $index }}][shift_type]" class="form-control form-control-sm" required>
                                                    <option value="day" {{ (is_array($member) ? ($member['shift_type'] ?? 'day') : ($member['shift_type'] ?? 'day')) === 'day' ? 'selected' : '' }}>Денна</option>
                                                    <option value="night" {{ (is_array($member) ? ($member['shift_type'] ?? 'day') : ($member['shift_type'] ?? 'day')) === 'night' ? 'selected' : '' }}>Нічна</option>
                                                    <option value="both" {{ (is_array($member) ? ($member['shift_type'] ?? 'day') : ($member['shift_type'] ?? 'day')) === 'both' ? 'selected' : '' }}>Обидві</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger btn-sm remove-crew-member">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Майно на зміну</h3>
                            </div>
                            <div class="card-body">
                                <h5>Боєприпаси (Vampire)</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Назва</th>
                                                <th style="width: 100px">К-сть</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ammunition as $item)
                                                <tr>
                                                    <td>{{ $item->name }}</td>
                                                    <td>
                                                        <input type="number" name="ammunition[{{ $item->id }}]" class="form-control form-control-sm" value="{{ old('ammunition.' . $item->id, $currentAmmunition[$item->id] ?? 0) }}" min="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <h5 class="mt-4">Наявні дрони на позиції</h5>
                                <div id="existing-drones-container" class="mb-3">
                                    <p class="text-muted small">Оберіть позицію, щоб побачити дрони</p>
                                </div>

                                <h5 class="mt-4">Додати нові дрони на позицію</h5>
                                <div id="new-drones-container">
                                    <!-- Нові дрони будуть додаватися сюди -->
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-new-drone">
                                    <i class="fas fa-plus"></i> Додати дрон
                                </button>
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
                theme: 'bootstrap4'
            });

            let crewIndex = {{ count($crewData) }};
            $('#add-crew-member').click(function() {
                let html = `
                    <div class="crew-member row mb-2">
                        <div class="col-md-4">
                            <input type="text" name="crew[${crewIndex}][callsign]" class="form-control form-control-sm" placeholder="Позивний" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="crew[${crewIndex}][role]" class="form-control form-control-sm" placeholder="Посада" required>
                        </div>
                        <div class="col-md-3">
                            <select name="crew[${crewIndex}][shift_type]" class="form-control form-control-sm" required>
                                <option value="day">Денна</option>
                                <option value="night">Нічна</option>
                                <option value="both">Обидві</option>
                            </select>
                        </div>
                        <div class="col-md-1">
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
                $(this).closest('.crew-member').remove();
            });

            let droneIndex = {{ old('new_drones') ? count(old('new_drones')) : 0 }};
            $('#add-new-drone').click(function() {
                let html = `
                    <div class="new-drone-item border p-2 mb-2">
                        <div class="form-group mb-1">
                            <label class="small">Назва дрона</label>
                            <input type="text" name="new_drones[${droneIndex}][name]" class="form-control form-control-sm" placeholder="Наприклад: Vampire 1" required>
                        </div>
                        <div class="form-group mb-1">
                            <label class="small">Серійний номер</label>
                            <input type="text" name="new_drones[${droneIndex}][serial_number]" class="form-control form-control-sm" placeholder="Серійний номер">
                        </div>
                        <div class="form-group mb-1">
                            <label class="small">Статус</label>
                            <select name="new_drones[${droneIndex}][status]" class="form-control form-control-sm drone-status-select" required>
                                <option value="active">Активний</option>
                                <option value="repair">В ремонті</option>
                                <option value="non_operational">Не боєготовий</option>
                                <option value="lost">Втрачений</option>
                            </select>
                        </div>
                        <div class="form-group mb-1 lost-at-container" style="display: none;">
                            <label class="small">Час втрати</label>
                            <input type="datetime-local" name="new_drones[${droneIndex}][lost_at]" class="form-control form-control-sm">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small">Тип зміни</label>
                            <select name="new_drones[${droneIndex}][shift_type]" class="form-control form-control-sm" required>
                                <option value="day">Денна</option>
                                <option value="night">Нічна</option>
                                <option value="both">Обидві</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-danger btn-xs remove-new-drone">Видалити</button>
                    </div>
                `;
                $('#new-drones-container').append(html);
                droneIndex++;
            });

            $(document).on('click', '.remove-new-drone', function() {
                $(this).closest('.new-drone-item').remove();
            });

            $(document).on('change', '.drone-status-select', function() {
                let status = $(this).val();
                let container = $(this).closest('.form-group').next('.lost-at-container');
                if (status === 'lost') {
                    container.show();
                    if (!container.find('input').val()) {
                        let now = new Date();
                        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                        container.find('input').val(now.toISOString().slice(0, 16));
                    }
                } else {
                    container.hide();
                }
            });

            $('#position_id').change(function() {
                let positionId = $(this).val();
                if (!positionId) {
                    $('#existing-drones-container').html('<p class="text-muted small">Оберіть позицію, щоб побачити дрони</p>');
                    return;
                }

                $('#existing-drones-container').html('<p class="text-muted small"><i class="fas fa-spinner fa-spin"></i> Завантаження...</p>');

                $.get(`/admin/vampire/drones/by-position/${positionId}`, function(drones) {
                    if (drones.length === 0) {
                        $('#existing-drones-container').html('<p class="text-muted small">На цій позиції немає зареєстрованих дронів</p>');
                        return;
                    }

                    let html = '<div class="list-group list-group-sm">';
                    drones.forEach(function(drone) {
                        let statusText = drone.status;
                        if (drone.status === 'active') statusText = 'Активний';
                        else if (drone.status === 'repair') statusText = 'В ремонті';
                        else if (drone.status === 'non_operational') statusText = 'Не боєготовий';
                        else if (drone.status === 'lost') statusText = 'Втрачений';

                        let shiftIcon = '';
                        if (drone.shift_type === 'day') shiftIcon = '<i class="fas fa-sun text-warning" title="Денний"></i>';
                        else if (drone.shift_type === 'night') shiftIcon = '<i class="fas fa-moon text-secondary" title="Нічний"></i>';
                        else if (drone.shift_type === 'both') shiftIcon = '<i class="fas fa-sun text-warning"></i> / <i class="fas fa-moon text-secondary"></i>';

                        html += `
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h6 class="mb-0">${drone.name}</h6>
                                        <small class="text-muted">${drone.serial_number || 'S/N відсутній'}</small>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="hidden" name="existing_drones[${drone.id}][id]" value="${drone.id}">
                                        <div class="form-group mb-1">
                                            <select name="existing_drones[${drone.id}][status]" class="form-control form-control-sm drone-status-select">
                                                <option value="active" ${drone.status === 'active' ? 'selected' : ''}>Активний</option>
                                                <option value="repair" ${drone.status === 'repair' ? 'selected' : ''}>В ремонті</option>
                                                <option value="non_operational" ${drone.status === 'non_operational' ? 'selected' : ''}>Не боєготовий</option>
                                                <option value="lost" ${drone.status === 'lost' ? 'selected' : ''}>Втрачений</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-1 lost-at-container" style="${drone.status === 'lost' ? '' : 'display: none;'}">
                                            <input type="datetime-local" name="existing_drones[${drone.id}][lost_at]" class="form-control form-control-sm" value="${drone.lost_at ? drone.lost_at.substring(0, 16) : ''}">
                                        </div>
                                        <div class="form-group mb-0">
                                            <select name="existing_drones[${drone.id}][shift_type]" class="form-control form-control-sm">
                                                <option value="day" ${drone.shift_type === 'day' ? 'selected' : ''}>Денна</option>
                                                <option value="night" ${drone.shift_type === 'night' ? 'selected' : ''}>Нічна</option>
                                                <option value="both" ${drone.shift_type === 'both' ? 'selected' : ''}>Обидві</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    $('#existing-drones-container').html(html);
                }).fail(function() {
                    $('#existing-drones-container').html('<p class="text-danger small">Помилка завантаження дронів</p>');
                });
            });

            // Trigger change if position is already selected (e.g., after validation error or on edit page load)
            if ($('#position_id').val()) {
                $('#position_id').trigger('change');
            }
        });
    </script>
@endsection
