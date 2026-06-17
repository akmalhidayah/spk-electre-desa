@extends('layouts.app')

@section('title', 'Penilaian Alternatif - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Penilaian Alternatif')

@section('content')
    @php
        $isComplete = $totalSeharusnya > 0 && $totalTerisi === $totalSeharusnya;
        $completionWidth = min($persentaseKelengkapan, 100);
        $scaleLabels = [
            1 => 'Sangat rendah',
            2 => 'Rendah',
            3 => 'Sedang',
            4 => 'Tinggi',
            5 => 'Sangat tinggi',
        ];
    @endphp

    <div class="stack penilaian-page">
        <section class="page-header-card">
            <div>
                <h2>Penilaian Alternatif</h2>
                <p>Input nilai setiap dusun terhadap kriteria sebagai matriks keputusan metode ELECTRE.</p>
            </div>
            @if ($dusuns->isNotEmpty() && $kriterias->isNotEmpty())
                <button type="submit" form="penilaianForm" class="btn btn-primary btn-auto">
                    <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" /><path d="M17 21v-8H7v8M7 3v5h8" /></svg>
                    Simpan Penilaian
                </button>
            @endif
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('admin.penilaian.preview') }}" class="filter-bar penilaian-year-form compact-filter electre-filter">
                <div class="filter-field input-with-icon">
                    <label for="tahun" class="form-label sr-only">Tahun Penilaian</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4M16 2v4" /><path d="M3 10h18" /><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z" /></svg>
                    </span>
                    <input
                        id="tahun"
                        type="number"
                        name="tahun"
                        min="2020"
                        max="2100"
                        value="{{ $tahun }}"
                        class="form-control"
                        list="tahun-list"
                        required
                    >
                    <datalist id="tahun-list">
                        @foreach ($tahunList as $itemTahun)
                            <option value="{{ $itemTahun }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                        Tampilkan
                    </button>
                    <a href="{{ route('admin.penilaian.index') }}" class="btn btn-light">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7" /><path d="M3 4v6h6" /></svg>
                        Tahun Ini
                    </a>
                </div>
            </form>
        </section>

        @if ($errors->any())
            <div class="alert alert-danger">
                Pastikan seluruh nilai alternatif telah diisi dengan skala 1 sampai 5.
            </div>
        @endif

        @if ($dusuns->isEmpty())
            <div class="alert alert-warning">Belum ada dusun aktif. Silakan aktifkan data dusun terlebih dahulu.</div>
        @endif

        @if ($kriterias->isEmpty())
            <div class="alert alert-warning">Belum ada kriteria aktif. Silakan aktifkan data kriteria terlebih dahulu.</div>
        @endif

        <section class="panel scale-panel compact-scale-panel">
            <div>
                <h2 class="panel-title">Skala Nilai</h2>
                <p class="panel-text">Gunakan skala 1 sampai 5 untuk setiap dusun dan kriteria.</p>
            </div>
            <div class="scale-grid">
                @foreach ($scaleLabels as $nilai => $label)
                    <span><strong>{{ $nilai }}</strong> {{ $label }}</span>
                @endforeach
            </div>
        </section>

        @if ($dusuns->isNotEmpty() && $kriterias->isNotEmpty())
            <form
                id="penilaianForm"
                method="POST"
                action="{{ route('admin.penilaian.store') }}"
                class="stack js-confirm"
                data-title="Simpan Penilaian?"
                data-text="Nilai alternatif untuk tahun ini akan disimpan atau diperbarui."
                data-icon="question"
                data-confirm-button="Ya, Simpan"
            >
                @csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">

                <section class="panel">
                    <div class="matrix-toolbar">
                        <div>
                            <h2 class="panel-title">Matriks Keputusan Tahun {{ $tahun }}</h2>
                            <p class="panel-text">Setiap baris adalah dusun aktif dan setiap kolom adalah kriteria aktif.</p>
                        </div>
                        <span class="badge {{ $isComplete ? 'badge-success' : 'badge-warning' }}">
                            {{ $isComplete ? 'Lengkap' : 'Belum Lengkap' }}
                        </span>
                    </div>

                    <div class="table-wrap desktop-table">
                        <table class="data-table assessment-table">
                            <thead>
                                <tr>
                                    <th>Dusun</th>
                                    @foreach ($kriterias as $kriteria)
                                        <th>
                                            <strong>{{ $kriteria->kode }}</strong>
                                            <small>{{ $kriteria->nama_kriteria }}</small>
                                            <small>Bobot {{ number_format((float) $kriteria->bobot, 2, ',', '.') }}%</small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dusuns as $dusun)
                                    <tr>
                                        <td>
                                            <span class="code-pill">{{ $dusun->kode_alternatif ?? '-' }}</span>
                                            <strong>{{ $dusun->nama_dusun }}</strong>
                                        </td>
                                        @foreach ($kriterias as $kriteria)
                                            @php
                                                $fieldName = "nilai[{$dusun->id}][{$kriteria->id}]";
                                                $oldPath = "nilai.{$dusun->id}.{$kriteria->id}";
                                                $selectedValue = old($oldPath, $values[$dusun->id][$kriteria->id] ?? '');
                                            @endphp
                                            <td>
                                                <select
                                                    name="{{ $fieldName }}"
                                                    class="form-control score-select assessment-desktop-input @error($oldPath) is-invalid @enderror"
                                                    data-assessment-key="{{ $dusun->id }}-{{ $kriteria->id }}"
                                                    required
                                                    aria-label="Nilai {{ $dusun->nama_dusun }} untuk {{ $kriteria->nama_kriteria }}"
                                                >
                                                    <option value="">Pilih</option>
                                                    @for ($nilai = 1; $nilai <= 5; $nilai++)
                                                        <option value="{{ $nilai }}" @selected((string) $selectedValue === (string) $nilai)>
                                                            {{ $nilai }} - {{ $scaleLabels[$nilai] }}
                                                        </option>
                                                    @endfor
                                                </select>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mobile-list assessment-mobile-list">
                        @foreach ($dusuns as $dusun)
                            <article class="mobile-card">
                                <div class="mobile-card-head">
                                    <div>
                                        <span class="code-pill">{{ $dusun->kode_alternatif ?? '-' }}</span>
                                        <h3>{{ $dusun->nama_dusun }}</h3>
                                    </div>
                                </div>
                                <div class="assessment-card-grid">
                                    @foreach ($kriterias as $kriteria)
                                        @php
                                            $fieldName = "nilai[{$dusun->id}][{$kriteria->id}]";
                                            $oldPath = "nilai.{$dusun->id}.{$kriteria->id}";
                                            $selectedValue = old($oldPath, $values[$dusun->id][$kriteria->id] ?? '');
                                        @endphp
                                        <div class="assessment-field">
                                            <label for="nilai-{{ $dusun->id }}-{{ $kriteria->id }}" class="form-label">
                                                {{ $kriteria->kode }} - {{ $kriteria->nama_kriteria }}
                                            </label>
                                            <select
                                                id="nilai-{{ $dusun->id }}-{{ $kriteria->id }}"
                                                name="{{ $fieldName }}"
                                                class="form-control assessment-mobile-input @error($oldPath) is-invalid @enderror"
                                                data-assessment-key="{{ $dusun->id }}-{{ $kriteria->id }}"
                                                required
                                            >
                                                <option value="">Pilih nilai</option>
                                                @for ($nilai = 1; $nilai <= 5; $nilai++)
                                                    <option value="{{ $nilai }}" @selected((string) $selectedValue === (string) $nilai)>
                                                        {{ $nilai }} - {{ $scaleLabels[$nilai] }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="panel submit-panel">
                    <div>
                        <h2 class="panel-title">Simpan Matriks Penilaian</h2>
                        <p class="panel-text">Data yang sudah ada pada tahun {{ $tahun }} akan diperbarui tanpa membuat duplikasi.</p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-auto">
                        Simpan Penilaian
                    </button>
                </section>
            </form>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var desktopInputs = document.querySelectorAll('.assessment-desktop-input');
            var mobileInputs = document.querySelectorAll('.assessment-mobile-input');
            var mediaQuery = window.matchMedia('(min-width: 900px)');

            function syncAssessmentInputs() {
                var desktopMode = mediaQuery.matches;

                desktopInputs.forEach(function (input) {
                    input.disabled = !desktopMode;
                });

                mobileInputs.forEach(function (input) {
                    input.disabled = desktopMode;
                });
            }

            document.querySelectorAll('[data-assessment-key]').forEach(function (input) {
                input.addEventListener('change', function () {
                    document.querySelectorAll('[data-assessment-key="' + input.dataset.assessmentKey + '"]').forEach(function (pairedInput) {
                        if (pairedInput !== input) {
                            pairedInput.value = input.value;
                        }
                    });
                });
            });

            syncAssessmentInputs();

            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', syncAssessmentInputs);
            } else if (mediaQuery.addListener) {
                mediaQuery.addListener(syncAssessmentInputs);
            }
        });
    </script>
@endsection
