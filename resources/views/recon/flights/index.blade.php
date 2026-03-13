@extends('adminlte::page')

@section('title', 'Польоти розвідки')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Польоти розвідки (Активна зміна #{{ $userActiveShift->id }})</h1>
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
            <div>
                <span class="badge badge-success">Позиція: {{ $userActiveShift->position_name }}</span>
                <span class="badge badge-info ml-2">Початок: {{ $userActiveShift->started_at }}</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
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
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Додати новий політ</h3>
                </div>
                <form action="{{ route('recon.flights.store') }}" method="POST" enctype="multipart/form-data" id="recon-flight-form">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="recon_drone_id">Дрон</label>
                            <select name="recon_drone_id" id="recon_drone_id" class="form-control @error('recon_drone_id') is-invalid @enderror" required>
                                <option value="">Оберіть дрон</option>
                                @foreach($drones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('recon_drone_id') == $drone['id'] ? 'selected' : '' }}>
                                        {{ $drone['name'] }} ({{ $drone['serial_number'] ?? 'без S/N' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('recon_drone_id')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="ammunition-section" style="{{ old('mission_type') === 'combat' ? '' : 'display: none;' }}">
                            <div id="ammunition-container">
                                <div class="form-group ammunition-row">
                                    <label>Боєприпаси (до 4-х одиниць)</label>
                                    @error('ammunition')
                                        <div class="text-danger small mb-2">{{ $message }}</div>
                                    @enderror
                                    <div class="row mb-2">
                                        <div class="col-8">
                                            <select name="ammunition[0][id]" class="form-control select2 @error('ammunition.0.id') is-invalid @enderror">
                                                <option value="">Без боєприпасу</option>
                                                @foreach($ammunition as $item)
                                                    <option value="{{ $item['id'] }}" {{ old('ammunition.0.id') == $item['id'] ? 'selected' : '' }}>
                                                        {{ $item['name'] }} (Залишок: {{ $item['quantity'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('ammunition.0.id')
                                                <span class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-4">
                                            <input type="number" name="ammunition[0][quantity]" class="form-control @error('ammunition.0.quantity') is-invalid @enderror" value="{{ old('ammunition.0.quantity', 1) }}" min="1" placeholder="К-ть">
                                            @error('ammunition.0.quantity')
                                                <span class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-info mb-3" id="add-ammunition">
                                <i class="fas fa-plus"></i> Додати боєприпас
                            </button>
                        </div>

                        <div class="form-group">
                            <label for="mission_type">Тип місії</label>
                            <select name="mission_type" id="mission_type" class="form-control @error('mission_type') is-invalid @enderror" required>
                                @foreach(\App\Enums\ReconMissionTypesEnum::cases() as $case)
                                    <option value="{{ $case->value }}" {{ old('mission_type') == $case->value ? 'selected' : '' }}>
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

                        <div class="form-group">
                            <label for="coordinates">Координати</label>
                            <input type="text" name="coordinates" id="coordinates" class="form-control @error('coordinates') is-invalid @enderror" value="{{ old('coordinates') }}" placeholder="00.0000, 00.0000" required>
                            @error('coordinates')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="stream_status" class="custom-control-input" id="stream_status" value="1" {{ old('stream_status', '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="stream_status">Стрім (наявність)</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="flight_time">Час вильоту</label>
                            <input type="datetime-local" name="flight_time" id="flight_time" class="form-control @error('flight_time') is-invalid @enderror" value="{{ old('flight_time', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('flight_time')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="result">Результат</label>
                            <select name="result" id="result" class="form-control @error('result') is-invalid @enderror" required>
                                @foreach(\App\Enums\ReconMissionResultsEnum::cases() as $case)
                                    <option value="{{ $case->value }}" {{ old('result') == $case->value ? 'selected' : '' }}>
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
                            <small class="text-muted">Увага: при виборі "Втрата борту" дрон буде списано автоматично.</small>
                        </div>

                        <div class="form-group">
                            <label for="video">Відео польоту (макс. 75мб)</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="video" class="custom-file-input @error('video') is-invalid @enderror" id="video" accept="video/*">
                                    <label class="custom-file-label" for="video">Оберіть файл</label>
                                </div>
                            </div>
                            @error('video')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Опис / Нотатки</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Додайте опис або нотатки">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Зафіксувати політ
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Останні польоти</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Час</th>
                                    <th>Зміна</th>
                                    <th>Дрон</th>
                                    <th>Стрім</th>
                                    <th>Тип</th>
                                    <th>БК</th>
                                    <th>Координати</th>
                                    <th>Результат</th>
                                    <th>Опис</th>
                                    <th>Відео</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($flights as $flight)
                                    <tr>
                                        <td>{{ $flight->flight_time->format('H:i d.m') }}</td>
                                        <td>
                                            @if($flight->shift_type?->value === 'day')
                                                <i class="fas fa-sun text-warning" title="Денна"></i>
                                            @elseif($flight->shift_type?->value === 'night')
                                                <i class="fas fa-moon text-secondary" title="Нічна"></i>
                                            @elseif($flight->shift_type?->value === 'both')
                                                <i class="fas fa-sun text-warning" title="Денна"></i> / <i class="fas fa-moon text-secondary" title="Нічна"></i>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $flight->drone->name }}</td>
                                        <td class="text-center">
                                            @if($flight->stream_status)
                                                <i class="fas fa-check-circle text-success" title="Є стрім"></i>
                                            @else
                                                <i class="fas fa-times-circle text-danger" title="Без стріму"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $flight->mission_type->value === 'recon' ? 'Розвідка' : 'Скид' }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach($flight->ammunition as $ammo)
                                                <div>{{ $ammo->name }} ({{ $ammo->pivot->quantity }})</div>
                                            @endforeach
                                            @if($flight->ammunition->isNotEmpty())
                                                <div class="mt-1 border-top pt-1">
                                                    <strong>Всього: {{ $flight->total_ammunition_quantity }}</strong>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $flight->coordinates }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($flight->result->value) {
                                                    'success' => 'success',
                                                    'board_loosed' => 'danger',
                                                    default => 'secondary'
                                                };
                                                $resultLabel = match($flight->result->value) {
                                                    'success' => 'Успішно',
                                                    'board_loosed' => 'Втрата',
                                                    'other' => 'Інше',
                                                    default => $flight->result->value
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ $resultLabel }}</span>
                                        </td>
                                        <td>
                                            @if($flight->description)
                                                <span title="{{ $flight->description }}">{{ Str::limit($flight->description, 30) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($flight->video_path)
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $flight->id }}" title="Переглянути">
                                                        <i class="fas fa-video"></i>
                                                    </button>
                                                    <a href="{{ route('recon.flights.download', $flight->id) }}" class="btn btn-xs btn-success" title="Скачати">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>

                                                <div class="modal fade" id="videoModal{{ $flight->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Відео польоту ({{ $flight->flight_time->format('H:i') }})</h5>
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
                                            <div class="btn-group">
                                                <a href="{{ route('recon.flights.edit', $flight->id) }}" class="btn btn-xs btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('recon.flights.destroy', $flight->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Ви впевнені?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center p-4">Польотів ще не зафіксовано</td>
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
        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            let ammoCount = {{ count(old('ammunition', [0])) }};
            $('#add-ammunition').on('click', function() {
                if (ammoCount >= 4) {
                    alert('Максимум 4 боєприпаси');
                    return;
                }

                let newRow = `
                    <div class="row mb-2">
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

            @if(old('ammunition'))
                @foreach(old('ammunition') as $index => $oldAmmo)
                    @if($index > 0)
                        let row{{ $index }} = `
                            <div class="row mb-2">
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
                                    <input type="number" name="ammunition[{{ $index }}][quantity]" class="form-control @error("ammunition.$index.quantity") is-invalid @enderror" value="{{ $oldAmmo['quantity'] }}" min="1" placeholder="К-ть">
                                    @error("ammunition.$index.quantity")
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        `;
                        $('#ammunition-container').append(row{{ $index }});
                        $('.select2').last().select2({
                            theme: 'bootstrap4'
                        });
                    @endif
                @endforeach
            @endif

            $('#mission_type').on('change', function() {
                if ($(this).val() === 'combat') {
                    $('#ammunition-section').slideDown();
                } else {
                    $('#ammunition-section').slideUp();
                    // Optional: clear ammunition if hidden, but maybe user wants to keep it if they accidentally switched
                }
            });

            $('.custom-file-input').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            $('#recon-flight-form').on('submit', function(e) {
                let totalAmmo = 0;
                $('input[name$="[quantity]"]').each(function() {
                    let val = parseInt($(this).val());
                    let ammoId = $(this).closest('.row').find('select[name$="[id]"]').val();
                    if (ammoId && !isNaN(val)) {
                        totalAmmo += val;
                    }
                });

                if (totalAmmo > 4) {
                    e.preventDefault();
                    alert('Загальна кількість боєприпасів за один політ не може перевищувати 4. Зараз: ' + totalAmmo);
                }
            });

            $('.modal').on('hidden.bs.modal', function () {
                let video = $(this).find('video')[0];
                if (video) video.pause();
            });

            $('input[name="global_shift_type"]').on('change', function() {
                let shiftType = $(this).val();
                $.ajax({
                    url: '{{ route('recon.flights.set_shift_type') }}',
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
        });
    </script>
@endsection

@section('css')
    <style>
        .bg-black { background-color: #000; }
    </style>
@endsection
