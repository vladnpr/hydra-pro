@extends('adminlte::page')

@section('title', 'Редагувати виліт')

@section('content_header')
    <h1>Редагувати виліт (Зміна #{{ $activeShift->id }})</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Дані вильоту</h3>
                </div>
                <form action="{{ route('flight_operations.update', $flight->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="drone_id">Дрон</label>
                            <select name="drone_id" id="drone_id" class="form-control @error('drone_id') is-invalid @enderror" required>
                                @foreach($activeShift->drones as $drone)
                                    <option value="{{ $drone['id'] }}" {{ old('drone_id', $flight->drone_id) == $drone['id'] ? 'selected' : '' }}>
                                        {{ $drone['name'] }} ({{ $drone['model'] }})
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
                                @foreach($activeShift->ammunition as $item)
                                    <option value="{{ $item['id'] }}" {{ old('ammunition_id', $flight->ammunition_id) == $item['id'] ? 'selected' : '' }}>
                                        {{ $item['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ammunition_id')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="coordinates">Координати</label>
                            <input type="text" name="coordinates" id="coordinates" class="form-control @error('coordinates') is-invalid @enderror" value="{{ old('coordinates', $flight->coordinates) }}" required>
                            @error('coordinates')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="flight_time">Час вильоту</label>
                            <input type="datetime-local" name="flight_time" id="flight_time" class="form-control @error('flight_time') is-invalid @enderror" value="{{ old('flight_time', $flight->flight_time->format('Y-m-d\TH:i')) }}" required>
                            @error('flight_time')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="result">Результат</label>
                            <select name="result" id="result" class="form-control @error('result') is-invalid @enderror" required>
                                <option value="влучання" {{ old('result', $flight->result) == 'влучання' ? 'selected' : '' }}>Влучання</option>
                                <option value="удар в районі цілі" {{ old('result', $flight->result) == 'удар в районі цілі' ? 'selected' : '' }}>Удар в районі цілі</option>
                                <option value="недольот" {{ old('result', $flight->result) == 'недольот' ? 'selected' : '' }}>Недольот</option>
                            </select>
                            @error('result')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="detonation">Детонація</label>
                            <select name="detonation" id="detonation" class="form-control @error('detonation') is-invalid @enderror" required>
                                <option value="так" {{ old('detonation', $flight->detonation) == 'так' ? 'selected' : '' }}>Так</option>
                                <option value="ні" {{ old('detonation', $flight->detonation) == 'ні' ? 'selected' : '' }}>Ні</option>
                                <option value="інше" {{ old('detonation', $flight->detonation) == 'інше' ? 'selected' : '' }}>Інше</option>
                            </select>
                            @error('detonation')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="stream">Стрім (необов'язково)</label>
                            <input type="text" name="stream" id="stream" class="form-control @error('stream') is-invalid @enderror" value="{{ old('stream', $flight->stream) }}" placeholder="Посилання на стрім">
                            @error('stream')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="note">Примітка (необов'язково)</label>
                            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" rows="2">{{ old('note', $flight->note) }}</textarea>
                            @error('note')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('flight_operations.index') }}" class="btn btn-default">Скасувати</a>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-save"></i> Оновити дані
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
