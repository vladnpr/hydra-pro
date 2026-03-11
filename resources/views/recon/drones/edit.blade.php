@extends('adminlte::page')

@section('title', 'Редагувати дрон')

@section('content_header')
    <h1>Редагувати дрон</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Редагування: {{ $drone->name }}</h3>
                </div>
                <form action="{{ route('recon.drones.update', $drone->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Назва</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name', $drone->name) }}" required>
                            @error('name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="serial_number">Серійний номер</label>
                            <input type="text" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" id="serial_number" value="{{ old('serial_number', $drone->serial_number) }}">
                            @error('serial_number')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="position_id">Позиція (тільки Recon)</label>
                            <select name="position_id" id="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ old('position_id', $drone->position_id) == $position->id ? 'selected' : '' }}>
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
                                <option value="active" {{ old('status', $drone->status) === 'active' ? 'selected' : '' }}>Активний</option>
                                <option value="repair" {{ old('status', $drone->status) === 'repair' ? 'selected' : '' }}>В ремонті</option>
                                <option value="non_operational" {{ old('status', $drone->status) === 'non_operational' ? 'selected' : '' }}>Не боєготовий</option>
                                <option value="lost" {{ old('status', $drone->status) === 'lost' ? 'selected' : '' }}>Втрачений</option>
                            </select>
                            @error('status')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info">Оновити</button>
                        <a href="{{ route('recon.drones.index') }}" class="btn btn-default float-right">Скасувати</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
