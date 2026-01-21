@extends('adminlte::page')

@section('title', 'Бойові вильоти')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Бойові вильоти (Активна зміна #{{ $userActiveShift->id }})</h1>
        <div>
            <span class="badge badge-success">Позиція: {{ $userActiveShift->position_name }}</span>
            <span class="badge badge-info ml-2">Початок: {{ $userActiveShift->started_at }}</span>
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

    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Додати новий виліт</h3>
                </div>
                <form action="{{ route('flight_operations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="combat_shift_id" value="{{ $userActiveShift->id }}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="drone_id">Дрон</label>
                            <select name="drone_id" id="drone_id" class="form-control @error('drone_id') is-invalid @enderror" required>
                                <option value="">Оберіть дрон</option>
                                @foreach($userActiveShift->drones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('drone_id') == $drone['id'] ? 'selected' : '' }}>
                                        {{ $drone['name'] }} ({{ $drone['model'] }}) (Фактично: {{ $drone['quantity'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('drone_id')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="ammunition_id">Боєприпас</label>
                            <select name="ammunition_id" id="ammunition_id" class="form-control @error('ammunition_id') is-invalid @enderror" required>
                                <option value="">Оберіть БК</option>
                                @foreach($userActiveShift->ammunition as $item)
                                    <option value="{{ $item['id'] }}" {{ old('ammunition_id') == $item['id'] ? 'selected' : '' }}>
                                        {{ $item['name'] }} (Фактично: {{ $item['quantity'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('ammunition_id')
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
                            <label for="flight_time">Час вильоту</label>
                            <input type="datetime-local" name="flight_time" id="flight_time" class="form-control @error('flight_time') is-invalid @enderror" value="{{ old('flight_time', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('flight_time')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="result">Результат</label>
                            <select name="result" id="result" class="form-control @error('result') is-invalid @enderror" required>
                                <option value="влучання" {{ old('result') == 'влучання' ? 'selected' : '' }}>Влучання</option>
                                <option value="удар в районі цілі" {{ old('result') == 'удар в районі цілі' ? 'selected' : '' }}>Удар в районі цілі</option>
                                <option value="втрата борту" {{ old('result') == 'втрата борту' ? 'selected' : '' }}>Втрата борту</option>
                            </select>
                            @error('result')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="detonation">Детонація</label>
                            <select name="detonation" id="detonation" class="form-control @error('detonation') is-invalid @enderror" required>
                                <option value="так" {{ old('detonation') == 'так' ? 'selected' : '' }}>Так</option>
                                <option value="ні" {{ old('detonation') == 'ні' || !old('detonation') ? 'selected' : '' }}>Ні</option>
                                <option value="інше" {{ old('detonation') == 'інше' ? 'selected' : '' }}>Інше</option>
                            </select>
                            @error('detonation')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="stream">Стрім (необов'язково)</label>
                            <input type="text" name="stream" id="stream" class="form-control @error('stream') is-invalid @enderror" value="{{ old('stream') }}" placeholder="Посилання на стрім">
                            @error('stream')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="video">Відео вильоту (макс. 75мб)</label>
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
                            <label for="note">Примітка (необов'язково)</label>
                            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" rows="2">{{ old('note') }}</textarea>
                            @error('note')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Зафіксувати виліт
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Історія вильотів поточної зміни</h3>
                </div>
                <div class="card-body p-0">
                    @php
                        $today = date('Y-m-d');
                    @endphp
                    @forelse($userActiveShift->flights as $date => $flights)
                        <div class="card mb-0 shadow-none border-bottom">
                            <div class="card-header p-2 bg-light">
                                <h3 class="card-title small">
                                    <strong>{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</strong>
                                    @if($date == $today)
                                        <span class="badge badge-primary ml-2">Сьогодні</span>
                                    @endif
                                    <span class="ml-2 text-muted">({{ count($flights) }})</span>
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pl-3 text-nowrap">Час</th>
                                                <th>Дрон</th>
                                                <th>БК</th>
                                                <th class="d-none d-lg-table-cell">Координати</th>
                                                <th class="d-none d-xl-table-cell">Стрім</th>
                                                <th class="d-none d-md-table-cell">Детонація</th>
                                                <th>Відео</th>
                                                <th>Результат</th>
                                                <th>Дії</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($flights as $flight)
                                                <tr>
                                                    <td class="pl-3 text-nowrap">{{ \Carbon\Carbon::parse($flight['flight_time'])->format('H:i') }}</td>
                                                    <td>{{ $flight['drone_name'] }}</td>
                                                    <td>{{ $flight['ammunition_name'] }}</td>
                                                    <td class="d-none d-lg-table-cell">{{ $flight['coordinates'] }}</td>
                                                    <td class="d-none d-xl-table-cell">{{ $flight['stream'] }}</td>
                                                    <td class="d-none d-md-table-cell">{{ $flight['detonation'] ?? 'ні' }}</td>
                                                    <td>
                                                        @if(!empty($flight['video_path']))
                                                            <button type="button" class="btn btn-xs btn-secondary" data-toggle="modal" data-target="#videoModal{{ $flight['id'] }}">
                                                                <i class="fas fa-video"></i>
                                                            </button>

                                                            <div class="modal fade" id="videoModal{{ $flight['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Відео вильоту ({{ \Carbon\Carbon::parse($flight['flight_time'])->format('H:i') }})</h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body text-center bg-black">
                                                                            <video width="100%" controls>
                                                                                <source src="{{ Storage::url($flight['video_path']) }}" type="video/mp4">
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
                                                        @php
                                                            $badgeClass = match($flight['result']) {
                                                                'влучання' => 'success',
                                                                'удар в районі цілі' => 'warning',
                                                                'втрата борту' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                            $shortResult = match($flight['result']) {
                                                                'влучання' => 'влуч.',
                                                                'удар в районі цілі' => 'удар',
                                                                'втрата борту' => 'втрата',
                                                                default => $flight['result']
                                                            };
                                                        @endphp
                                                        <span class="badge badge-{{ $badgeClass }} d-none d-md-inline">{{ $flight['result'] }}</span>
                                                        <span class="badge badge-{{ $badgeClass }} d-inline d-md-none">{{ $shortResult }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('flight_operations.edit', $flight['id']) }}" class="btn btn-xs btn-info">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('flight_operations.destroy', $flight['id']) }}" method="POST" style="display:inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Ви впевнені?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center">
                            <span class="text-muted">Вильотів у цій зміні ще не зафіксовано.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.custom-file-input').on('change', function () {
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
