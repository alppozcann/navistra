@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="section-title">Yük Detayları</h1>
                <div>
                    <a href="{{ route('yukler.edit', $yuk) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil me-2"></i>Düzenle
                    </a>
                    <form action="{{ route('yukler.destroy', $yuk) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Bu yükü silmek istediğinize emin misiniz?')">
                            <i class="bi bi-trash me-2"></i>Sil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-lg border-0 rounded-lg mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Yük Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Başlık</h6>
                        <p class="mb-0">{{ $yuk->title }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Yük Türü</h6>
                        <p class="mb-0">{{ $yuk->yuk_type }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Ağırlık</h6>
                        <p class="mb-0">{{ number_format($yuk->weight, 2) }} kg</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Boyutlar</h6>
                        <p class="mb-0">
                            @if($yuk->dimensions)
                                {{ $yuk->dimensions['length'] ?? '?' }} x 
                                {{ $yuk->dimensions['width'] ?? '?' }} x 
                                {{ $yuk->dimensions['height'] ?? '?' }} cm
                            @else
                                Belirtilmemiş
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Rota</h6>
                        <p class="mb-0">{{ $yuk->from_location }} → {{ $yuk->to_location }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Teklif Edilen Fiyat</h6>
                        <p class="mb-0">{{ number_format($yuk->proposed_price, 2) }} TL</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">İstenen Teslimat Tarihi</h6>
                        <p class="mb-0">{{ $yuk->desired_delivery_date->format('d.m.Y') }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Durum</h6>
                        <p class="mb-0">
                            @switch($yuk->status)
                                @case('active')
                                    <span class="badge bg-success">Aktif</span>
                                    @break
                                @case('matched')
                                    <span class="badge bg-primary">Eşleşti</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-info">Tamamlandı</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $yuk->status }}</span>
                            @endswitch
                        </p>
                    </div>
                    @if($yuk->description)
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Açıklama</h6>
                            <p class="mb-0">{{ $yuk->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Eşleşebilecek Rotalar</h5>
                        <div class="d-flex align-items-center">
                            <label class="me-2">Sırala:</label>
                            <select class="form-select" id="sortRoutes" style="width: auto;">
                                <option value="score">Eşleşme Puanı</option>
                                <option value="price">Fiyat</option>
                                <option value="date">Tarih</option>
                                <option value="capacity">Kapasite</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($matchingRoutes->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Rota</th>
                                        <th>Kapasite</th>
                                        <th>Fiyat</th>
                                        <th>Tarih</th>
                                        <th>Eşleşme</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="routesTableBody">
                                    @foreach($matchingRoutes as $route)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">{{ $route->title }}</h6>
                                                        <small class="text-muted">{{ $route->start_location }} → {{ $route->end_location }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ number_format($route->available_capacity, 2) }} kg</td>
                                            <td>{{ number_format($route->price, 2) }} TL</td>
                                            <td>
                                                <div>Kalkış: {{ $route->departure_date->format('d.m.Y') }}</div>
                                                <div>Varış: {{ $route->arrival_date->format('d.m.Y') }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: {{ $route->match_score * 100 }}%"
                                                             aria-valuenow="{{ $route->match_score * 100 }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="ms-2">{{ number_format($route->match_score * 100, 0) }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                @if($yuk->status === 'active')
                                                    <form action="{{ route('yukler.request_match', ['yuk' => $yuk->id, 'gemiRoute' => $route->id]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-link-45deg me-1"></i>Eşleştir
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('gemi_routes.show', $route) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Bu yük için uygun rota bulunamadı.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sortRoutes');
    const tableBody = document.getElementById('routesTableBody');
    
    sortSelect.addEventListener('change', function() {
        const rows = Array.from(tableBody.getElementsByTagName('tr'));
        const sortBy = this.value;
        
        rows.sort((a, b) => {
            let aValue, bValue;
            
            switch(sortBy) {
                case 'score':
                    aValue = parseFloat(a.querySelector('.progress-bar').style.width);
                    bValue = parseFloat(b.querySelector('.progress-bar').style.width);
                    break;
                case 'price':
                    aValue = parseFloat(a.cells[2].textContent.replace(/[^0-9.-]+/g, ''));
                    bValue = parseFloat(b.cells[2].textContent.replace(/[^0-9.-]+/g, ''));
                    break;
                case 'date':
                    aValue = new Date(a.cells[3].textContent.split('\n')[0].split(': ')[1]);
                    bValue = new Date(b.cells[3].textContent.split('\n')[0].split(': ')[1]);
                    break;
                case 'capacity':
                    aValue = parseFloat(a.cells[1].textContent.replace(/[^0-9.-]+/g, ''));
                    bValue = parseFloat(b.cells[1].textContent.replace(/[^0-9.-]+/g, ''));
                    break;
            }
            
            return bValue - aValue; // Sort in descending order
        });
        
        // Clear and re-append sorted rows
        tableBody.innerHTML = '';
        rows.forEach(row => tableBody.appendChild(row));
    });
});
</script>
@endpush
@endsection
