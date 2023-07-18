<?php

namespace App\Http\Controllers;

use App\Models\DataAtk;
use App\Models\Pengeluaran;
use App\Models\Permintaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PermintaanController extends Controller
{
    public function index(Request $request): Response
    {

        $query = Permintaan::latest()->with(['dataAtk.pemasukan.satuan', 'user.divisi']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->where('jumlah', 'LIKE', "%$search%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%$search%")
                            ->orWhereHas('divisi', function ($query) use ($search) {
                                $query->where('nama_divisi', 'LIKE', "%$search%");
                            });
                    })
                    ->orWhereHas('dataAtk.pemasukan', function ($query) use ($search) {
                        $query->where('jenis_atk', 'LIKE', "%$search%")
                            ->orWhereHas('satuan', function ($query) use ($search) {
                                $query->where('nama_satuan', 'LIKE', "%$search%");
                            });
                    });
            });
        }

        $permintaan = $query->paginate($request->perpage ?? 10)->withQueryString();

        return Inertia::render('Permintaan/Page', [
            'permintaan' => $permintaan,
        ]);
    }

    public function accept(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'pesan' => 'required',
        ]);

        try {
            $permintaan = Permintaan::findOrFail($id);
            $permintaan->status = 'disetujui';
            $permintaan->pesan = $request->pesan;
            $permintaan->save();

            $dataAtk = DataAtk::findOrFail($permintaan->data_atk_id);
            $dataAtk->stok -= $permintaan->jumlah;
            $dataAtk->save();

            $pengeluaran = new Pengeluaran;
            $pengeluaran->jenis_atk = $dataAtk->pemasukan->jenis_atk;
            $pengeluaran->jumlah_keluar = $permintaan->jumlah;
            $pengeluaran->nama_satuan = $dataAtk->pemasukan->satuan->nama_satuan;
            $pengeluaran->save();

            return redirect()->route('permintaan.index')->with('success', 'Permintaan berhasil diaccept');
        } catch (\Throwable $th) {
            return redirect()->route('permintaan.index')->with('error', "Opps something went wrong");
        }
    }

    public function refuse(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'pesan' => 'required',
        ]);

        try {
            $permintaan = Permintaan::findOrFail($id);
            $permintaan->status = 'ditolak';
            $permintaan->pesan = $request->pesan;
            $permintaan->save();

            return redirect()->route('permintaan.index')->with('success', 'Permintaan berhasil direfuse');
        } catch (\Throwable $th) {
            return redirect()->route('permintaan.index')->with('error', "Opps something went wrong");
        }
    }


    // divisi
    public function indexDivisi(Request $request): Response
    {
        $data_atk = DataAtk::with(['kategori', 'pemasukan'])->latest()->get();
        $query = Permintaan::latest()->where('user_id', Auth::user()->id)->with(['dataAtk.pemasukan.satuan', 'user.divisi']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->where('jumlah', 'LIKE', "%$search%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%$search%")
                            ->orWhereHas('divisi', function ($query) use ($search) {
                                $query->where('nama_divisi', 'LIKE', "%$search%");
                            });
                    })
                    ->orWhereHas('dataAtk.pemasukan', function ($query) use ($search) {
                        $query->where('jenis_atk', 'LIKE', "%$search%")
                            ->orWhereHas('satuan', function ($query) use ($search) {
                                $query->where('nama_satuan', 'LIKE', "%$search%");
                            });
                    });
            });
        }

        $permintaan = $query->paginate($request->perpage ?? 10)->withQueryString();

        return Inertia::render('ViewDivisi/Permintaan/Page', [
            'permintaan' => $permintaan,
            'data_atk' => $data_atk
        ]);
    }

    public function storeDivisi(Request $request): RedirectResponse
    {
        $request->validate([
            'data_atk_id' => 'required|integer',
            'jumlah' => 'required|integer',
        ]);

        try {
            $permintaan = new Permintaan;
            $permintaan->user_id = Auth::user()->id;
            $permintaan->data_atk_id = $request->data_atk_id;
            $permintaan->jumlah = $request->jumlah;
            $permintaan->save();

            return redirect()->route('divisi-permintaan.index')->with('success', 'Permintaan ATK berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->route('divisi-permintaan.index')->with('error', "Opps something went wrong");
        }
    }

    public function updateDivisi(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'data_atk_id' => 'required|integer',
            'jumlah' => 'required|integer',
        ]);

        try {
            $permintaan = Permintaan::findOrFail($id);
            $permintaan->data_atk_id = $request->data_atk_id;
            $permintaan->jumlah = $request->jumlah;
            $permintaan->save();

            return redirect()->route('divisi-permintaan.index')->with('success', 'Permintaan ATK berhasil diedit');
        } catch (\Throwable $th) {
            return redirect()->route('divisi-permintaan.index')->with('error', "Opps something went wrong");
        }
    }

    public function destroyDivisi(string $id): RedirectResponse
    {
        try {
            $permintaan = Permintaan::findOrFail($id);
            $permintaan->delete();

            return redirect()->route('divisi-permintaan.index')->with('success', 'Permintaan ATK berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->route('divisi-permintaan.index')->with('error', "Opps something went wrong");
        }
    }
}
