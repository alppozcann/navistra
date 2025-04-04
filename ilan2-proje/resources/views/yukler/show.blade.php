@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $yuk->title }}</h4>
                <span class="badge bg-primary fs-5">{{ number_format($yuk->proposed_price, 2) }} TL</span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Yük Bilgileri</h5>
                        <p class="mb-1"><strong>Yük Tipi:</strong> {{ $yuk->yuk_type }}</p>
                        <p class="mb-1"><strong>Ağırlık:</strong> {{ number_format($yuk->weight, 2) }} kg</p>
                        @if($yuk->dimensions)
                            <p class="mb-1">
                                <strong>Boyutlar:</strong>
                                {{ $yuk->dimensions['width'] ?? 0 }} x 
                                {{ $yuk->dimensions['length'] ?? 0 }} x 
                                {{ $yuk->dimensions['height'] ?? 0 }} cm
                            </p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h5>Taşıma Bilgileri</h5>
                        <p class="mb-1"><strong>Alınacağı Yer:</strong> {{ $yuk->from_location }}</p>
                        <p class="mb-1"><strong>Teslim Edileceği Yer:</strong> {{ $yuk->to_location }}</p>
                        <p class="mb-1">
                            <strong>İstenen Teslimat Tarihi:</strong> 
                            {{ $yuk->desired_delivery_date->format('d.m.Y') }}
                        </p>
                        <p class="mb-1">
                            <strong>Durum:</strong>
                            @if($yuk->status === 'active')
                                <span class="badge bg-success">Aktif</span>
                            @elseif($yuk->status === 'matched')
                                <span class="badge bg-info">Eşleştirildi</span>
                            @elseif($yuk->status === 'completed')
                                <span class="badge bg-secondary">Tamamlandı</span>
                            @else
                                <span class="badge bg-warning">{{ $yuk->status }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                @if($yuk->description)
                    <div class="mb-4">
                        <h5>Açıklama</h5>
                        <p>{{ $yuk->description }}</p>
                    </div>
                @endif
                
                <div class="mb-3">
                    <h5>Yük Veren Bilgileri</h5>
                    <p class="mb-1"><strong>Ad Soyad:</strong> {{ $yuk->user->name }}</p>
                    @if($yuk->user->description)
                        <p class="mb-1"><strong>Hakkında:</strong> {{ $yuk->user->description }}</p>
                    @endif
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('yukler.index') }}" class="btn btn-secondary">Geri Dön</a>
                    
                    @auth
                        @if(Auth::id() === $yuk->user_id)
                            @if($yuk->status === 'matched')
                                <form action="{{ route('yukler.complete_delivery', $yuk) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Teslimatı tamamlamak istediğinize emin misiniz?')">Teslimatı Tamamla</button>
                                </form>
                                <form action="{{ route('yukler.cancel_match', $yuk) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Eşleştirmeyi iptal etmek istediğinize emin misiniz?')">Eşleştirmeyi İptal Et</button>
                                </form>
                            @elseif($yuk->status === 'active')
                                <div>
                                    <a href="{{ route('yukler.edit', $yuk) }}" class="btn btn-primary">Düzenle</a>
                                    <form action="{{ route('yukler.destroy', $yuk) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Bu yük ilanını silmek istediğinize emin misiniz?')">Sil</button>
                                    </form>
                                </div>
                            @endif
                        @elseif(Auth::user()->isGemici())
                            <!-- Burada gemici kullanıcı için işlem butonları olabilir -->
                        @endif
                    @endauth
                </div>
            </div>
        </div>
        
        @if($yuk->status === 'matched' && $yuk->matchedGemiRoute)
            <div class="card">
                <div class="card-header">
                    <h4>Eşleşen Gemi Rotası</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ $yuk->matchedGemiRoute->title }}</h5>
                            <p class="mb-1"><strong>Gemici:</strong> {{ $yuk->matchedGemiRoute->user->name }}</p>
                            <p class="mb-1"><strong>Rota:</strong> {{ $yuk->matchedGemiRoute->start_location }} → {{ $yuk->matchedGemiRoute->end_location }}</p>
                            <p class="mb-1">
                                <strong>Tarihler:</strong> 
                                {{ $yuk->matchedGemiRoute->departure_date->format('d.m.Y') }} - 
                                {{ $yuk->matchedGemiRoute->arrival_date->format('d.m.Y') }}
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('gemi_routes.show', $yuk->matchedGemiRoute) }}" class="btn btn-outline-primary">Rota Detayları</a>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(Auth::id() === $yuk->user_id && isset($matchingRoutes) && $matchingRoutes->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4>Bu Yük İçin Uygun Rotalar</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rota</th>
                                    <th>Gemici</th>
                                    <th>Tarih</th>
                                    <th>Fiyat</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($matchingRoutes as $route)
                                    <tr>
                                        <td>
                                            <a href="{{ route('gemi_routes.show', $route) }}">{{ $route->title }}</a>
                                            <small class="d-block text-muted">{{ $route->start_location }} → {{ $route->end_location }}</small>
                                        </td>
                                        <td>{{ $route->user->name }}</td>
                                        <td>{{ $route->departure_date->format('d.m.Y') }}</td>
                                        <td>{{ number_format($route->price, 2) }} TL</td>
                                        <td>
                                            <form action="{{ route('yukler.request_match', ['yuk' => $yuk, 'gemiRoute' => $route]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bu rotaya yükünüzü eklemek istediğinize emin misiniz?')">Eşleştir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Hızlı Bilgiler</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Fiyat Bilgisi</h6>
                    <div class="d-flex justify-content-between">
                        <span>Teklif Edilen Fiyat:</span>
                        <span class="fw-bold">{{ number_format($yuk->proposed_price, 2) }} TL</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6>Zaman Bilgisi</h6>
                    <div class="d-flex justify-content-between">
                        <span>İlan Tarihi:</span>
                        <span>{{ $yuk->created_at->format('d.m.Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>İstenen Teslimat:</span>
                        <span>{{ $yuk->desired_delivery_date->format('d.m.Y') }}</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6>İletişim</h6>
                    <p class="mb-0">İlan sahibiyle iletişime geçmek için giriş yapın.</p>
                </div>
            </div>
        </div>
        
        @if(Auth::check() && Auth::user()->isGemici() && Auth::id() !== $yuk->user_id)
            <div class="card">
                <div class="card-header">
                    <h5>Bu yükü taşımak ister misiniz?</h5>
                </div>
                <div class="card-body">
                    <p>Bu yükü taşımak için rotanız uygunsa, teklif verebilirsiniz.</p>
                    <div class="d-grid">
                        <a href="{{ route('gemi_routes.create') }}" class="btn btn-success">Yeni Rota Ekle</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
