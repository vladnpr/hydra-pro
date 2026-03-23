@extends('adminlte::page')

@section('title', 'Вильоти ППО')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Вильоти ППО ({{ $userActiveShift->position_name }})</h1>
        <div class="d-flex align-items-center">
            <a href="{{ route('air-defence.combat_shifts.show', $userActiveShift->id) }}" class="btn btn-default">
                <i class="fas fa-eye"></i> Деталі чергування
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Зафіксувати новий виліт</h3>
                </div>
                <form action="{{ route('air-defence.flights.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="position_id" value="{{ $userActiveShift->position_id }}">
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
                            <label for="air_defence_drone_id">Дрон</label>
                            <select name="air_defence_drone_id" id="air_defence_drone_id" class="form-control" required>
                                <option value="">-- Оберіть дрон --</option>
                                @foreach($userActiveShift->airDefenceDrones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('air_defence_drone_id') == $drone['id'] ? 'selected' : '' }}>
                                        {{ $drone['name'] }} (Залишок: {{ $drone['quantity'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="air_defence_ammunition_id">БК</label>
                            <select name="air_defence_ammunition_id" id="air_defence_ammunition_id" class="form-control" required>
                                <option value="">-- Оберіть БК --</option>
                                @foreach($userActiveShift->airDefenceAmmunition as $ammo)
                                    <option value="{{ $ammo['id'] }}" {{ old('air_defence_ammunition_id') == $ammo['id'] ? 'selected' : '' }}>
                                        {{ $ammo['name'] }} (Залишок: {{ $ammo['quantity'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="coordinates">Координати (необов'язково)</label>
                            <input type="text" name="coordinates" id="coordinates" class="form-control" value="{{ old('coordinates') }}" placeholder="37U CP 63595 53223">
                        </div>

                        <div class="form-group">
                            <label for="start_time">Час початку</label>
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
                            <label for="end_time">Час кінця</label>
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
                            <label for="result">Результат</label>
                            <select name="result" id="result" class="form-control" required onchange="handleResultChange()">
                                <option value="влучання" {{ old('result', 'влучання') == 'влучання' ? 'selected' : '' }}>Влучання</option>
                                <option value="в районі цілі" {{ old('result') == 'в районі цілі' ? 'selected' : '' }}>В районі цілі</option>
                                <option value="втрата борта" {{ old('result') == 'втрата борта' ? 'selected' : '' }}>Втрата борта</option>
                                <option value="борт повернувся" {{ old('result') == 'борт повернувся' ? 'selected' : '' }}>Борт повернувся</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="detonation">Детонація</label>
                            <select name="detonation" id="detonation" class="form-control" required>
                                <option value="1" {{ old('detonation', '1') == '1' ? 'selected' : '' }}>Так</option>
                                <option value="0" {{ old('detonation') == '0' ? 'selected' : '' }}>Ні</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="stream_switch" name="stream_switch" value="1" {{ old('stream_switch', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="stream_switch">Стрім</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comment">Коментар</label>
                            <textarea name="comment" id="comment" class="form-control" rows="2" placeholder="Втрачали управління, відпрацювали по бліндажу">{{ old('comment') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="video">Відео вильоту (макс. 75мб)</label>
                            <div class="custom-file">
                                <input type="file" name="video" class="custom-file-input" id="video" accept="video/*">
                                <label class="custom-file-label" for="video">Оберіть файл</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">Додати виліт</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            @forelse($flights as $date => $dayFlights)
                <div class="card card-outline card-secondary mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-info">{{ count($dayFlights) }} вильотів</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Час</th>
                                        <th>Дрон / БК</th>
                                        <th>Координати</th>
                                        <th>Результат</th>
                                        <th>Детонація</th>
                                        <th>Відео</th>
                                        <th>Дії</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayFlights as $flight)
                                        <tr>
                                            <td class="text-nowrap">
                                                {{ $flight->start_time?->format('H:i') }} - {{ $flight->end_time?->format('H:i') }}
                                            </td>
                                            <td>
                                                <strong>{{ $flight->drone?->name }}</strong><br>
                                                <small class="text-muted">{{ $flight->ammunition?->name }}</small>
                                            </td>
                                            <td><small>{{ $flight->coordinates }}</small></td>
                                            <td>{{ $flight->result }}</td>
                                            <td>
                                                @if($flight->detonation)
                                                    <span class="badge badge-success">Так</span>
                                                @else
                                                    <span class="badge badge-danger">Ні</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($flight->video_path)
                                                    <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $flight->id }}">
                                                        <i class="fas fa-video"></i>
                                                    </button>

                                                    <div class="modal fade" id="videoModal{{ $flight->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Відео вильоту {{ $flight->start_time?->format('H:i') }}</h5>
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
                                                    <a href="{{ route('air-defence.flights.edit', $flight->id) }}" class="btn btn-xs btn-default">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('air-defence.flights.destroy', $flight->id) }}" method="POST" onsubmit="return confirm('Ви впевнені?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @if($flight->comment || $flight->stream)
                                            <tr class="expandable-body">
                                                <td colspan="7">
                                                    <div class="p-2">
                                                        @if($flight->stream)
                                                            <div><strong>Стрім:</strong> {{ $flight->stream }}</div>
                                                        @endif
                                                        @if($flight->comment)
                                                            <div><strong>Коментар:</strong> {{ $flight->comment }}</div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center text-muted">
                        Вильотів поки що немає
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@section('js')
    <script>
        function setCurrentTime(fieldId) {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById(fieldId).value = now.toISOString().slice(0, 16);
        }

        function handleResultChange() {
            const result = document.getElementById('result').value;
            const detonation = document.getElementById('detonation');

            if (result === 'борт повернувся') {
                detonation.value = '0';
                detonation.disabled = true;
                // Add a hidden input to ensure the value is sent when disabled
                if (!document.getElementById('detonation_hidden')) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'detonation';
                    hiddenInput.id = 'detonation_hidden';
                    hiddenInput.value = '0';
                    detonation.parentNode.appendChild(hiddenInput);
                }
            } else {
                detonation.disabled = false;
                const hiddenInput = document.getElementById('detonation_hidden');
                if (hiddenInput) {
                    hiddenInput.parentNode.removeChild(hiddenInput);
                }
            }
        }

        $(document).ready(function() {
            handleResultChange();
            // Update custom file label
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Pause video on modal close
            $('.modal').on('hidden.bs.modal', function() {
                let video = $(this).find('video')[0];
                if (video) video.pause();
            });
        });
    </script>
@endsection

@section('css')
    <style>
        .bg-black { background-color: #000; }
        .expandable-body { background-color: #f8f9fa; }
    </style>
@endsection
