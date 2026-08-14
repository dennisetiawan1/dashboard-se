<?php

namespace App\Http\Controllers;

use App\Models\ReferenceUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ReferenceUploadController extends Controller
{
    public function download(ReferenceUpload $referenceUpload)
    {
        return Storage::disk('public')->download(
            $referenceUpload->file_path,
            $referenceUpload->original_filename
        );
    }

    public function destroy(ReferenceUpload $referenceUpload): RedirectResponse
    {
        // Hapus file dari storage jika masih ada
        if ($referenceUpload->file_path &&
            Storage::disk('public')->exists($referenceUpload->file_path)) {

            Storage::disk('public')->delete($referenceUpload->file_path);
        }

        // Hapus data riwayat upload
        $referenceUpload->delete();

        return redirect()
            ->route('uploads.index')
            ->with('success', 'Riwayat upload referensi berhasil dihapus.');
    }
}