@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Yeni Yük İlanı Ekle</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('yukler.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="yuk_type" class="form-label">Yük Tipi</label>
                        <input type="text" class="form-control @error('yuk_type') is-invalid @enderror" id="yuk_type" name="yuk_type" value="{{ old('yuk_type') }}" required>
                        @error('yuk_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Örn: Gıda, Tekstil, Elektronik, vb.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="weight" class="form-label">Ağırlık (kg)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" value="{{ old('weight') }}" required>
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Boyutlar (opsiyonel)</label>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">En</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('width') is-invalid @enderror" id="width" name="width" value="{{ old('width') }}">
                                    <span class="input-group-text">cm</span>
                                </div>
                                @error('width')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">Boy</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('length') is-invalid @enderror" id="length" name="length" value="{{ old('length') }}">
                                    <span class="input-group-text">cm</span>
                                </div>
                                @error('length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="input-group">
                                    <span class="input-group-text">Yükseklik</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('height') is-invalid @enderror" id="height" name="height" value="{{ old('height') }}">
                                    <span class="input-group-text">cm</span>
                                </div>
                                @error('height')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="from_location" class="form-label">Alınacağı Yer</label>
                            <input type="text" class="form-control @error('from_location') is-invalid @enderror" id="from_location" name="from_location" value="{{ old('from_location') }}" required>
                            @error('from_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="to_location" class="form-label">Teslim Edileceği Yer</label>
                            <input type="text" class="form-control @error('to_location') is-invalid @enderror" id="to_location" name="to_location" value="{{ old('to_location') }}" required>
                            @error('to_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="proposed_price" class="form-label">Teklif Edilen Fiyat (TL)</label>
                            <input type="number" step="0.01" min="0" class="form-control @error('proposed_price') is-invalid @enderror" id="proposed_price" name="proposed_price" value="{{ old('proposed_price') }}" required>
                            @error('proposed_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="desired_delivery_date" class="form-label">İstenen Teslimat Tarihi</label>
                            <input type="date" class="form-control @error('desired_delivery_date') is-invalid @enderror" id="desired_delivery_date" name="desired_delivery_date" value="{{ old('desired_delivery_date') }}" required>
                            @error('desired_delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('yukler.index') }}" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary">İlanı Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
