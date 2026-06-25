@extends('layouts.app')

@section('title', 'Usulan Pembangunan - SPK ELECTRE Desa')
@section('eyebrow', 'Admin')
@section('page-title', 'Usulan Pembangunan')

@section('content')
    @php
        $acceptedPdfCount = $acceptedUsulansForPdf->count();
    @endphp

    <div class="stack">
        <section class="page-header-card">
            <div>
                <h2>Usulan Pembangunan</h2>
                <p>Kelola usulan pembangunan dari masing-masing dusun.</p>
            </div>
            <div class="page-header-actions">
                <button type="button" class="btn btn-secondary btn-auto" data-open-accepted-pdf-modal @disabled($acceptedPdfCount === 0)>
                    <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5" /><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M7 14h10v7H7Z" /></svg>
                    PDF Usulan Diterima
                </button>
                <a href="{{ route('admin.usulan.create') }}" class="btn btn-primary btn-auto">
                    <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                    Tambah Usulan
                </a>
            </div>
        </section>

        <section class="panel">
            <form method="GET" action="{{ route('admin.usulan.index') }}" class="filter-bar usulan-filter compact-filter">
                <div class="filter-field grow input-with-icon compact-filter-search">
                    <label for="q" class="form-label sr-only">Pencarian</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                    </span>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Cari kegiatan, deskripsi, atau dusun">
                </div>
                <div class="filter-field input-with-icon">
                    <label for="tahun" class="form-label sr-only">Tahun</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4M16 2v4" /><path d="M3 10h18" /><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z" /></svg>
                    </span>
                    <select id="tahun" name="tahun" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($tahunTersedia as $tahun)
                            <option value="{{ $tahun }}" @selected($filters['tahun'] == $tahun)>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field input-with-icon">
                    <label for="dusun_id" class="form-label sr-only">Dusun</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg>
                    </span>
                    <select id="dusun_id" name="dusun_id" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($dusuns as $dusun)
                            <option value="{{ $dusun->id }}" @selected($filters['dusun_id'] == $dusun->id)>{{ $dusun->nama_dusun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field input-with-icon">
                    <label for="status" class="form-label sr-only">Status</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
                    </span>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.3-4.3" /><path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.usulan.index') }}" class="btn btn-light">
                        <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7" /><path d="M3 4v6h6" /></svg>
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="panel">
            @if ($usulans->count() > 0)
                <div class="table-wrap desktop-table">
                    <table class="data-table admin-usulan-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tahun</th>
                                <th>Dusun</th>
                                <th>Nama Kegiatan</th>
                                <th>Estimasi Anggaran</th>
                                <th>Status & Catatan</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usulans as $usulan)
                                <tr>
                                    <td>{{ ($usulans->firstItem() ?? 0) + $loop->index }}</td>
                                    <td><strong>{{ $usulan->tahun }}</strong></td>
                                    <td>{{ $usulan->dusun?->nama_dusun ?? '-' }}</td>
                                    <td>
                                        <strong>{{ $usulan->nama_kegiatan }}</strong>
                                        @if ($usulan->deskripsi)
                                            <small>{{ \Illuminate\Support\Str::limit($usulan->deskripsi, 64) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $usulan->estimasi_anggaran !== null ? 'Rp '.number_format((float) $usulan->estimasi_anggaran, 0, ',', '.') : '-' }}</td>
                                    <td class="inline-status-cell">
                                        <form method="POST" action="{{ route('admin.usulan.update-status', $usulan) }}" class="inline-status-form">
                                            @csrf
                                            @method('PATCH')
                                            <div class="inline-status-row">
                                                <select name="status" class="form-control inline-status-select" aria-label="Status usulan {{ $usulan->nama_kegiatan }}">
                                                    @foreach ($statuses as $status)
                                                        <option value="{{ $status }}" @selected($usulan->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-secondary action-icon-btn" title="Simpan status dan catatan" aria-label="Simpan status dan catatan">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" /><path d="M17 21v-8H7v8" /><path d="M7 3v5h8" /></svg>
                                                </button>
                                            </div>
                                            <details class="inline-note-dropdown">
                                                <summary>Catatan</summary>
                                                <textarea name="catatan_admin" rows="3" class="form-control" placeholder="Catatan admin">{{ $usulan->catatan_admin }}</textarea>
                                            </details>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="action-group icon-actions">
                                            <a href="{{ route('admin.usulan.edit', $usulan) }}" class="btn btn-sm btn-light action-icon-btn" title="Edit usulan" aria-label="Edit usulan">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.usulan.destroy', $usulan) }}" class="js-confirm" data-title="Hapus Usulan?" data-text="Data usulan akan dihapus. Lanjutkan?" data-icon="warning" data-confirm-button="Ya, Hapus">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger action-icon-btn" title="Hapus usulan" aria-label="Hapus usulan">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-list">
                    @foreach ($usulans as $usulan)
                        <article class="mobile-card">
                            <div class="mobile-card-head">
                                <div>
                                    <span class="code-pill">{{ $usulan->tahun }}</span>
                                    <h3>{{ $usulan->nama_kegiatan }}</h3>
                                </div>
                                <span class="badge {{ $usulan->status_badge_class }}">{{ $usulan->status_label }}</span>
                            </div>
                            <dl class="meta-grid">
                                <div><dt>Dusun</dt><dd>{{ $usulan->dusun?->nama_dusun ?? '-' }}</dd></div>
                                <div><dt>Anggaran</dt><dd>{{ $usulan->estimasi_anggaran !== null ? 'Rp '.number_format((float) $usulan->estimasi_anggaran, 0, ',', '.') : '-' }}</dd></div>
                            </dl>
                            <form method="POST" action="{{ route('admin.usulan.update-status', $usulan) }}" class="inline-status-form mobile-inline-status">
                                @csrf
                                @method('PATCH')
                                <div class="inline-status-row">
                                    <select name="status" class="form-control inline-status-select" aria-label="Status usulan {{ $usulan->nama_kegiatan }}">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" @selected($usulan->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-secondary action-icon-btn" title="Simpan status dan catatan" aria-label="Simpan status dan catatan">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" /><path d="M17 21v-8H7v8" /><path d="M7 3v5h8" /></svg>
                                    </button>
                                </div>
                                <details class="inline-note-dropdown">
                                    <summary>Catatan</summary>
                                    <textarea name="catatan_admin" rows="3" class="form-control" placeholder="Catatan admin">{{ $usulan->catatan_admin }}</textarea>
                                </details>
                            </form>
                            <div class="mobile-actions icon-actions">
                                <a href="{{ route('admin.usulan.edit', $usulan) }}" class="btn btn-sm btn-light action-icon-btn" title="Edit usulan" aria-label="Edit usulan">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.usulan.destroy', $usulan) }}" class="js-confirm" data-title="Hapus Usulan?" data-text="Data usulan akan dihapus. Lanjutkan?" data-icon="warning" data-confirm-button="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger action-icon-btn" title="Hapus usulan" aria-label="Hapus usulan">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrap">{{ $usulans->links() }}</div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6M8 13h8M8 17h5" /></svg></div>
                    <h3>Usulan pembangunan belum ditemukan</h3>
                    <p>Tambahkan usulan baru atau ubah filter pencarian.</p>
                    <a href="{{ route('admin.usulan.create') }}" class="btn btn-primary btn-auto">Tambah Usulan</a>
                </div>
            @endif
        </section>
    </div>

    <div class="modal-backdrop" data-accepted-pdf-modal hidden>
        <div class="modal-card accepted-pdf-modal" role="dialog" aria-modal="true" aria-labelledby="acceptedPdfTitle">
            <div class="modal-head">
                <div>
                    <h3 id="acceptedPdfTitle">Cetak PDF Usulan Diterima</h3>
                    <p>Pilih usulan diterima tahun {{ $filters['tahun'] }} yang ingin ditampilkan di PDF.</p>
                </div>
                <button type="button" class="icon-button" data-close-accepted-pdf-modal aria-label="Tutup modal">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                </button>
            </div>

            @if ($acceptedPdfCount > 0)
                <form method="POST" action="{{ route('admin.usulan.export-pdf') }}" target="_blank" class="accepted-pdf-form" data-no-loading="true">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $filters['tahun'] }}">

                    <div class="modal-toolbar">
                        <label class="checkbox-row select-all-row">
                            <input type="checkbox" data-accepted-pdf-select-all checked>
                            <span>Pilih semua usulan diterima</span>
                        </label>
                        <span class="selection-counter" data-accepted-pdf-counter>{{ $acceptedPdfCount }} dipilih</span>
                    </div>

                    <div class="accepted-pdf-list">
                        @foreach ($acceptedUsulansForPdf as $acceptedUsulan)
                            <label class="accepted-pdf-item">
                                <input type="checkbox" name="usulan_ids[]" value="{{ $acceptedUsulan->id }}" data-accepted-pdf-checkbox checked>
                                <span>
                                    <strong>{{ $acceptedUsulan->nama_kegiatan }}</strong>
                                    <small>
                                        {{ $acceptedUsulan->dusun?->nama_dusun ?? 'Desa Barambang' }}
                                        @if ($acceptedUsulan->lokasi_kegiatan)
                                            &middot; {{ $acceptedUsulan->lokasi_kegiatan }}
                                        @endif
                                    </small>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-light" data-close-accepted-pdf-modal>Batal</button>
                        <button type="submit" class="btn btn-primary" data-accepted-pdf-submit>
                            <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5" /><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M7 14h10v7H7Z" /></svg>
                            Cetak PDF
                        </button>
                    </div>
                </form>
            @else
                <div class="empty-state compact-empty">
                    <h3>Belum ada usulan diterima</h3>
                    <p>Ubah status usulan menjadi diterima pada tahun {{ $filters['tahun'] }} sebelum mencetak PDF.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.querySelector('[data-accepted-pdf-modal]');
            var openButton = document.querySelector('[data-open-accepted-pdf-modal]');

            if (!modal || !openButton) {
                return;
            }

            var closeButtons = modal.querySelectorAll('[data-close-accepted-pdf-modal]');
            var selectAll = modal.querySelector('[data-accepted-pdf-select-all]');
            var checkboxes = Array.prototype.slice.call(modal.querySelectorAll('[data-accepted-pdf-checkbox]'));
            var counter = modal.querySelector('[data-accepted-pdf-counter]');
            var submitButton = modal.querySelector('[data-accepted-pdf-submit]');

            function updateCounter() {
                var selected = checkboxes.filter(function (checkbox) {
                    return checkbox.checked;
                }).length;

                if (counter) {
                    counter.textContent = selected + ' dipilih';
                }

                if (submitButton) {
                    submitButton.disabled = selected === 0;
                }

                if (selectAll) {
                    selectAll.checked = selected === checkboxes.length;
                    selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
                }
            }

            openButton.addEventListener('click', function () {
                modal.hidden = false;
                document.body.classList.add('modal-open');
                updateCounter();
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    modal.hidden = true;
                    document.body.classList.remove('modal-open');
                });
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.hidden = true;
                    document.body.classList.remove('modal-open');
                }
            });

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                    updateCounter();
                });
            }

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', updateCounter);
            });
        });
    </script>
@endsection
