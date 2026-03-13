@extends('adminlte::page')

@section('title', 'Додати розвідувальний дрон')

@section('content_header')
    <h1>Додати розвідувальний дрон</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Основна інформація</h3>
                </div>
                <form action="{{ route('recon.drones.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Назва</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Наприклад: Mavic 3T" value="{{ old('name') }}" required>
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
                            <label for="position_id">Позиція (тільки Recon)</label>
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
                            <label for="shift_type">Тип зміни</label>
                            <select name="shift_type" id="shift_type" class="form-control @error('shift_type') is-invalid @enderror" required>
                                <option value="day" {{ old('shift_type') === 'day' ? 'selected' : '' }}>Денна</option>
                                <option value="night" {{ old('shift_type') === 'night' ? 'selected' : '' }}>Нічна</option>
                                <option value="both" {{ old('shift_type') === 'both' || old('shift_type') === null ? 'selected' : '' }}>Обидві</option>
                            </select>
                            @error('shift_type')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Зберегти</button>
                        <a href="{{ route('recon.drones.index') }}" class="btn btn-default float-right">Скасувати</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
