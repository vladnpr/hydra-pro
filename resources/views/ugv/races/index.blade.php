@extends('adminlte::page')

@section('title', 'Рейси НРК')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Рейси НРК ({{ $userActiveShift->position_name }})</h1>
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
            <a href="{{ route('ugv.combat_shifts.show', $userActiveShift->id) }}" class="btn btn-default">
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
                    <h3 class="card-title">Додати рейс до плану</h3>
                </div>
                <form action="{{ route('ugv.race_plans.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="combat_shift_id" value="{{ $userActiveShift->id }}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="position_name">Назва позиції / Рейси</label>
                            <input type="text" name="position_name" id="position_name" class="form-control @error('position_name') is-invalid @enderror" value="{{ old('position_name') }}" placeholder="напр. ПНГ 1" required>
                            @error('position_name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info btn-block">Додати в план</button>
                    </div>
                </form>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Зафіксувати новий рейс</h3>
                </div>
                <form action="{{ route('ugv.races.store') }}" method="POST" enctype="multipart/form-data">
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
                            <label>Ціль / Маршрут (у порядку проходження)</label>
                            <div id="route-sequence-container" class="border rounded p-2 bg-light mb-2" style="min-height: 50px;">
                                <p class="text-muted small mb-0 text-center" id="empty-route-msg">Оберіть цілі нижче, щоб сформувати маршрут</p>
                                <div id="selected-plans-list" class="list-group list-group-flush"></div>
                            </div>
                            <div class="row">
                                @foreach($plans as $plan)
                                    <div class="col-6 mb-1">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input plan-checkbox" type="checkbox"
                                                id="plan_{{ $plan['id'] }}"
                                                value="{{ $plan['id'] }}"
                                                data-name="{{ $plan['position_name'] }}"
                                                data-coords="{{ $plan['coordinates'] ? "({$plan['coordinates']})" : '' }}"
                                                {{ (is_array(old('ugv_race_plan_ids')) && in_array($plan['id'], old('ugv_race_plan_ids'))) ? 'checked' : '' }}>
                                            <label for="plan_{{ $plan['id'] }}" class="custom-control-label font-weight-normal">
                                                {{ $plan['position_name'] }} {{ $plan['coordinates'] ? "({$plan['coordinates']})" : '' }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div id="hidden-plan-inputs">
                                @if(is_array(old('ugv_race_plan_ids')))
                                    @foreach(old('ugv_race_plan_ids') as $oldId)
                                        <input type="hidden" name="ugv_race_plan_ids[]" value="{{ $oldId }}" class="hidden-plan-id-input" data-id="{{ $oldId }}">
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="ugv_drone_id">НРК</label>
                            <select name="ugv_drone_id" id="ugv_drone_id" class="form-control" required>
                                @foreach($drones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('ugv_drone_id') == $drone['id'] ? 'selected' : '' }}>{{ $drone['name'] }} ({{ $drone['serial_number'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_time">Час виїзду</label>
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
                            <label for="end_time">Час повернення</label>
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
                                <option value="combat" {{ old('mission_type') === 'combat' ? 'selected' : '' }}>бойова</option>
                                <option value="evac" {{ old('mission_type') === 'evac' ? 'selected' : '' }}>евак</option>
                            </select>
                        </div>
                        <div class="form-group" id="coordinates-section" style="{{ old('mission_type') === 'combat' ? '' : 'display: none;' }}">
                            <label for="race_coordinates">Координати</label>
                            <input type="text" name="coordinates" id="race_coordinates" class="form-control" value="{{ old('coordinates') }}" placeholder="47.123, 37.456">
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
                                <option value="not_worked" {{ old('result') === 'not_worked' ? 'selected' : '' }}>не відпрацювали</option>
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
                            <label for="video">Відео рейсу (макс. 75мб)</label>
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
                        <button type="submit" class="btn btn-primary btn-block">Додати рейс</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">План рейсів</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Рейс</th>
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
                                            <a href="{{ route('ugv.race_plans.edit', $plan['id']) }}" class="btn btn-xs btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('ugv.race_plans.destroy', $plan['id']) }}" method="POST" style="display:inline-block;">
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
                    <h3 class="card-title">Журнал рейсів</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @forelse($races as $date => $dayRaces)
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
                                        <th>НРК</th>
                                        <th>Рейс</th>
                                        <th>Відео</th>
                                        <th>Місія</th>
                                        <th>БК</th>
                                        <th>Результат</th>
                                        <th>Коментар</th>
                                        <th>Дії</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayRaces as $race)
                                        <tr>
                                            <td>
                                                <div class="text-nowrap">{{ \Carbon\Carbon::parse($race->start_time)->format('H:i') }}</div>
                                                @if($race->end_time)
                                                    <div class="text-nowrap text-muted small">{{ \Carbon\Carbon::parse($race->end_time)->format('H:i') }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($race->shift_type?->value === 'day')
                                                    <i class="fas fa-sun text-warning" title="Денна"></i>
                                                @elseif($race->shift_type?->value === 'night')
                                                    <i class="fas fa-moon text-secondary" title="Нічна"></i>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $race->drone?->name }}</td>
                                            <td>{{ $race->racePlan?->position_name ?? '-' }} {{ $race->coordinates ? "({$race->coordinates})" : '' }}</td>
                                            <td>
                                                @if($race->video_path)
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $race->id }}" title="Переглянути">
                                                            <i class="fas fa-video"></i>
                                                        </button>
                                                        <a href="{{ route('ugv.races.download', $race->id) }}" class="btn btn-xs btn-success" title="Скачати">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>

                                                    <div class="modal fade" id="videoModal{{ $race->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Відео рейсу #{{ $race->id }}</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body text-center bg-black">
                                                                    <video width="100%" controls>
                                                                        <source src="{{ Storage::url($race->video_path) }}" type="video/mp4">
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
                                                @if($race->mission_type === 'combat')
                                                    <span class="badge badge-danger"><i class="fas fa-crosshairs"></i> бойова</span>
                                                @else
                                                    <span class="badge badge-info"><i class="fas fa-truck-loading"></i> логістика</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach($race->ammunition as $ammo)
                                                    <div><i class="fas fa-bomb small"></i> {{ $ammo->name }} ({{ $ammo->pivot->quantity }})</div>
                                                @endforeach
                                                @if($race->ammunition->isEmpty()) - @endif
                                            </td>
                                            <td>
                                                @if($race->result === 'worked')
                                                    <span class="badge badge-success">відпрацювали</span>
                                                @elseif($race->result === 'not_worked')
                                                    <span class="badge badge-warning">не відпрацювали</span>
                                                @else
                                                    <span class="badge badge-danger">втрата</span>
                                                @endif
                                            </td>
                                            <td>{{ $race->comment }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('ugv.races.edit', $race->id) }}" class="btn btn-xs btn-info">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('ugv.races.destroy', $race->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Видалити цей рейс?')">
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
                                <p class="text-muted">Рейсів ще не зафіксовано</p>
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
                    url: '{{ route('ugv.races.set_shift_type') }}',
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

            // Route sequence handling
            function updateRouteSequence() {
                let selectedList = $('#selected-plans-list');
                let hiddenContainer = $('#hidden-plan-inputs');
                let checkboxes = $('.plan-checkbox');

                // Get all current hidden inputs to preserve order if possible
                let currentOrder = [];
                hiddenContainer.find('input').each(function() {
                    currentOrder.push($(this).val());
                });

                // Clear both
                selectedList.empty();
                hiddenContainer.empty();

                // Checkboxes that are checked
                let checkedIds = [];
                checkboxes.filter(':checked').each(function() {
                    checkedIds.push($(this).val());
                });

                // Sort currentOrder to only include those still checked
                let newOrder = currentOrder.filter(id => checkedIds.includes(id));

                // Add new checked IDs that aren't in newOrder yet
                checkedIds.forEach(id => {
                    if (!newOrder.includes(id)) {
                        newOrder.push(id);
                    }
                });

                if (newOrder.length === 0) {
                    $('#empty-route-msg').show();
                } else {
                    $('#empty-route-msg').hide();
                    newOrder.forEach((id, index) => {
                        let cb = $(`#plan_${id}`);
                        let name = cb.data('name');
                        let coords = cb.data('coords');

                        selectedList.append(`
                            <div class="list-group-item p-1 d-flex align-items-center" data-id="${id}">
                                <span class="badge badge-secondary mr-2">${index + 1}</span>
                                <span class="flex-grow-1">${name} ${coords}</span>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-link btn-xs text-secondary move-up" ${index === 0 ? 'disabled' : ''}><i class="fas fa-chevron-up"></i></button>
                                    <button type="button" class="btn btn-link btn-xs text-secondary move-down" ${index === newOrder.length - 1 ? 'disabled' : ''}><i class="fas fa-chevron-down"></i></button>
                                </div>
                            </div>
                        `);

                        hiddenContainer.append(`<input type="hidden" name="ugv_race_plan_ids[]" value="${id}">`);
                    });
                }
            }

            $('.plan-checkbox').on('change', updateRouteSequence);

            $(document).on('click', '.move-up', function() {
                let item = $(this).closest('.list-group-item');
                let id = item.data('id');
                let hiddenInputs = $('#hidden-plan-inputs input');
                let index = hiddenInputs.filter(`[value="${id}"]`).index();

                if (index > 0) {
                    let input = hiddenInputs.eq(index);
                    input.insertBefore(hiddenInputs.eq(index - 1));
                    updateRouteFromInputs();
                }
            });

            $(document).on('click', '.move-down', function() {
                let item = $(this).closest('.list-group-item');
                let id = item.data('id');
                let hiddenInputs = $('#hidden-plan-inputs input');
                let index = hiddenInputs.filter(`[value="${id}"]`).index();

                if (index < hiddenInputs.length - 1) {
                    let input = hiddenInputs.eq(index);
                    input.insertAfter(hiddenInputs.eq(index + 1));
                    updateRouteFromInputs();
                }
            });

            function updateRouteFromInputs() {
                let selectedList = $('#selected-plans-list');
                let hiddenInputs = $('#hidden-plan-inputs input');

                selectedList.empty();
                hiddenInputs.each(function(index) {
                    let id = $(this).val();
                    let cb = $(`#plan_${id}`);
                    let name = cb.data('name');
                    let coords = cb.data('coords');

                    selectedList.append(`
                        <div class="list-group-item p-1 d-flex align-items-center" data-id="${id}">
                            <span class="badge badge-secondary mr-2">${index + 1}</span>
                            <span class="flex-grow-1">${name} ${coords}</span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-link btn-xs text-secondary move-up" ${index === 0 ? 'disabled' : ''}><i class="fas fa-chevron-up"></i></button>
                                <button type="button" class="btn btn-link btn-xs text-secondary move-down" ${index === hiddenInputs.length - 1 ? 'disabled' : ''}><i class="fas fa-chevron-down"></i></button>
                            </div>
                        </div>
                    `);
                });
            }

            // Initial call if there are old values
            if ($('.plan-checkbox:checked').length > 0) {
                updateRouteSequence();
            }

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
