@extends('adminlte::page')

@section('title', 'Додати НРК')

@section('content_header')
    <h1>Додати НРК</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Основна інформація</h3>
                </div>
                <form action="{{ route('ugv.drones.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Назва</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Наприклад: UGV 1" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="serial_number">Серійний номер</label>
                            <input type="text" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" id="serial_number" placeholder="Введіть серійний номер" value="{{ old('serial_number') }}">
                            @error('serial_number')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="position_id">Позиція (UGV)</label>
                            <select name="position_id" id="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('position_id') === null ? 'selected' : '' }}>Оберіть позицію</option>
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
                                <option value="active" {{ old('status') === 'active' || old('status') === null ? 'selected' : '' }}>Активний</option>
                                <option value="repair" {{ old('status') === 'repair' ? 'selected' : '' }}>В ремонті</option>
                                <option value="non_operational" {{ old('status') === 'non_operational' ? 'selected' : '' }}>Не боєготовий</option>
                                <option value="lost" {{ old('status') === 'lost' ? 'selected' : '' }}>Втрачений</option>
                            </select>
                            @error('status')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="shift_type">Тип зміни (Денний/Нічний)</label>
                            <select name="shift_type" id="shift_type" class="form-control @error('shift_type') is-invalid @enderror" required>
                                @foreach(\App\Enums\ShiftTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('shift_type') === $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shift_type')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group" id="lost_at_group" style="display: none;">
                            <label for="lost_at">Дата та час втрати</label>
                            <input type="datetime-local" name="lost_at" class="form-control @error('lost_at') is-invalid @enderror" id="lost_at" value="{{ old('lost_at') }}">
                            @error('lost_at')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Зберегти</button>
                        <a href="{{ route('ugv.drones.index') }}" class="btn btn-default float-right">Скасувати</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            function toggleLostAt() {
                if ($('#status').val() === 'lost') {
                    $('#lost_at_group').show();
                } else {
                    $('#lost_at_group').hide();
                }
            }

            $('#status').change(function() {
                toggleLostAt();
            });

            // Initial check
            toggleLostAt();
        });
    </script>
@endsection
