@extends('adminlte::page')

@section('title', 'Розпочати чергування')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Розпочати нове чергування</h1>
        <div>
            <a href="{{ route('combat_shifts.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Назад до списку
            </a>
            <button type="submit" form="create-shift-form" class="btn btn-primary ml-2">
                <i class="fas fa-save"></i> Зберегти
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('combat_shifts.store') }}" method="POST" id="create-shift-form">
                @csrf
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
                                            <option value="{{ $user->id }}" {{ (is_array(old('user_ids')) && in_array($user->id, old('user_ids'))) || (!old('user_ids') && $user->id == auth()->id()) ? 'selected' : '' }}>
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
                                        <option value="">Оберіть позицію</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
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
                                        <option value="opened" {{ old('status') == 'opened' ? 'selected' : '' }}>Відкрито</option>
                                        <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Закрито</option>
                                    </select>
                                    @error('status')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="started_at">Час початку</label>
                                    <input type="datetime-local" name="started_at" id="started_at" class="form-control @error('started_at') is-invalid @enderror" value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('started_at')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="ended_at">Час завершення (необов'язково)</label>
                                    <input type="datetime-local" name="ended_at" id="ended_at" class="form-control @error('ended_at') is-invalid @enderror" value="{{ old('ended_at') }}">
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
                                    @if(old('crew'))
                                        @foreach(old('crew') as $index => $member)
                                            <div class="crew-member row mb-2">
                                                <div class="col-md-5">
                                                    <input type="text" name="crew[{{ $index }}][callsign]" class="form-control form-control-sm" placeholder="Позивний" value="{{ $member['callsign'] }}" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="crew[{{ $index }}][role]" class="form-control form-control-sm" placeholder="Посада" value="{{ $member['role'] }}" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-sm remove-crew-member">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="crew-member row mb-2">
                                            <div class="col-md-5">
                                                <input type="text" name="crew[0][callsign]" class="form-control form-control-sm" placeholder="Позивний" required>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="crew[0][role]" class="form-control form-control-sm" placeholder="Посада" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm remove-crew-member">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Пошкодження</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" id="add-damaged-drone">
                                        <i class="fas fa-plus"></i> Додати дрон
                                    </button>
                                    <button type="button" class="btn btn-tool" id="add-damaged-coil">
                                        <i class="fas fa-plus"></i> Додати катушку
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6>Пошкоджені дрони</h6>
                                <div id="damaged-drones-container">
                                    @if(old('damaged_drones'))
                                        @foreach(old('damaged_drones') as $index => $item)
                                            <div class="damaged-item row mb-2">
                                                <div class="col-md-7">
                                                    <input type="text" name="damaged_drones[{{ $index }}][name]" class="form-control form-control-sm" placeholder="Назва дрона" value="{{ $item['name'] }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" name="damaged_drones[{{ $index }}][quantity]" class="form-control form-control-sm" placeholder="К-ть" value="{{ $item['quantity'] }}" min="1" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-sm remove-damaged-item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <h6 class="mt-3">Пошкоджені катушки</h6>
                                <div id="damaged-coils-container">
                                    @if(old('damaged_coils'))
                                        @foreach(old('damaged_coils') as $index => $item)
                                            <div class="damaged-item row mb-2">
                                                <div class="col-md-7">
                                                    <input type="text" name="damaged_coils[{{ $index }}][name]" class="form-control form-control-sm" placeholder="Назва катушки" value="{{ $item['name'] }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" name="damaged_coils[{{ $index }}][quantity]" class="form-control form-control-sm" placeholder="К-ть" value="{{ $item['quantity'] }}" min="1" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-sm remove-damaged-item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
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
                                    <table class="table table-sm text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Назва</th>
                                                <th style="width: 100px;">Фактична кількість</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($drones as $drone)
                                                <tr>
                                                    <td>{{ $drone->name }} ({{ $drone->model }})</td>
                                                    <td>
                                                        <input type="number" name="drones[{{ $drone->id }}]" class="form-control form-control-sm" value="{{ old("drones.$drone->id", 0) }}" min="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <h5 class="mt-4">Боєприпаси</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Назва</th>
                                                <th style="width: 100px;">Фактична кількість</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ammunition as $item)
                                                <tr>
                                                    <td>{{ $item->name }}</td>
                                                    <td>
                                                        <input type="number" name="ammunition[{{ $item->id }}]" class="form-control form-control-sm" value="{{ old("ammunition.$item->id", 0) }}" min="0">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Зберегти
                                </button>
                                <a href="{{ route('combat_shifts.index') }}" class="btn btn-default float-right">Скасувати</a>
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

            let crewIndex = {{ old('crew') ? count(old('crew')) : 1 }};

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

            let damagedDroneIndex = {{ old('damaged_drones') ? count(old('damaged_drones')) : 0 }};
            let damagedCoilIndex = {{ old('damaged_coils') ? count(old('damaged_coils')) : 0 }};

            $('#add-damaged-drone').click(function() {
                const html = `
                    <div class="damaged-item row mb-2">
                        <div class="col-md-7">
                            <input type="text" name="damaged_drones[${damagedDroneIndex}][name]" class="form-control form-control-sm" placeholder="Назва дрона" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="damaged_drones[${damagedDroneIndex}][quantity]" class="form-control form-control-sm" placeholder="К-ть" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-damaged-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#damaged-drones-container').append(html);
                damagedDroneIndex++;
            });

            $('#add-damaged-coil').click(function() {
                const html = `
                    <div class="damaged-item row mb-2">
                        <div class="col-md-7">
                            <input type="text" name="damaged_coils[${damagedCoilIndex}][name]" class="form-control form-control-sm" placeholder="Назва катушки" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="damaged_coils[${damagedCoilIndex}][quantity]" class="form-control form-control-sm" placeholder="К-ть" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-damaged-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#damaged-coils-container').append(html);
                damagedCoilIndex++;
            });

            $(document).on('click', '.remove-damaged-item', function() {
                $(this).closest('.damaged-item').remove();
            });
        });
    </script>
@endsection
